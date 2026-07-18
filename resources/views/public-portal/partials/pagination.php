<?php

use Illuminate\Pagination\LengthAwarePaginator;

/** @var LengthAwarePaginator $properties expected in scope */
?>
<?php if ($properties->lastPage() > 1) { ?>
<nav class="pagination" aria-label="Paginação">
    <?php if ($properties->onFirstPage()) { ?>
    <span class="btn btn-outline" aria-disabled="true">&laquo; Anterior</span>
    <?php } else { ?>
    <a class="btn btn-outline" href="<?= e($properties->previousPageUrl()) ?>">&laquo; Anterior</a>
    <?php } ?>

    <?php for ($page = 1; $page <= $properties->lastPage(); $page++) { ?>
    <?php if ($page === $properties->currentPage()) { ?>
    <span class="btn"><?= $page ?></span>
    <?php } else { ?>
    <a class="btn btn-outline" href="<?= e($properties->url($page)) ?>"><?= $page ?></a>
    <?php } ?>
    <?php } ?>

    <?php if ($properties->hasMorePages()) { ?>
    <a class="btn btn-outline" href="<?= e($properties->nextPageUrl()) ?>">Próxima &raquo;</a>
    <?php } else { ?>
    <span class="btn btn-outline" aria-disabled="true">Próxima &raquo;</span>
    <?php } ?>
</nav>
<?php } ?>
