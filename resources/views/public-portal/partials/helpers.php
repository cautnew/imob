<?php

use App\Models\Property;
use Illuminate\Support\Facades\Storage;

if (! function_exists('portal_money')) {
    function portal_money(float|string|null $amount): string
    {
        if ($amount === null) {
            return 'Consulte';
        }

        return 'R$ '.number_format((float) $amount, 2, ',', '.');
    }
}

if (! function_exists('portal_cover_image_url')) {
    function portal_cover_image_url(Property $property): ?string
    {
        $cover = $property->media->first();

        if (! $cover) {
            return null;
        }

        return Storage::disk($cover->disk)->url($cover->path);
    }
}

if (! function_exists('portal_route')) {
    function portal_route(string $name, string $companySlug, array $params = []): string
    {
        return route("public.{$name}", ['companySlug' => $companySlug, ...$params]);
    }
}
