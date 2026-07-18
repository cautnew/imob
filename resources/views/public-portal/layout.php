<?php
/**
 * Shared document shell for every public-portal page. Content views build
 * their markup into $content (output buffering) and set $head before
 * including this file. See partials/head-meta.php for the <head> tags.
 */
$head ??= [];
$favoriteIds ??= [];
$comparisonIds ??= [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php include __DIR__.'/partials/head-meta.php'; ?>
<style>
    :root { color-scheme: light; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1f2933; background: #f6f7f9; line-height: 1.5; }
    a { color: inherit; }
    .container { max-width: 1120px; margin: 0 auto; padding: 0 1.25rem; }
    header.site-header { background: #14213d; color: #fff; }
    header.site-header .container { display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; padding-bottom: 1rem; flex-wrap: wrap; gap: .75rem; }
    header.site-header a { text-decoration: none; }
    .brand { font-size: 1.25rem; font-weight: 700; }
    nav.site-nav { display: flex; gap: 1.25rem; flex-wrap: wrap; }
    nav.site-nav a { font-size: .95rem; opacity: .9; }
    nav.site-nav a:hover { opacity: 1; text-decoration: underline; }
    main { min-height: 60vh; padding: 2rem 0 3rem; }
    footer.site-footer { background: #14213d; color: #cbd2e0; padding: 1.5rem 0; margin-top: 2rem; font-size: .875rem; }
    h1 { font-size: 1.75rem; margin: 0 0 1rem; }
    h2 { font-size: 1.25rem; margin: 0 0 .75rem; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; }
    .card { background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); display: flex; flex-direction: column; }
    .card img.cover, .card .cover-placeholder { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; background: #e4e7eb; display: flex; align-items: center; justify-content: center; color: #9aa5b1; font-size: .85rem; }
    .card .card-body { padding: .875rem 1rem 1rem; display: flex; flex-direction: column; gap: .4rem; flex: 1; }
    .card .price { font-size: 1.1rem; font-weight: 700; color: #14213d; }
    .card .meta { font-size: .85rem; color: #616e7c; }
    .card .actions { display: flex; gap: .5rem; margin-top: auto; padding-top: .5rem; }
    .btn { display: inline-block; padding: .45rem .85rem; border-radius: .375rem; border: 1px solid #14213d; background: #14213d; color: #fff; font-size: .85rem; cursor: pointer; text-decoration: none; text-align: center; }
    .btn.btn-outline { background: transparent; color: #14213d; }
    .btn.btn-small { padding: .3rem .6rem; font-size: .8rem; }
    form.inline { display: inline; }
    .filters { background: #fff; border-radius: .5rem; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
    .filters fieldset { border: none; padding: 0; margin: 0 0 1rem; }
    .filters legend { font-weight: 600; padding: 0 0 .35rem; }
    .filters label { display: block; font-size: .875rem; margin-bottom: .25rem; }
    .filters .row { display: flex; gap: .75rem; flex-wrap: wrap; }
    .filters .field { flex: 1; min-width: 140px; }
    .filters input[type=text], .filters input[type=number], .filters input[type=date], .filters select { width: 100%; padding: .4rem .5rem; border: 1px solid #d3d9e0; border-radius: .3rem; }
    .checkbox-list { display: flex; flex-wrap: wrap; gap: .5rem 1rem; }
    .checkbox-list label { display: flex; align-items: center; gap: .35rem; font-weight: 400; }
    .layout-with-sidebar { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 800px) { .layout-with-sidebar { grid-template-columns: 1fr; } }
    .pagination { display: flex; gap: .5rem; margin-top: 1.5rem; flex-wrap: wrap; }
    .empty-state { background: #fff; border-radius: .5rem; padding: 2rem; text-align: center; color: #616e7c; }
    table.compare-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; }
    table.compare-table th, table.compare-table td { border-bottom: 1px solid #e4e7eb; padding: .6rem .75rem; text-align: left; vertical-align: top; }
    table.compare-table th.attr-label { background: #f6f7f9; width: 200px; }
    table.compare-table tr.row-diff td, table.compare-table tr.row-diff th.attr-label { background: #fff6d9; }
    table.compare-table tr.section-row th.attr-label { background: #eef2f7; color: #14213d; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
    .comparison-legend { display: flex; align-items: center; gap: .4rem; font-size: .85rem; color: #616e7c; margin-bottom: .75rem; }
    .comparison-legend .swatch { display: inline-block; width: .9rem; height: .9rem; border-radius: .2rem; background: #fff6d9; border: 1px solid #e4e7eb; }
    .alert { padding: .75rem 1rem; border-radius: .4rem; margin-bottom: 1rem; }
    .alert-error { background: #fde8e8; color: #9b2c2c; }
    .badge { display: inline-block; padding: .1rem .5rem; border-radius: 1rem; background: #eef2ff; color: #14213d; font-size: .75rem; }
</style>
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="brand" href="<?= e(portal_route('home', $company->slug)) ?>"><?= e($company->name) ?></a>
        <nav class="site-nav">
            <a href="<?= e(portal_route('home', $company->slug)) ?>">Início</a>
            <a href="<?= e(portal_route('properties.index', $company->slug)) ?>">Imóveis</a>
            <a href="<?= e(portal_route('search', $company->slug)) ?>">Busca avançada</a>
            <a href="<?= e(portal_route('favorites.index', $company->slug)) ?>">Favoritos<?= count($favoriteIds) ? ' ('.count($favoriteIds).')' : '' ?></a>
            <a href="<?= e(portal_route('comparison.index', $company->slug)) ?>">Comparação<?= count($comparisonIds) ? ' ('.count($comparisonIds).')' : '' ?></a>
        </nav>
    </div>
</header>
<main>
    <div class="container">
        <?= $content ?>
    </div>
</main>
<footer class="site-footer">
    <div class="container">
        <p><?= e($company->name) ?><?php if ($company->phone) { ?> &middot; <?= e($company->phone) ?><?php } ?><?php if ($company->address) { ?> &middot; <?= e($company->address) ?><?php } ?></p>
    </div>
</footer>
</body>
</html>
