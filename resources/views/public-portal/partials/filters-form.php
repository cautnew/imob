<?php
/**
 * Shared filter fields, used by both properties/index.php (sidebar) and
 * properties/search.php (advanced search form). Expects $filters (from
 * BuildsFilterOptions::filterOptions()) and, optionally, $selected (the
 * current query values) in scope.
 */
$selected ??= [];
$sel = fn (string $key, mixed $default = null) => $selected[$key] ?? $default;
$selArr = fn (string $key) => (array) ($selected[$key] ?? []);
$selAttr = fn (int $attributeId, ?string $sub = null) => $sub
    ? ($selected['atributos'][$attributeId][$sub] ?? '')
    : ($selected['atributos'][$attributeId] ?? null);
?>
<form method="GET" action="<?= e(portal_route('properties.index', $company->slug)) ?>">
    <fieldset>
        <legend>Finalidade</legend>
        <select name="purpose">
            <option value="">Todas</option>
            <?php foreach ($filters['purposes'] as $purpose) { ?>
            <option value="<?= e($purpose['value']) ?>" <?= $sel('purpose') === $purpose['value'] ? 'selected' : '' ?>><?= e($purpose['label']) ?></option>
            <?php } ?>
        </select>
    </fieldset>

    <fieldset>
        <legend>Tipo de imóvel</legend>
        <div class="checkbox-list">
            <?php foreach ($filters['types'] as $type) { ?>
            <label><input type="checkbox" name="tipo[]" value="<?= e($type['value']) ?>" <?= in_array($type['value'], $selArr('tipo'), true) ? 'checked' : '' ?>> <?= e($type['label']) ?></label>
            <?php } ?>
        </div>
    </fieldset>

    <fieldset>
        <legend>Preço</legend>
        <div class="row">
            <div class="field">
                <label for="preco_min">Mínimo</label>
                <input type="number" step="0.01" min="0" id="preco_min" name="preco_min" value="<?= e($sel('preco_min', '')) ?>" placeholder="<?= e($filters['priceRange']['min'] !== null ? portal_money($filters['priceRange']['min']) : '') ?>">
            </div>
            <div class="field">
                <label for="preco_max">Máximo</label>
                <input type="number" step="0.01" min="0" id="preco_max" name="preco_max" value="<?= e($sel('preco_max', '')) ?>" placeholder="<?= e($filters['priceRange']['max'] !== null ? portal_money($filters['priceRange']['max']) : '') ?>">
            </div>
        </div>
    </fieldset>

    <?php if ($filters['neighborhoods']->isNotEmpty()) { ?>
    <fieldset>
        <legend>Bairro</legend>
        <div class="checkbox-list">
            <?php foreach ($filters['neighborhoods'] as $neighborhood) { ?>
            <label><input type="checkbox" name="bairro[]" value="<?= e($neighborhood) ?>" <?= in_array($neighborhood, $selArr('bairro'), true) ? 'checked' : '' ?>> <?= e($neighborhood) ?></label>
            <?php } ?>
        </div>
    </fieldset>
    <?php } ?>

    <?php if ($filters['cities']->isNotEmpty()) { ?>
    <fieldset>
        <legend>Cidade</legend>
        <div class="checkbox-list">
            <?php foreach ($filters['cities'] as $city) { ?>
            <label><input type="checkbox" name="cidade[]" value="<?= e($city) ?>" <?= in_array($city, $selArr('cidade'), true) ? 'checked' : '' ?>> <?= e($city) ?></label>
            <?php } ?>
        </div>
    </fieldset>
    <?php } ?>

    <?php foreach ($filters['featureCategories'] as $category) { ?>
    <?php if ($category->features->isEmpty()) {
        continue;
    } ?>
    <fieldset>
        <legend><?= e($category->name) ?></legend>
        <div class="checkbox-list">
            <?php foreach ($category->features as $feature) { ?>
            <label><input type="checkbox" name="caracteristicas[]" value="<?= e((string) $feature->id) ?>" <?= in_array((string) $feature->id, $selArr('caracteristicas'), true) ? 'checked' : '' ?>> <?= e($feature->name) ?></label>
            <?php } ?>
        </div>
    </fieldset>
    <?php } ?>

    <?php foreach ($filters['attributes'] as $attribute) { ?>
    <fieldset>
        <legend><?= e($attribute->name) ?></legend>
        <?php if (in_array($attribute->type->value, ['inteiro', 'decimal'], true)) { ?>
        <div class="row">
            <div class="field">
                <label>Mínimo</label>
                <input type="number" step="<?= $attribute->type->value === 'decimal' ? '0.01' : '1' ?>" name="atributos[<?= e((string) $attribute->id) ?>][min]" value="<?= e($selAttr($attribute->id, 'min')) ?>">
            </div>
            <div class="field">
                <label>Máximo</label>
                <input type="number" step="<?= $attribute->type->value === 'decimal' ? '0.01' : '1' ?>" name="atributos[<?= e((string) $attribute->id) ?>][max]" value="<?= e($selAttr($attribute->id, 'max')) ?>">
            </div>
        </div>
        <?php } elseif ($attribute->type->value === 'boolean') { ?>
        <label><input type="checkbox" name="atributos[<?= e((string) $attribute->id) ?>]" value="1" <?= $selAttr($attribute->id) ? 'checked' : '' ?>> Sim</label>
        <?php } elseif ($attribute->type->value === 'data') { ?>
        <div class="row">
            <div class="field">
                <label>De</label>
                <input type="date" name="atributos[<?= e((string) $attribute->id) ?>][de]" value="<?= e($selAttr($attribute->id, 'de')) ?>">
            </div>
            <div class="field">
                <label>Até</label>
                <input type="date" name="atributos[<?= e((string) $attribute->id) ?>][ate]" value="<?= e($selAttr($attribute->id, 'ate')) ?>">
            </div>
        </div>
        <?php } elseif ($attribute->type->value === 'select') { ?>
        <select name="atributos[<?= e((string) $attribute->id) ?>]">
            <option value="">Todos</option>
            <?php foreach ($attribute->options as $option) { ?>
            <option value="<?= e((string) $option->id) ?>" <?= (int) $selAttr($attribute->id) === $option->id ? 'selected' : '' ?>><?= e($option->value) ?></option>
            <?php } ?>
        </select>
        <?php } elseif ($attribute->type->value === 'multiselect') { ?>
        <div class="checkbox-list">
            <?php foreach ($attribute->options as $option) { ?>
            <label><input type="checkbox" name="atributos[<?= e((string) $attribute->id) ?>][]" value="<?= e((string) $option->id) ?>" <?= in_array((string) $option->id, array_map('strval', (array) $selAttr($attribute->id)), true) ? 'checked' : '' ?>> <?= e($option->value) ?></label>
            <?php } ?>
        </div>
        <?php } else { ?>
        <input type="text" name="atributos[<?= e((string) $attribute->id) ?>]" value="<?= e($selAttr($attribute->id) ?? '') ?>">
        <?php } ?>
    </fieldset>
    <?php } ?>

    <fieldset>
        <legend>Ordenar por</legend>
        <select name="ordenar">
            <option value="recentes" <?= $sel('ordenar', 'recentes') === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
            <option value="preco_asc" <?= $sel('ordenar') === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
            <option value="preco_desc" <?= $sel('ordenar') === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
        </select>
    </fieldset>

    <button type="submit" class="btn">Filtrar</button>
    <a class="btn btn-outline" href="<?= e(portal_route('properties.index', $company->slug)) ?>">Limpar filtros</a>
</form>
