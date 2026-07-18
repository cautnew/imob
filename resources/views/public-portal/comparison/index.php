<?php
include __DIR__.'/../partials/helpers.php';
$favoriteIds ??= [];

$attributeValueFor = function ($property, $attributeId) {
    $values = $property->attributeValues->where('property_attribute_id', $attributeId);

    if ($values->isEmpty()) {
        return '—';
    }

    if ($values->count() > 1) {
        return $values->pluck('propertyAttributeOption.value')->filter()->implode(', ') ?: '—';
    }

    return $values->first()->propertyAttributeOption?->value ?? $values->first()->value ?? '—';
};

$priceForType = function ($property, $priceTypeId) {
    $price = $property->prices->firstWhere('price_type_id', $priceTypeId);

    return $price ? portal_money($price->amount) : '—';
};

$hasFeature = fn ($property, $featureId) => $property->features->contains('id', $featureId) ? 'Sim' : 'Não';

// Renders one comparison row and highlights it when the values differ across properties.
$renderRow = function (string $label, array $values) {
    $diff = count(array_unique($values)) > 1;
    ?>
    <tr<?= $diff ? ' class="row-diff"' : '' ?>>
        <th class="attr-label"><?= e($label) ?></th>
        <?php foreach ($values as $value) { ?>
        <td><?= e($value) ?></td>
        <?php } ?>
    </tr>
    <?php
};

ob_start();
?>
<h1>Comparar imóveis</h1>

<?php if (session('comparison_error')) { ?>
<div class="alert alert-error"><?= e(session('comparison_error')) ?></div>
<?php } ?>

<?php if ($properties->isEmpty()) { ?>
<div class="empty-state">Você ainda não adicionou imóveis para comparar. Use o botão "Comparar" nos cartões de imóvel.</div>
<?php } else { ?>
<p class="comparison-legend"><span class="swatch"></span> Linhas destacadas indicam diferenças entre os imóveis.</p>
<div style="overflow-x:auto;">
<table class="compare-table">
    <tr>
        <th class="attr-label">Imóvel</th>
        <?php foreach ($properties as $property) { ?>
        <th>
            <a href="<?= e(portal_route('properties.show', $company->slug, ['propertySlug' => $property->slug])) ?>"><?= e($property->title) ?></a><br>
            <form class="inline" method="POST" action="<?= e(portal_route('comparison.destroy', $company->slug, ['propertySlug' => $property->slug])) ?>">
                <?= csrf_field() ?><?= method_field('DELETE') ?>
                <button type="submit" class="btn btn-small btn-outline">Remover</button>
            </form>
        </th>
        <?php } ?>
    </tr>
    <?php $renderRow('Preço principal', $properties->map(fn ($property) => portal_money($property->principalPrice()?->amount))->all()); ?>
    <?php foreach ($comparablePriceTypes as $priceType) { ?>
        <?php $renderRow($priceType->name, $properties->map(fn ($property) => $priceForType($property, $priceType->id))->all()); ?>
    <?php } ?>
    <?php $renderRow('Tipo', $properties->map(fn ($property) => $property->type->label())->all()); ?>
    <?php $renderRow('Finalidade', $properties->map(fn ($property) => $property->purpose->label())->all()); ?>
    <?php $renderRow('Bairro / Cidade', $properties->map(fn ($property) => "{$property->neighborhood}, {$property->city}")->all()); ?>
    <?php $renderRow('Área total', $properties->map(fn ($property) => number_format((float) $property->total_area, 2, ',', '.').' m²')->all()); ?>
    <?php if ($comparisonFeatures->isNotEmpty()) { ?>
    <tr class="section-row"><th class="attr-label" colspan="<?= count($properties) + 1 ?>">Características</th></tr>
    <?php foreach ($comparisonFeatures as $feature) { ?>
        <?php $renderRow($feature->name, $properties->map(fn ($property) => $hasFeature($property, $feature->id))->all()); ?>
    <?php } ?>
    <?php } ?>
    <?php if ($comparableAttributes->isNotEmpty()) { ?>
    <tr class="section-row"><th class="attr-label" colspan="<?= count($properties) + 1 ?>">Atributos personalizados</th></tr>
    <?php foreach ($comparableAttributes as $attribute) { ?>
        <?php $renderRow($attribute->name, $properties->map(fn ($property) => (string) $attributeValueFor($property, $attribute->id))->all()); ?>
    <?php } ?>
    <?php } ?>
</table>
</div>
<?php } ?>

<?php
$content = ob_get_clean();
$head = [
    'title' => 'Comparação de imóveis — '.$company->name,
    'canonical' => portal_route('comparison.index', $company->slug),
];
include __DIR__.'/../layout.php';
