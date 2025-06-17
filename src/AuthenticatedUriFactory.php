<?php

declare(strict_types=1);

namespace Petersons\D2L;

use Carbon\CarbonImmutable;

final class AuthenticatedUriFactory
{
    public function __construct(
        private string $host,
        private string $appId,
        private string $appKey,
        private string $lmsUserId,
        private string $lmsUserKey,
    ) {}

    public function createAuthenticatedUri(string $url, string $httpMethod): string
    {
        $parsedUrl = parse_url($url);

        $queryString = $this->getQueryString($httpMethod, $parsedUrl['path']);

        $uri = sprintf('%s%s%s', $this->host, $parsedUrl['path'], $queryString);
        if (isset($parsedUrl['query'])) {
            $uri .= '&' . $parsedUrl['query'];
        }

        return $uri;
    }

    public function getQueryParametersAsArray(string $httpMethod, string $path): array
    {
        $adjustedTimestampSeconds = $this->getAdjustedTimestampInSeconds();

        $signature = $this->formatSignature($path, $httpMethod, $adjustedTimestampSeconds);

        return $this->buildAuthenticatedUriQueryArray($signature, $adjustedTimestampSeconds);
    }

    private function getQueryString(string $httpMethod, string $path): string
    {
        $adjustedTimestampSeconds = $this->getAdjustedTimestampInSeconds();

        $signature = $this->formatSignature($path, $httpMethod, $adjustedTimestampSeconds);

        return $this->buildAuthenticatedUriQueryString($signature, $adjustedTimestampSeconds);
    }

    private function getAdjustedTimestampInSeconds(): int
    {
        return CarbonImmutable::now()->getTimestamp();
    }

    private function formatSignature(string $path, string $httpMethod, int $timestampSeconds): string
    {
        return strtoupper($httpMethod) . '&' . urldecode(strtolower($path)) . '&' . $timestampSeconds;
    }

    private function buildAuthenticatedUriQueryArray(string $signature, int $timestamp): array
    {
        return [
            D2LConstants::APP_ID_PARAMETER => $this->appId,
            D2LConstants::USER_ID_PARAMETER => $this->lmsUserId,
            D2LConstants::SIGNATURE_BY_APP_KEY_PARAMETER => D2LSigner::getBase64HashString($this->appKey, $signature),
            D2LConstants::SIGNATURE_BY_USER_KEY_PARAMETER => D2LSigner::getBase64HashString($this->lmsUserKey, $signature),
            D2LConstants::TIMESTAMP_PARAMETER => $timestamp,
        ];
    }

    private function buildAuthenticatedUriQueryString(string $signature, int $timestamp): string
    {
        $queryString  = '?' . D2LConstants::APP_ID_PARAMETER . '=' . $this->appId;
        $queryString .= '&' . D2LConstants::USER_ID_PARAMETER . '=' . $this->lmsUserId;
        $queryString .= '&' . D2LConstants::SIGNATURE_BY_APP_KEY_PARAMETER;
        $queryString .= '=' . D2LSigner::getBase64HashString($this->appKey, $signature);
        $queryString .= '&' . D2LConstants::SIGNATURE_BY_USER_KEY_PARAMETER;
        $queryString .= '=' . D2LSigner::getBase64HashString($this->lmsUserKey, $signature);
        $queryString .= '&' . D2LConstants::TIMESTAMP_PARAMETER . '=' . $timestamp;

        return $queryString;
    }
}
