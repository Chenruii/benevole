<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


/** @var int $index */
/** @var MibboList $list */
/** @var string $baseLink */

// ici on prépare les données pour ne pas a avoir a éxécuter de code dans le templating
$id = $list->datas[$index]['_id'];
$photo = $list->getFieldRaw($index,"photo");
$photoPath = empty($photo['path']) ? null: $photo['path'];
$photoLegend = empty($photo['legend']) ? null: $photo['legend'];
$poste = $list->getFieldDisplay($index,'poste');
$email = $list->getFieldDisplay($index,'email');

?>
<hr>
<article class="">
    <figure>
        <?php if (!empty($photoPath)): ?>
            <img src="<?= $photoPath ?>" alt=""/>
            <figcaption ><small><?= $photoLegend ?> </small></figcaption>
        <?php else: ?>
            <img src="https://placehold.it/220x250" alt=""/>
        <?php endif; ?>
        <a href="<?=$baseLink."/".$id ?>"> <?= __('Voir le détail')  ?>  </a>
        <figcaption class="">

            <?php if (!empty($poste)): ?>
            <strong><?= $poste ?></strong><br>
            <?php endif; ?>

            <?= $list->getFieldDisplay($index,'phone'); ?><br>

            <a href="mailto:<?= $email ?>"><?=$email ?></a>
        </figcaption>
    </figure>
    <h3 class=""><?= $list->getFieldDisplay($index,'firstname') . ' ' . $list->getFieldDisplay($index,'lastname') ?></h3>
</article>