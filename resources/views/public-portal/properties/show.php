<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

include __DIR__.'/../partials/helpers.php';

$price = $property->principalPrice();
$cover = portal_cover_image_url($property);
$isFavorite = in_array($property->id, $favoriteIds, true);
$isComparing = in_array($property->id, $comparisonIds, true);

$featuresByCategory = $property->features->groupBy(fn ($feature) => $feature->featureCategory?->name ?? 'Outros');

$attributesByGroup = $property->attributeValues
    ->groupBy('property_attribute_id')
    ->map(function ($values) {
        $attribute = $values->first()->propertyAttribute;
        $display = $values->count() > 1
            ? $values->pluck('propertyAttributeOption.value')->filter()->implode(', ')
            : ($values->first()->propertyAttributeOption?->value ?? $values->first()->value);

        return ['attribute' => $attribute, 'value' => $display];
    })
    ->filter(fn ($row) => $row['attribute'] !== null && $row['value'] !== null && $row['value'] !== '');

ob_start();
?>
<p><a href="<?= e(portal_route('properties.index', $company->slug)) ?>">&larr; Voltar aos imóveis</a></p>

<h1><?= e($property->title) ?></h1>
<p class="meta"><?= e($property->neighborhood) ?>, <?= e($property->city) ?>/<?= e($property->state) ?></p>

<?php if ($cover) { ?>
<img src="<?= e($cover) ?>" alt="<?= e($property->title) ?>" style="width:100%;max-height:420px;object-fit:cover;border-radius:.5rem;margin-bottom:1rem;">
<?php } ?>

<?php if ($property->media->count() > 1) { ?>
<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); margin-bottom: 1.5rem;">
    <?php foreach ($property->media as $media) { ?>
    <img src="<?= e(Storage::disk($media->disk)->url($media->path)) ?>" alt="<?= e($property->title) ?>" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:.375rem;">
    <?php } ?>
</div>
<?php } ?>

<div class="price" style="font-size:1.5rem;margin-bottom:1rem;">
    <?= e(portal_money($price?->amount)) ?><?php if ($price && $price->frequency->value !== 'unico') { ?> <small>/ <?= e($price->frequency->label()) ?></small><?php } ?>
</div>

<div class="actions" style="margin-bottom:1.5rem;">
    <form class="inline" method="POST" action="<?= e(portal_route($isFavorite ? 'favorites.destroy' : 'favorites.store', $company->slug, ['propertySlug' => $property->slug])) ?>">
        <?= csrf_field() ?>
        <?php if ($isFavorite) { ?><?= method_field('DELETE') ?><?php } ?>
        <button type="submit" class="btn <?= $isFavorite ? '' : 'btn-outline' ?>"><?= $isFavorite ? '♥ Nos favoritos' : '♡ Favoritar' ?></button>
    </form>
    <form class="inline" method="POST" action="<?= e(portal_route($isComparing ? 'comparison.destroy' : 'comparison.store', $company->slug, ['propertySlug' => $property->slug])) ?>">
        <?= csrf_field() ?>
        <?php if ($isComparing) { ?><?= method_field('DELETE') ?><?php } ?>
        <button type="submit" class="btn btn-outline"><?= $isComparing ? 'Remover da comparação' : '+ Adicionar à comparação' ?></button>
    </form>
</div>

<?php
$shareUrl = portal_route('properties.show', $company->slug, ['propertySlug' => $property->slug]);
$shareTitle = $property->title;
include __DIR__.'/../partials/share-buttons.php';
?>

<h2>Detalhes</h2>
<table class="compare-table" style="margin-bottom:1.5rem;">
    <tr><th class="attr-label">Finalidade</th><td><?= e($property->purpose->label()) ?></td></tr>
    <tr><th class="attr-label">Tipo</th><td><?= e($property->type->label()) ?></td></tr>
    <tr><th class="attr-label">Área total</th><td><?= e(number_format((float) $property->total_area, 2, ',', '.')) ?> m²</td></tr>
    <?php if ($property->built_area) { ?>
    <tr><th class="attr-label">Área construída</th><td><?= e(number_format((float) $property->built_area, 2, ',', '.')) ?> m²</td></tr>
    <?php } ?>
    <tr><th class="attr-label">Endereço</th><td><?= e($property->street) ?><?php if ($property->number) { ?>, <?= e($property->number) ?><?php } ?> &mdash; <?= e($property->neighborhood) ?>, <?= e($property->city) ?>/<?= e($property->state) ?></td></tr>
    <?php foreach ($attributesByGroup as $row) { ?>
    <tr><th class="attr-label"><?= e($row['attribute']->name) ?></th><td><?= e((string) $row['value']) ?></td></tr>
    <?php } ?>
</table>

<?php if ($property->description) { ?>
<h2>Descrição</h2>
<p><?= nl2br(e($property->description)) ?></p>
<?php } ?>

<?php if ($featuresByCategory->isNotEmpty()) { ?>
<h2>Características</h2>
<?php foreach ($featuresByCategory as $categoryName => $features) { ?>
<p><strong><?= e($categoryName) ?>:</strong> <?= e($features->pluck('name')->implode(', ')) ?></p>
<?php } ?>
<?php } ?>

<?php
$content = ob_get_clean();

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $property->title,
    'description' => $property->description,
    'image' => $cover,
    'offers' => [
        '@type' => 'Offer',
        'price' => $price ? (string) $price->amount : null,
        'priceCurrency' => 'BRL',
        'availability' => 'https://schema.org/InStock',
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => trim($property->street.' '.$property->number),
        'addressLocality' => $property->city,
        'addressRegion' => $property->state,
        'postalCode' => $property->zip_code,
        'addressCountry' => 'BR',
    ],
];

$head = [
    'title' => $property->title.' — '.$company->name,
    'description' => $property->description ? Str::limit(strip_tags($property->description), 155) : $property->title,
    'canonical' => portal_route('properties.show', $company->slug, ['propertySlug' => $property->slug]),
    'image' => $cover,
    'jsonLd' => $jsonLd,
];

include __DIR__.'/../layout.php';
