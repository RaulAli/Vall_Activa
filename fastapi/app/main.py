from __future__ import annotations

import os
import re
import uuid
from typing import Any, Literal

from fastapi import FastAPI, Request
from pydantic import BaseModel, Field

from .ai_gateway import AIGateway
from .products import DEFAULT_PRODUCTS
from .retriever import retrieve_relevant_products

app = FastAPI(title="VAMO NLP Search Service", version="0.1.0")
ai_gateway = AIGateway()


class InterpretRequest(BaseModel):
    domain: Literal["routes", "offers"]
    query: str = Field(min_length=1, max_length=300)
    context: dict[str, Any] = Field(default_factory=dict)


class InterpretResponse(BaseModel):
    interpreted_filters: dict[str, Any]
    confidence: float = Field(ge=0.0, le=1.0)
    warnings: list[str] = Field(default_factory=list)
    request_id: str
    provider_used: str | None = None
    source: Literal["ai", "fallback"] = "fallback"


class RecommendRequest(BaseModel):
    prompt: str = Field(min_length=1, max_length=400)
    products: list[dict[str, Any]] | None = None
    top_k: int = Field(default=5, ge=1, le=20)


class RecommendResponse(BaseModel):
    success: bool
    provider_used: str | None
    recommendation: str
    items_found: int
    request_id: str


def _first_int(pattern: str, text: str) -> int | None:
    m = re.search(pattern, text)
    if not m:
        return None
    try:
        return int(m.group(1))
    except (TypeError, ValueError):
        return None


def _parse_routes(query: str) -> tuple[dict[str, Any], float, list[str]]:
    q = query.lower().strip()
    out: dict[str, Any] = {}
    warnings: list[str] = []
    confidence = 0.45

    inferred_sport = _infer_route_sport_from_text(query)
    if inferred_sport is not None:
        out["sportCode"] = inferred_sport
        confidence += 0.15

    if any(w in q for w in ["facil", "fácil", "easy"]):
        out["difficulty"] = "EASY"
        confidence += 0.1
    elif any(w in q for w in ["media", "moderada", "medium"]):
        out["difficulty"] = "MODERATE"
        confidence += 0.1
    elif any(w in q for w in ["dificil", "difícil", "hard"]):
        out["difficulty"] = "HARD"
        confidence += 0.1

    if any(w in q for w in ["circular", "loop"]):
        out["routeType"] = "CIRCULAR"
        confidence += 0.1
    elif any(w in q for w in ["lineal", "ida y vuelta", "out and back"]):
        out["routeType"] = "LINEAR"
        confidence += 0.1

    distance_min = _first_int(r"(?:mas de|m[aá]s de|more than)\s*(\d+)\s*(?:km|k)", q)
    distance_max = _first_int(r"(?:menos de|under|hasta)\s*(\d+)\s*(?:km|k)", q)

    m_dist_range = re.search(r"entre\s*(\d{1,4})\s*(?:km|k)?\s*(?:y|a|hasta)\s*(\d{1,4})\s*(?:km|k)?", q)
    if m_dist_range:
        a = int(m_dist_range.group(1))
        b = int(m_dist_range.group(2))
        distance_min = min(a, b)
        distance_max = max(a, b)

    if distance_min is not None:
        out["distanceMin"] = distance_min * 1000
        confidence += 0.05
    if distance_max is not None:
        out["distanceMax"] = distance_max * 1000
        confidence += 0.05

    gain_max = _first_int(r"(?:menos de|under|hasta)\s*(\d+)\s*(?:m\+|m de desnivel|m)", q)
    if gain_max is not None and "desnivel" in q:
        out["gainMax"] = gain_max
        confidence += 0.05

    gain_min, gain_between_max = _infer_gain_range_from_text(query)
    if gain_min is not None:
        out["gainMin"] = gain_min
        confidence += 0.12
    if gain_between_max is not None:
        out["gainMax"] = gain_between_max
        confidence += 0.12

    cleaned = re.sub(r"\s+", " ", query).strip()
    cleaned_q = _sanitize_route_q(cleaned)
    if cleaned_q:
        out["q"] = cleaned_q

    if confidence < 0.5:
        warnings.append("low_confidence_interpretation")

    return out, min(confidence, 0.95), warnings


def _parse_offers(query: str) -> tuple[dict[str, Any], float, list[str]]:
    q = query.lower().strip()
    out: dict[str, Any] = {}
    warnings: list[str] = []
    confidence = 0.45

    if any(w in q for w in ["descuento", "%", "rebaja"]):
        out["discountType"] = "PERCENT"
        confidence += 0.12
    if any(w in q for w in ["2x1", "dos por uno"]):
        out["discountType"] = "BUNDLE"
        confidence += 0.15

    max_price = _first_int(r"(?:menos de|under|hasta)\s*(\d+)\s*(?:eur|€|euros)", q)
    min_price = _first_int(r"(?:mas de|m[aá]s de|more than)\s*(\d+)\s*(?:eur|€|euros)", q)
    if min_price is not None:
        out["priceMin"] = str(min_price)
        confidence += 0.08
    if max_price is not None:
        out["priceMax"] = str(max_price)
        confidence += 0.08

    if any(w in q for w in ["stock", "disponible", "available"]):
        out["inStock"] = True
        confidence += 0.05

    cleaned = re.sub(r"\s+", " ", query).strip()
    if cleaned:
        out["q"] = cleaned

    if confidence < 0.5:
        warnings.append("low_confidence_interpretation")

    return out, min(confidence, 0.95), warnings


def _build_interpret_prompt(domain: str, query: str, context: dict[str, Any]) -> list[dict[str, str]]:
    if domain == "routes":
        schema_hint = (
            '{"interpreted_filters":{"q":"keywords|null","sportCode":"hike|run|bike|kayak",'
            '"distanceMin":null,"distanceMax":null,"gainMin":null,"gainMax":null,'
            '"difficulty":"EASY|MODERATE|HARD|null","routeType":"CIRCULAR|LINEAR|null",'
            '"durationMin":null,"durationMax":null,"sort":"recent|distance|gain|null",'
            '"order":"asc|desc|null"},"confidence":0.0,"warnings":[]}'
        )
    else:
        schema_hint = (
            '{"interpreted_filters":{"q":"...","discountType":"PERCENT|BUNDLE|null",'
            '"priceMin":null,"priceMax":null,"pointsMin":null,"pointsMax":null,'
            '"inStock":null,"sort":"recent|price|points|null","order":"asc|desc|null"},'
            '"confidence":0.0,"warnings":[]}'
        )

    system = (
        "Eres un parser de busqueda en lenguaje natural. "
        "Devuelve SOLO JSON valido sin markdown ni texto extra. "
        "No inventes filtros. Usa null si no aplica. "
        f"Formato obligatorio: {schema_hint}"
    )
    user = (
        f"Domain: {domain}\n"
        f"User query: {query}\n"
        f"Context JSON: {context}"
    )

    return [
        {"role": "system", "content": system},
        {"role": "user", "content": user},
    ]


def _infer_route_sport_from_text(text: str) -> str | None:
    q = text.lower()

    bike_terms = {"ciclismo", "ciclista", "bici", "bicicleta", "mtb", "btt", "gravel", "bike", "cycling"}
    hiking_terms = {"senderismo", "hiking", "trekking", "montana", "montaña", "andar", "caminar"}
    running_terms = {"trail", "running", "run", "correr"}
    kayak_terms = {"kayak", "piragua", "canoa"}

    def contains_any(terms: set[str]) -> bool:
        return any(term in q for term in terms)

    if contains_any(bike_terms):
        return "bike"
    if contains_any(hiking_terms):
        return "hike"
    if contains_any(running_terms):
        return "run"
    if contains_any(kayak_terms):
        return "kayak"
    return None


def _normalize_route_sport_code(value: Any) -> str | None:
    if value is None:
        return None

    v = str(value).strip().lower()
    if v == "":
        return None

    alias_map = {
        "hiking": "hike",
        "senderismo": "hike",
        "trekking": "hike",
        "hike": "hike",
        "running": "run",
        "trail": "run",
        "run": "run",
        "mtb": "bike",
        "cycling": "bike",
        "bike": "bike",
        "ciclismo": "bike",
        "kayak": "kayak",
    }

    return alias_map.get(v, v)


def _sanitize_route_q(value: Any) -> str | None:
    if not isinstance(value, str):
        return None

    q = value.strip().lower()
    if not q:
        return None

    tokens = [t for t in re.split(r"[^a-z0-9áéíóúüñ]+", q) if t]
    if not tokens:
        return None

    stop = {
        "ruta",
        "rutas",
        "de",
        "entre",
        "a",
        "y",
        "en",
        "con",
        "sin",
        "menos",
        "mas",
        "más",
        "que",
        "por",
        "para",
        "km",
        "k",
        "moderada",
        "moderado",
        "facil",
        "fácil",
        "dificil",
        "difícil",
        "senderismo",
        "hiking",
        "trekking",
        "montana",
        "montaña",
        "ciclismo",
        "ciclista",
        "bici",
        "bicicleta",
        "trail",
        "running",
        "run",
        "correr",
    }

    cleaned = [
        t
        for t in tokens
        if t not in stop and not t.isdigit() and re.fullmatch(r"\d+(?:km|k|m)?", t) is None
    ]
    if not cleaned:
        return None

    return " ".join(cleaned[:4])


def _infer_gain_range_from_text(text: str) -> tuple[int | None, int | None]:
    q = text.lower()

    m = re.search(r"entre\s*(\d{1,5})\s*(?:m|metros|m\+)?\s*(?:y|a|hasta)\s*(\d{1,5})\s*(?:m|metros|m\+)?\s*(?:de\s*desnivel|desnivel)", q)
    if m:
        a = int(m.group(1))
        b = int(m.group(2))
        return (min(a, b), max(a, b))

    m = re.search(r"desnivel\s*entre\s*(\d{1,5})\s*(?:m|metros|m\+)?\s*(?:y|a|hasta)\s*(\d{1,5})\s*(?:m|metros|m\+)?", q)
    if m:
        a = int(m.group(1))
        b = int(m.group(2))
        return (min(a, b), max(a, b))

    return (None, None)


def _sanitize_interpret_result(domain: str, result: dict[str, Any], fallback_query: str) -> tuple[dict[str, Any], float, list[str]]:
    filters_raw = result.get("interpreted_filters")
    if not isinstance(filters_raw, dict):
        filters_raw = {}

    confidence = result.get("confidence", 0.7)
    try:
        confidence_value = float(confidence)
    except (TypeError, ValueError):
        confidence_value = 0.7
    confidence_value = max(0.0, min(1.0, confidence_value))

    warnings_raw = result.get("warnings", [])
    warnings: list[str] = []
    if isinstance(warnings_raw, list):
        warnings = [str(w).strip() for w in warnings_raw if str(w).strip()]

    if domain == "routes":
        allowed = {
            "q",
            "sportCode",
            "distanceMin",
            "distanceMax",
            "gainMin",
            "gainMax",
            "difficulty",
            "routeType",
            "durationMin",
            "durationMax",
            "sort",
            "order",
        }
    else:
        allowed = {
            "q",
            "discountType",
            "priceMin",
            "priceMax",
            "pointsMin",
            "pointsMax",
            "inStock",
            "sort",
            "order",
        }

    sanitized: dict[str, Any] = {}
    for key in allowed:
        if key in filters_raw:
            sanitized[key] = filters_raw[key]

    if domain == "routes":
        def to_int_or_none(value: Any) -> int | None:
            if value is None or value == "":
                return None
            try:
                return int(float(value))
            except (TypeError, ValueError):
                return None

        for distance_key in ("distanceMin", "distanceMax"):
            parsed = to_int_or_none(sanitized.get(distance_key))
            if parsed is None:
                sanitized.pop(distance_key, None)
                continue

            if parsed > 0 and parsed <= 200:
                parsed *= 1000
            sanitized[distance_key] = parsed

        for int_key in ("gainMin", "gainMax", "durationMin", "durationMax"):
            parsed = to_int_or_none(sanitized.get(int_key))
            if parsed is None:
                sanitized.pop(int_key, None)
            else:
                sanitized[int_key] = parsed

        if "gainMin" not in sanitized and "gainMax" not in sanitized:
            gain_min, gain_max = _infer_gain_range_from_text(fallback_query)
            if gain_min is not None:
                sanitized["gainMin"] = gain_min
            if gain_max is not None:
                sanitized["gainMax"] = gain_max

        sport_code = sanitized.get("sportCode")
        if sport_code is None:
            inferred = _infer_route_sport_from_text(fallback_query)
            if inferred is None:
                sanitized.pop("sportCode", None)
            else:
                sanitized["sportCode"] = inferred
        else:
            normalized = _normalize_route_sport_code(sport_code)
            if normalized is None:
                sanitized.pop("sportCode", None)
            else:
                sanitized["sportCode"] = normalized

        difficulty = sanitized.get("difficulty")
        if difficulty is None:
            sanitized.pop("difficulty", None)
        else:
            difficulty_value = str(difficulty).strip().upper()
            if difficulty_value in {"EASY", "MODERATE", "HARD"}:
                sanitized["difficulty"] = difficulty_value
            else:
                sanitized.pop("difficulty", None)

        route_type = sanitized.get("routeType")
        if route_type is None:
            sanitized.pop("routeType", None)
        else:
            route_type_value = str(route_type).strip().upper()
            if route_type_value in {"CIRCULAR", "LINEAR"}:
                sanitized["routeType"] = route_type_value
            else:
                sanitized.pop("routeType", None)

        sort = sanitized.get("sort")
        if sort is None:
            sanitized.pop("sort", None)
        else:
            sort_value = str(sort).strip().lower()
            if sort_value in {"recent", "distance", "gain"}:
                sanitized["sort"] = sort_value
            else:
                sanitized.pop("sort", None)

        order = sanitized.get("order")
        if order is None:
            sanitized.pop("order", None)
        else:
            order_value = str(order).strip().lower()
            if order_value in {"asc", "desc"}:
                sanitized["order"] = order_value
            else:
                sanitized.pop("order", None)

        q_value = _sanitize_route_q(sanitized.get("q"))
        if q_value is None:
            sanitized.pop("q", None)
        else:
            sanitized["q"] = q_value

    if "q" in sanitized and sanitized.get("q") is None:
        sanitized.pop("q", None)

    return sanitized, confidence_value, warnings


def _build_recommendation_prompt(prompt: str, relevant: list[dict[str, Any]]) -> list[dict[str, str]]:
    products_text = "\n".join(
        [
            "- Name: {name}\n  Category: {category}\n  Description: {description}\n  Price: ${price}".format(
                name=p.get("name", ""),
                category=p.get("category", ""),
                description=p.get("description", ""),
                price=p.get("price", ""),
            )
            for p in relevant
        ]
    )

    system = (
        "Eres un asistente experto en recomendacion de productos. "
        "Recomienda solo productos presentes en el contexto y explica por que encajan."
    )
    user = (
        "Productos relevantes:\n"
        f"{products_text}\n\n"
        "Instrucciones:\n"
        "- No inventes productos.\n"
        "- Si nada encaja, dilo claramente.\n\n"
        f"Pregunta del usuario: {prompt}"
    )

    return [
        {"role": "system", "content": system},
        {"role": "user", "content": user},
    ]


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.get("/v1/nl-search/providers/status")
async def providers_status() -> dict[str, Any]:
    report = await ai_gateway.status()
    return {
        "total_providers": len(ai_gateway.providers),
        "report": report,
        "blacklist": sorted(ai_gateway.blacklist.keys()),
    }


@app.post("/v1/nl-search/interpret", response_model=InterpretResponse)
async def interpret(payload: InterpretRequest, request: Request) -> InterpretResponse:
    request_id = request.headers.get("x-request-id") or str(uuid.uuid4())

    use_ai = (os.getenv("NL_USE_AI_GATEWAY", "1").strip().lower() in {"1", "true", "yes", "on"})
    target_domain = (os.getenv("NL_SEARCH_TARGET_DOMAIN", "routes") or "routes").strip().lower()
    ai_domain_enabled = payload.domain == target_domain

    if use_ai and ai_domain_enabled and ai_gateway.providers:
        try:
            prompt_messages = _build_interpret_prompt(payload.domain, payload.query, payload.context)
            ai_result, provider = await ai_gateway.extract_json(prompt_messages, request_id=request_id)
            interpreted, confidence, warnings = _sanitize_interpret_result(payload.domain, ai_result, payload.query)
            return InterpretResponse(
                interpreted_filters=interpreted,
                confidence=confidence,
                warnings=warnings,
                request_id=request_id,
                provider_used=provider,
                source="ai",
            )
        except Exception as exc:  # noqa: BLE001
            if payload.domain == "routes":
                interpreted, confidence, warnings = _parse_routes(payload.query)
            else:
                interpreted, confidence, warnings = _parse_offers(payload.query)
            warnings = [*warnings, f"ai_gateway_failed:{exc.__class__.__name__}"]
            return InterpretResponse(
                interpreted_filters=interpreted,
                confidence=confidence,
                warnings=warnings,
                request_id=request_id,
                provider_used=None,
                source="fallback",
            )

    if payload.domain == "routes":
        interpreted, confidence, warnings = _parse_routes(payload.query)
    else:
        interpreted, confidence, warnings = _parse_offers(payload.query)

    if use_ai and not ai_domain_enabled:
        warnings = [*warnings, f"ai_disabled_for_domain:{payload.domain}"]

    return InterpretResponse(
        interpreted_filters=interpreted,
        confidence=confidence,
        warnings=warnings,
        request_id=request_id,
        provider_used=None,
        source="fallback",
    )


@app.post("/v1/recommend", response_model=RecommendResponse)
async def recommend(payload: RecommendRequest, request: Request) -> RecommendResponse:
    request_id = request.headers.get("x-request-id") or str(uuid.uuid4())

    products = payload.products if payload.products is not None else DEFAULT_PRODUCTS
    relevant = retrieve_relevant_products(payload.prompt, products, top_k=payload.top_k)

    if not ai_gateway.providers:
        names = [str(p.get("name", "")) for p in relevant if p.get("name")]
        text = "Te recomiendo revisar: " + ", ".join(names) if names else "No he encontrado productos relevantes."
        return RecommendResponse(
            success=True,
            provider_used=None,
            recommendation=text,
            items_found=len(relevant),
            request_id=request_id,
        )

    try:
        messages = _build_recommendation_prompt(payload.prompt, relevant)
        text, provider = await ai_gateway.chat(messages, request_id=request_id)
    except Exception:
        names = [str(p.get("name", "")) for p in relevant if p.get("name")]
        text = "Te recomiendo revisar: " + ", ".join(names) if names else "No he encontrado productos relevantes."
        provider = None

    return RecommendResponse(
        success=True,
        provider_used=provider,
        recommendation=text,
        items_found=len(relevant),
        request_id=request_id,
    )
