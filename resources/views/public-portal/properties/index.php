<?php
include __DIR__.'/../partials/helpers.php';
ob_start();
?>
<h1>Imóveis em <?= e($company->name) ?></h1>

<div class="layout-with-sidebar">
    <aside class="filters">
        <?php include __DIR__.'/../partials/filters-form.php'; ?>
    </aside>

    <section>
        <?php if ($properties->isEmpty()) { ?>
        <div class="empty-state">Nenhum imóvel encontrado para os filtros selecionados.</div>
        <?php } else { ?>
        <div class="grid">
            <?php foreach ($properties as $property) { ?>
            <?php include __DIR__.'/../partials/property-card.php'; ?>
            <?php } ?>
        </div>
        <?php include __DIR__.'/../partials/pagination.php'; ?>
        <?php } ?>
    </section>
</div>

<?php
$content = ob_get_clean();
$head = [
    'title' => 'Imóveis em '.$company->name,
    'description' => 'Confira os imóveis disponíveis para venda e aluguel em '.$company->name.'.',
    'canonical' => portal_route('properties.index', $company->slug),
];
include __DIR__.'/../layout.php';
