<?php



$image = $list->getFieldDisplay($index, 'thumb');
if(empty($image)){
    $image = "{{tpl:MIB_THEME}}/img/home-post.jpg";
}

$henClass = 0 === $categIndex && 0 === $index ? 'Hen' : '';

?>

<article class="Post Post--home Box flex force-flex-row <?= $henClass ?>">
    <img class="Post-img" src="<?=$image?>" alt="<?= $list->getFieldDisplay($index, 'title') ?>">
    <div class="Post-content">
        <h2 class="Post-title"><?= $list->getFieldDisplay($index, 'title',['truncate'=>70]) ?> </h2>
        <div class="Post-date"> <?= $list->getFieldDisplay($index, 'date') ?></div>
        <div class="Post-summary">
            <?= $list->getFieldDisplay($index, 'summary',['truncate'=>170]) ?>
        </div>
        <a  class="Post-link button" href="<?= $list->getFieldDisplay($index, 'slug') ?>">
            <span aria-hidden="true">></span>
            En savoir
            <span aria-hidden="true">+</span>
        </a>
    </div>
</article>