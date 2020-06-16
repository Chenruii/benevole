<?php



$image = $list->getFieldDisplay($index, 'thumb');
if (empty($image)) {
    $image = "{{tpl:MIB_THEME}}/img/home-post.jpg";
}
$tags = $list->getFieldDisplay($index, 'tag');
if (!empty($tags)) {
    $tags = explode(';', $tags);
    $tags = array_filter($tags, function($el){
        if(!empty($el)){
            return $el ;
        }
    });
}
$tags = !empty($tags) ? ('#' . join(' | #', $tags) ) : '';
$site = $list->getFieldDisplay($index, 'site');
$site = !empty($site) ? ($site . ' | ') : '';
?>
<article class="Post flex force-flex-row">
    <a href="<?= $list->getFieldDisplay($index, 'slug') ?>" >
        <img class="Post-img" src="<?= $image ?>" alt="<?= $list->getFieldDisplay($index, 'title') ?>">
    </a>
    <div class="Post-content">
        <h2 class="Post-title">
            <a  class="text-no-underline" href="<?= $list->getFieldDisplay($index, 'slug') ?>"><?= $list->getFieldDisplay($index, 'title') ?> </a></h2>
        <div class="Post-date">  <?= $site ?>

            <?= $list->getFieldDisplay($index, 'date') ?>
            <span class="Post-categ"> | <?= $list->getFieldDisplay($index, 'category') ?></span>
        </div>
        <div class="Post-date">  <?= $tags ?></div>
        <p><?= $list->getFieldDisplay($index, 'summary') ?></p>
        <a href="<?= $list->getFieldDisplay($index, 'slug') ?>" class="button">
            <span aria-hidden="true">></span>
            En savoir
            <span aria-hidden="true">+</span>
        </a>
    </div>
</article>