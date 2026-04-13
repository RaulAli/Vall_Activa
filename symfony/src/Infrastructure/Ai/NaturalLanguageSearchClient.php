<?php
declare(strict_types=1);

namespace App\Infrastructure\Ai;

final class NaturalLanguageSearchClient
{
    private string $baseUrl;
    private bool $enabled;
    private float $timeoutSeconds;
    private float $minConfidence;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) ($_ENV['FASTAPI_URL'] ?? 'http://localhost:8001'), '/');

        $enabledRaw = strtolower((string) ($_ENV['NL_SEARCH_ENABLED'] ?? '1'));
        $this->enabled = in_array($enabledRaw, ['1', 'true', 'yes', 'on'], true);

        $timeoutMs = (int) ($_ENV['NL_SEARCH_TIMEOUT_MS'] ?? 900);
        if ($timeoutMs < 100) {
            $timeoutMs = 100;
        }
        $this->timeoutSeconds = $timeoutMs / 1000;

        $minConfidence = (float) ($_ENV['NL_SEARCH_MIN_CONFIDENCE'] ?? 0.55);
        if ($minConfidence < 0.0) {
            $minConfidence = 0.0;
        }
        if ($minConfidence > 1.0) {
            $minConfidence = 1.0;
        }
        $this->minConfidence = $minConfidence;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    public function interpret(string $domain, string $query, array $context = [], ?string $requestId = null): ?array
    {
        $detailed = $this->interpretDetailed($domain, $query, $context, $requestId);
        if ($detailed === null) {
            return null;
        }

        $filters = $detailed['filters'] ?? null;
        return is_array($filters) ? $filters : null;
    }

    /**
     * @param array<string, mixed> $context
     * @return array{filters: array<string, mixed>, confidence: float, warnings: array<int, string>, requestId: string}|null
     */
    public function interpretDetailed(string $domain, string $query, array $context = [], ?string $requestId = null): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $payload = [
            'domain' => $domain,
            'query' => substr($query, 0, 300),
            'context' => $context,
        ];

        $body = json_encode($payload);
        if ($body === false) {
            return null;
        }

        $httpContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $this->buildHeaders($requestId),
                'content' => $body,
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $url = $this->baseUrl . '/v1/nl-search/interpret';
        $raw = @file_get_contents($url, false, $httpContext);
        if ($raw === false) {
            return null;
        }

        $statusCode = $this->extractStatusCode($http_response_header ?? []);
        if ($statusCode >= 400) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $filters = $decoded['interpreted_filters'] ?? null;
        if (!is_array($filters)) {
            return null;
        }

        $confidence = (float) ($decoded['confidence'] ?? 0.0);
        if ($confidence < $this->minConfidence) {
            return null;
        }

        $warnings = $decoded['warnings'] ?? [];
        if (!is_array($warnings)) {
            $warnings = [];
        }

        $responseRequestId = (string) ($decoded['request_id'] ?? ($requestId ?? ''));

        return [
            'filters' => $filters,
            'confidence' => $confidence,
            'warnings' => array_values(array_filter(array_map('strval', $warnings), static fn(string $w): bool => $w !== '')),
            'requestId' => $responseRequestId,
        ];
    }

    private function buildHeaders(?string $requestId): string
    {
        $headers = "Content-Type: application/json\r\nAccept: application/json\r\n";
        if (is_string($requestId) && trim($requestId) !== '') {
            $headers .= 'X-Request-Id: ' . trim($requestId) . "\r\n";
        }

        return $headers;
    }

    /**
     * @param list<string> $headers
     */
    private function extractStatusCode(array $headers): int
    {
        $statusLine = $headers[0] ?? '';
        if (preg_match('/HTTP\/\S+\s+(\d{3})/', $statusLine, $m) !== 1) {
            return 0;
        }

        return (int) $m[1];
    }
}
