<?php
/**
 * Expects $shareUrl (absolute canonical URL) and $shareTitle (plain text)
 * set by the including view.
 */
$shareText = rawurlencode($shareTitle.' — '.$shareUrl);
$shareUrlEncoded = rawurlencode($shareUrl);
$shareTitleEncoded = rawurlencode($shareTitle);
?>
<div class="share-buttons">
    <span class="share-label">Compartilhar:</span>
    <a class="share-btn share-whatsapp" href="https://wa.me/?text=<?= $shareText ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
    <a class="share-btn share-facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEncoded ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
    <a class="share-btn share-telegram" href="https://t.me/share/url?url=<?= $shareUrlEncoded ?>&text=<?= $shareTitleEncoded ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
    <a class="share-btn share-x" href="https://twitter.com/intent/tweet?url=<?= $shareUrlEncoded ?>&text=<?= $shareTitleEncoded ?>" target="_blank" rel="noopener noreferrer">X</a>
    <button type="button" class="share-btn share-copy" data-copy-url="<?= e($shareUrl) ?>">Copiar link</button>
</div>
