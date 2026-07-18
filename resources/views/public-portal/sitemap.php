<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url) { ?>
<url>
<loc><?= e($url['loc']) ?></loc>
<?php if ($url['lastmod']) { ?>
<lastmod><?= e($url['lastmod']->toAtomString()) ?></lastmod>
<?php } ?>
</url>
<?php } ?>
</urlset>
