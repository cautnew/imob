<?php
include __DIR__.'/../partials/helpers.php';
$comparisonIds ??= [];
ob_start();
?>
<h1>Seus favoritos</h1>

<?php if (session('favorites_error')) { ?>
<div class="alert alert-error"><?= e(session('favorites_error')) ?></div>
<?php } ?>

<?php if ($properties->isEmpty()) { ?>
<div class="empty-state">Você ainda não favoritou nenhum imóvel.</div>
<?php } else { ?>
<div class="grid">
    <?php foreach ($properties as $property) { ?>
    <?php include __DIR__.'/../partials/property-card.php'; ?>
    <?php } ?>
</div>
<?php } ?>

<?php
$content = ob_get_clean();
$head = [
    'title' => 'Favoritos — '.$company->name,
    'canonical' => portal_route('favorites.index', $company->slug),
];
include __DIR__.'/../layout.php';
