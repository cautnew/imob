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

ob_start();
?>
<h1>Comparar imóveis</h1>

<?php if (session('comparison_error')) { ?>
<div class="alert alert-error"><?= e(session('comparison_error')) ?></div>
<?php } ?>

<?php if ($properties->isEmpty()) { ?>
<div class="empty-state">Você ainda não adicionou imóveis para comparar. Use o botão "Comparar" nos cartões de imóvel.</div>
<?php } else { ?>
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
    <tr>
        <th class="attr-label">Preço principal</th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e(portal_money($property->principalPrice()?->amount)) ?></td>
        <?php } ?>
    </tr>
    <?php foreach ($comparablePriceTypes as $priceType) { ?>
    <tr>
        <th class="attr-label"><?= e($priceType->name) ?></th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e($priceForType($property, $priceType->id)) ?></td>
        <?php } ?>
    </tr>
    <?php } ?>
    <tr>
        <th class="attr-label">Tipo</th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e($property->type->label()) ?></td>
        <?php } ?>
    </tr>
    <tr>
        <th class="attr-label">Finalidade</th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e($property->purpose->label()) ?></td>
        <?php } ?>
    </tr>
    <tr>
        <th class="attr-label">Bairro / Cidade</th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e($property->neighborhood) ?>, <?= e($property->city) ?></td>
        <?php } ?>
    </tr>
    <tr>
        <th class="attr-label">Área total</th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e(number_format((float) $property->total_area, 2, ',', '.')) ?> m²</td>
        <?php } ?>
    </tr>
    <?php foreach ($comparableAttributes as $attribute) { ?>
    <tr>
        <th class="attr-label"><?= e($attribute->name) ?></th>
        <?php foreach ($properties as $property) { ?>
        <td><?= e((string) $attributeValueFor($property, $attribute->id)) ?></td>
        <?php } ?>
    </tr>
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
