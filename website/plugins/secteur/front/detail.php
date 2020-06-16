<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$form = MibboFormManager::getForm($MIB_PLUGIN['name'],__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR);
if (empty($form)) {
    error("Erreur dans le chargement du formulaire");
    exit("Erreur dans le chargement du formulaire");
}



$id = explode("/", $MIB_PAGE['info'])[0];
$form->loadData($id);


// ici on prépare les données pour ne pas a avoir a éxécuter de code dans le templating
$photo = (!empty($form->datas['photo']['path'])) ? $form->datas['photo']['path'] : null ;
$poste = $form->getFieldDisplay('poste');
$email = $form->getFieldDisplay('email');
$baseLink = $MIB_PAGE['rub'] ;

?>
<a href="<?= $baseLink ?>">Retour à la liste </a>
<br><br>

<article class="">
    <figure>
        <?php if (!empty($photo)): ?>
            <img src="<?= $photo ?>" alt=""/>
        <?php else: ?>
            <img src="https://placehold.it/220x250" alt=""/>
        <?php endif; ?>
        <figcaption class="">

            <?php if (!empty($poste)): ?>
                <strong><?= $poste ?></strong><br>
            <?php endif; ?>

            <?=$form->getFieldDisplay('phone'); ?><br>

            <a href="mailto:<?= $email ?>"><?=$email ?></a>
        </figcaption>
    </figure>
    <h3 class=""><?= $form->getFieldDisplay('firstname') . ' ' . $form->getFieldDisplay('lastname') ?></h3>
</article>