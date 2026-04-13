from __future__ import annotations

import json
import os
import re
import time
from dataclasses import dataclass
from typing import Any, Literal

import httpx

Role = Literal["system", "user", "assistant"]


@dataclass
class Provider:
    name: str
    family: str
    model: str
    api_key: str


class AIGateway:
    def __init__(self) -> None:
        self.providers = self._build_providers()
        self.current_index = 0
        self.blacklist: dict[str, float] = {}
        self.timeout_s = float(os.getenv("AI_GATEWAY_TIMEOUT_SECONDS", "12"))
        self.blacklist_ttl_s = int(os.getenv("AI_GATEWAY_BLACKLIST_TTL_SECONDS", "300"))

    def _build_providers(self) -> list[Provider]:
        providers: list[Provider] = []

        groq_key = (os.getenv("GROQ_API_KEY") or "").strip()
        if groq_key:
            providers.append(Provider("Groq", "openai", os.getenv("GROQ_MODEL", "llama-3.3-70b-versatile"), groq_key))

        cerebras_key = (os.getenv("CEREBRAS_API_KEY") or "").strip()
        if cerebras_key:
            providers.append(Provider("Cerebras", "openai", os.getenv("CEREBRAS_MODEL", "llama-3.3-70b"), cerebras_key))

        openrouter_key = (os.getenv("OPENROUTER_API_KEY") or "").strip()
        if openrouter_key:
            providers.append(Provider("OpenRouter", "openai", os.getenv("OPENROUTER_MODEL", "mistralai/mistral-7b-instruct:free"), openrouter_key))

        gemini_key = (os.getenv("GEMINI_API_KEY") or "").strip()
        if gemini_key:
            providers.append(Provider("Gemini", "gemini", os.getenv("GEMINI_MODEL", "gemini-2.5-flash"), gemini_key))

        mistral_key = (os.getenv("MISTRAL_API_KEY") or "").strip()
        if mistral_key:
            providers.append(Provider("Mistral", "mistral", os.getenv("MISTRAL_MODEL", "mistral-tiny"), mistral_key))

        cohere_key = (os.getenv("COHERE_API_KEY") or "").strip()
        if cohere_key:
            providers.append(Provider("Cohere", "cohere", os.getenv("COHERE_MODEL", "command"), cohere_key))

        providers = self._apply_provider_priority(providers)
        return providers

    def _apply_provider_priority(self, providers: list[Provider]) -> list[Provider]:
        if not providers:
            return providers

        raw_priority = (os.getenv("AI_GATEWAY_PROVIDER_PRIORITY") or "Gemini,Groq,Cerebras,OpenRouter,Cohere,Mistral").strip()
        priority_names = [p.strip().lower() for p in raw_priority.split(",") if p.strip()]
        if not priority_names:
            return providers

        position = {name: idx for idx, name in enumerate(priority_names)}
        fallback_pos = len(priority_names)

        return sorted(
            providers,
            key=lambda provider: (
                position.get(provider.name.lower(), fallback_pos),
                provider.name,
            ),
        )

    async def status(self) -> list[dict[str, Any]]:
        report: list[dict[str, Any]] = []
        probe_messages = [{"role": "user", "content": "ping"}]

        for provider in self.providers:
            start = time.time()
            try:
                _ = await self._chat_with_provider(provider, probe_messages, request_id="status-probe")
                self.blacklist.pop(provider.name, None)
                report.append(
                    {
                        "provider": provider.name,
                        "status": "online",
                        "latencyMs": int((time.time() - start) * 1000),
                        "error": None,
                    }
                )
            except Exception as exc:  # noqa: BLE001
                self.blacklist[provider.name] = time.time()
                report.append(
                    {
                        "provider": provider.name,
                        "status": "offline",
                        "latencyMs": None,
                        "error": str(exc)[:160],
                    }
                )

        return report

    async def chat(self, messages: list[dict[str, str]], request_id: str) -> tuple[str, str]:
        if not self.providers:
            raise RuntimeError("No AI providers configured")

        errors: list[str] = []
        total = len(self.providers)

        for attempt in range(total):
            idx = (self.current_index + attempt) % total
            provider = self.providers[idx]

            if self._is_blacklisted(provider.name):
                continue

            try:
                text = await self._chat_with_provider(provider, messages, request_id=request_id)
                self.current_index = (idx + 1) % total
                self.blacklist.pop(provider.name, None)
                return text, provider.name
            except Exception as exc:  # noqa: BLE001
                self.blacklist[provider.name] = time.time()
                errors.append(f"{provider.name}: {exc}")

        raise RuntimeError("All providers failed: " + " | ".join(errors))

    async def extract_json(self, messages: list[dict[str, str]], request_id: str) -> tuple[dict[str, Any], str]:
        text, provider = await self.chat(messages, request_id)
        data = _extract_json_object(text)
        return data, provider

    def _is_blacklisted(self, provider_name: str) -> bool:
        blocked_at = self.blacklist.get(provider_name)
        if blocked_at is None:
            return False
        if time.time() - blocked_at > self.blacklist_ttl_s:
            self.blacklist.pop(provider_name, None)
            return False
        return True

    async def _chat_with_provider(self, provider: Provider, messages: list[dict[str, str]], request_id: str) -> str:
        if provider.family == "openai":
            return await self._chat_openai_compatible(provider, messages, request_id)
        if provider.family == "gemini":
            return await self._chat_gemini(provider, messages, request_id)
        if provider.family == "mistral":
            return await self._chat_mistral(provider, messages, request_id)
        if provider.family == "cohere":
            return await self._chat_cohere(provider, messages, request_id)

        raise RuntimeError(f"Unknown provider family: {provider.family}")

    async def _chat_openai_compatible(self, provider: Provider, messages: list[dict[str, str]], request_id: str) -> str:
        if provider.name == "Groq":
            url = "https://api.groq.com/openai/v1/chat/completions"
        elif provider.name == "Cerebras":
            url = "https://api.cerebras.ai/v1/chat/completions"
        else:
            url = "https://openrouter.ai/api/v1/chat/completions"

        headers = {
            "Authorization": f"Bearer {provider.api_key}",
            "Content-Type": "application/json",
            "X-Request-Id": request_id,
        }

        if provider.name == "OpenRouter":
            headers["HTTP-Referer"] = os.getenv("OPENROUTER_REFERER", "https://vamo.local")
            headers["X-Title"] = os.getenv("OPENROUTER_APP_NAME", "VAMO")

        payload = {
            "model": provider.model,
            "messages": messages,
            "temperature": 0.1,
            "stream": False,
        }

        async with httpx.AsyncClient(timeout=self.timeout_s) as client:
            response = await client.post(url, headers=headers, json=payload)
            response.raise_for_status()
            data = response.json()

        text = (
            data.get("choices", [{}])[0]
            .get("message", {})
            .get("content", "")
        )
        if not isinstance(text, str) or not text.strip():
            raise RuntimeError("Empty response")
        return text

    async def _chat_gemini(self, provider: Provider, messages: list[dict[str, str]], request_id: str) -> str:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{provider.model}:generateContent"
        combined = "\n\n".join(f"{m['role'].upper()}: {m['content']}" for m in messages)

        payload = {
            "contents": [{"parts": [{"text": combined}]}],
            "generationConfig": {
                "temperature": 0.1,
                "maxOutputTokens": 1200,
            },
        }

        headers = {
            "Content-Type": "application/json",
            "X-Request-Id": request_id,
        }

        async with httpx.AsyncClient(timeout=self.timeout_s) as client:
            response = await client.post(url, headers=headers, params={"key": provider.api_key}, json=payload)
            response.raise_for_status()
            data = response.json()

        candidates = data.get("candidates", [])
        if not candidates:
            raise RuntimeError("No candidates")

        parts = candidates[0].get("content", {}).get("parts", [])
        text = "\n".join(p.get("text", "") for p in parts if isinstance(p, dict))
        if not text.strip():
            raise RuntimeError("Empty response")
        return text

    async def _chat_mistral(self, provider: Provider, messages: list[dict[str, str]], request_id: str) -> str:
        url = "https://api.mistral.ai/v1/chat/completions"
        headers = {
            "Authorization": f"Bearer {provider.api_key}",
            "Content-Type": "application/json",
            "X-Request-Id": request_id,
        }
        payload = {
            "model": provider.model,
            "messages": messages,
            "temperature": 0.1,
            "stream": False,
        }

        async with httpx.AsyncClient(timeout=self.timeout_s) as client:
            response = await client.post(url, headers=headers, json=payload)
            response.raise_for_status()
            data = response.json()

        text = (
            data.get("choices", [{}])[0]
            .get("message", {})
            .get("content", "")
        )
        if not isinstance(text, str) or not text.strip():
            raise RuntimeError("Empty response")
        return text

    async def _chat_cohere(self, provider: Provider, messages: list[dict[str, str]], request_id: str) -> str:
        url = "https://api.cohere.com/v2/chat"
        headers = {
            "Authorization": f"Bearer {provider.api_key}",
            "Content-Type": "application/json",
            "X-Request-Id": request_id,
        }
        payload = {
            "model": provider.model,
            "messages": [{"role": m["role"], "content": m["content"]} for m in messages],
            "temperature": 0.1,
        }

        async with httpx.AsyncClient(timeout=self.timeout_s) as client:
            response = await client.post(url, headers=headers, json=payload)
            response.raise_for_status()
            data = response.json()

        text = ""
        message = data.get("message", {})
        content = message.get("content", [])
        if isinstance(content, list):
            chunks = []
            for item in content:
                if isinstance(item, dict):
                    value = item.get("text", "")
                    if isinstance(value, str):
                        chunks.append(value)
            text = "\n".join(chunks)

        if not text:
            text = str(data.get("text", ""))

        if not text.strip():
            raise RuntimeError("Empty response")
        return text


def _extract_json_object(text: str) -> dict[str, Any]:
    candidate = text.strip()

    fence_match = re.search(r"```(?:json)?\s*(\{.*?\})\s*```", candidate, flags=re.DOTALL | re.IGNORECASE)
    if fence_match:
        candidate = fence_match.group(1).strip()

    if not candidate.startswith("{"):
        obj_match = re.search(r"\{.*\}", candidate, flags=re.DOTALL)
        if obj_match:
            candidate = obj_match.group(0)

    data = json.loads(candidate)
    if not isinstance(data, dict):
        raise RuntimeError("Response is not a JSON object")
    return data
