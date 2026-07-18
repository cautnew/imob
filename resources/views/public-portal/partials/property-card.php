<?php

use App\Models\Property;

/** @var Property $property expected in scope */
$cover = portal_cover_image_url($property);
$price = $property->principalPrice();
$isFavorite = in_array($property->id, $favoriteIds ?? [], true);
$isComparing = in_array($property->id, $comparisonIds ?? [], true);
?>
<div class="card">
    <a href="<?= e(portal_route('properties.show', $company->slug, ['propertySlug' => $property->slug])) ?>">
        <?php if ($cover) { ?>
        <img class="cover" src="<?= e($cover) ?>" alt="<?= e($property->title) ?>" loading="lazy">
        <?php } else { ?>
        <div class="cover-placeholder">Sem foto</div>
        <?php } ?>
    </a>
    <div class="card-body">
        <span class="badge"><?= e($property->type->label()) ?> &middot; <?= e($property->purpose->label()) ?></span>
        <strong><a href="<?= e(portal_route('properties.show', $company->slug, ['propertySlug' => $property->slug])) ?>"><?= e($property->title) ?></a></strong>
        <div class="meta"><?= e($property->neighborhood) ?>, <?= e($property->city) ?>/<?= e($property->state) ?></div>
        <div class="price"><?= e(portal_money($price?->amount)) ?><?php if ($price && $price->frequency->value !== 'unico') { ?> <small>/ <?= e($price->frequency->label()) ?></small><?php } ?></div>
        <div class="actions">
            <form class="inline" method="POST" action="<?= e(portal_route($isFavorite ? 'favorites.destroy' : 'favorites.store', $company->slug, ['propertySlug' => $property->slug])) ?>">
                <?= csrf_field() ?>
                <?php if ($isFavorite) { ?><?= method_field('DELETE') ?><?php } ?>
                <button type="submit" class="btn btn-small <?= $isFavorite ? '' : 'btn-outline' ?>"><?= $isFavorite ? '♥ Favorito' : '♡ Favoritar' ?></button>
            </form>
            <form class="inline" method="POST" action="<?= e(portal_route($isComparing ? 'comparison.destroy' : 'comparison.store', $company->slug, ['propertySlug' => $property->slug])) ?>">
                <?= csrf_field() ?>
                <?php if ($isComparing) { ?><?= method_field('DELETE') ?><?php } ?>
                <button type="submit" class="btn btn-small btn-outline"><?= $isComparing ? 'Remover comparação' : '+ Comparar' ?></button>
            </form>
        </div>
    </div>
</div>
