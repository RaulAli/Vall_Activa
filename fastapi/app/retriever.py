from __future__ import annotations

import math
import re
from typing import Any


def _normalize(text: str) -> list[str]:
    clean = re.sub(r"[^a-z0-9\s]", "", text.lower())
    return [w for w in re.split(r"\s+", clean) if w]


def _text_to_vector(words: list[str], vocabulary: list[str]) -> list[int]:
    return [sum(1 for w in words if w == word) for word in vocabulary]


def _build_vocabulary(products: list[dict[str, Any]]) -> list[str]:
    vocab: set[str] = set()
    for p in products:
        words = _normalize(f"{p.get('name', '')} {p.get('category', '')} {p.get('description', '')}")
        vocab.update(words)
    return sorted(vocab)


def _cosine_similarity(a: list[int], b: list[int]) -> float:
    dot = sum(x * y for x, y in zip(a, b))
    mag_a = math.sqrt(sum(x * x for x in a))
    mag_b = math.sqrt(sum(y * y for y in b))
    if mag_a == 0.0 or mag_b == 0.0:
        return 0.0
    return dot / (mag_a * mag_b)


def retrieve_relevant_products(query: str, products: list[dict[str, Any]], top_k: int = 5) -> list[dict[str, Any]]:
    if not products:
        return []

    vocab = _build_vocabulary(products)
    query_words = _normalize(query)
    query_vec = _text_to_vector(query_words, vocab)

    scored: list[tuple[dict[str, Any], float]] = []
    for p in products:
        product_words = _normalize(f"{p.get('name', '')} {p.get('category', '')} {p.get('description', '')}")
        product_vec = _text_to_vector(product_words, vocab)
        scored.append((p, _cosine_similarity(query_vec, product_vec)))

    scored.sort(key=lambda item: item[1], reverse=True)
    return [item[0] for item in scored[: max(1, top_k)]]
