<?php
include __DIR__.'/../partials/helpers.php';
$favoriteIds ??= [];
$comparisonIds ??= [];
ob_start();
?>
<h1>Busca avançada</h1>
<p>Combine os filtros abaixo para encontrar o imóvel ideal.</p>

<div class="filters" style="max-width: 640px;">
    <?php include __DIR__.'/../partials/filters-form.php'; ?>
</div>

<?php
$content = ob_get_clean();
$head = [
    'title' => 'Busca avançada — '.$company->name,
    'description' => 'Busca avançada de imóveis em '.$company->name.'.',
    'canonical' => portal_route('search', $company->slug),
];
include __DIR__.'/../layout.php';
