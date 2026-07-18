<?php
include __DIR__.'/../partials/helpers.php';
$favoriteIds ??= [];
$comparisonIds ??= [];
ob_start();
?>
<h1><?= e($company->name) ?></h1>
<p>
    <?php if ($company->phone) { ?><?= e($company->phone) ?><?php } ?>
    <?php if ($company->address) { ?> &middot; <?= e($company->address) ?><?php } ?>
</p>

<p><a class="btn" href="<?= e(portal_route('properties.index', $company->slug)) ?>">Ver todos os imóveis</a></p>

<h2>Imóveis em destaque</h2>
<?php if ($featuredProperties->isEmpty()) { ?>
<div class="empty-state">Nenhum imóvel publicado no momento.</div>
<?php } else { ?>
<div class="grid">
    <?php foreach ($featuredProperties as $property) { ?>
    <?php include __DIR__.'/../partials/property-card.php'; ?>
    <?php } ?>
</div>
<?php } ?>

<?php
$content = ob_get_clean();

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgent',
    'name' => $company->name,
    'telephone' => $company->phone,
    'address' => $company->address,
    'url' => portal_route('home', $company->slug),
];

$head = [
    'title' => $company->name,
    'description' => 'Confira os imóveis disponíveis em '.$company->name.'.',
    'canonical' => portal_route('home', $company->slug),
    'jsonLd' => $jsonLd,
];

include __DIR__.'/../layout.php';
