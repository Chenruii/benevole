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
$description = $form->getFieldDisplay('description');
$nameOrganizer = $form->getFieldDisplay('nameOrganize');
$email = $form->getFieldDisplay('email');
$baseLink = $MIB_PAGE['rub'] ;

?>
<section id="portfolio" class="portfolio">
    <div class="container">
        <div class="row">
            <a href="<?= $baseLink ?>">Retour à la liste </a>
        </div>

        <div class="main_portfolio_content center wow fadeInUp">
            <div id="mixcontent" class="mixcontent  wow zoomIn">
                <div class="col-md-4 mix cat4 cat2 cat1 cat3">
                    <h3 class=""><?= $form->getFieldDisplay('title') ?></h3>
                    <div class="single_portfolio_img">
                        <?php if (!empty($photoPath)): ?>
                            <img src="<?= $photoPath ?>" alt=""/>
                            <figcaption ><small><?= $photoLegend ?> </small></figcaption>
                        <?php else: ?>
                            <img src="https://placehold.it/220x250" alt=""/>
                        <?php endif; ?>
                        <br><br>
                        <?=$form->getFieldDisplay('theme'); ?><br>
                        <?=$form->getFieldDisplay('dateEvent'); ?><br>
                    </div>
                    <div class="single_mixi_portfolio center">
                        <div class="single_portfolio_text">
                            <?php if (!empty($nameOrganizer)): ?>
                                <strong><?= $nameOrganizer ?></strong><br>
                            <?php endif; ?>
                        </div>
                        <a href="mailto:<?= $email ?>"><?=$email ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr />
</section>
