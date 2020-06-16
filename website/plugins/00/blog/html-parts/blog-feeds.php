<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
$path = MIB_ROOT . DIRECTORY_SEPARATOR . "feeds" . DIRECTORY_SEPARATOR . 'instagram.json';
$feeds = $blog->getFeeds('instagram',19);

if (empty($feeds)) {
    return;
}

?>

<div class="Feeds">
<?php
foreach ($feeds as $feed):
    if(empty( $feed['img']))
        continue ;
    ?>
    <a href="<?= $feed['link'] ?>" target="_blank" title="<?= $feed['title'] ?>" class="Feed Feed--<?=$feed['type'] ?>">
        <img src="<?= $feed['img'] ?>" alt="" width="270" height="270" />
        <?= $feed['img'] ?>
    </a>
<?php endforeach; ?>
</div>

<script src="js/flickity.pkgd.min.2.2.js"></script>
<script type="text/javascript">
    (function () {
        var elem = document.querySelector('.Feeds')
        new Flickity(elem, {
            cellAlign: 'left',
            contain: true,
            autoPlay: 2000,
            wrapAround: true
        })
    })()
</script>