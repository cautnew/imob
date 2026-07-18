<?php
/**
 * Reads the $head array set by the calling content view:
 * ['title', 'description', 'canonical', 'image' (optional), 'jsonLd' (optional array)].
 */
$title = $head['title'] ?? $company->name;
$description = $head['description'] ?? 'Confira os imóveis disponíveis em '.$company->name.'.';
$canonical = $head['canonical'] ?? url()->current();
$image = $head['image'] ?? null;
?>
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($image) { ?>
<meta property="og:image" content="<?= e($image) ?>">
<?php } ?>

<meta name="twitter:card" content="<?= $image ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<?php if ($image) { ?>
<meta name="twitter:image" content="<?= e($image) ?>">
<?php } ?>

<?php if (! empty($head['jsonLd'])) { ?>
<?php include __DIR__.'/json-ld.php'; ?>
<?php } ?>
