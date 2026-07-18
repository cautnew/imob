<?php

namespace App\Http\Controllers\PublicPortal\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Reads and writes the anonymous-visitor favorites/comparison cookies.
 *
 * Each cookie is a JSON object keyed by company slug (a visitor may browse
 * more than one tenant's portal in the same browser):
 * {"acme-imoveis": [12, 45], "outra-imobiliaria": [3]}
 */
trait ManagesVisitorCookies
{
    /**
     * @return array<string, list<int>>
     */
    private function readCookie(Request $request, string $name): array
    {
        $raw = $request->cookie($name);

        if (! $raw) {
            return [];
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, list<int>>  $data
     */
    private function writeCookie(string $name, array $data, int $minutes): void
    {
        Cookie::queue($name, json_encode($data), $minutes);
    }

    /**
     * @return list<int>
     */
    private function idsFor(array $cookieData, string $companySlug): array
    {
        return array_values(array_unique($cookieData[$companySlug] ?? []));
    }
}
