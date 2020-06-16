<?php

$form = MibboFormManager::getForm('event');
if (empty($form)) {
    error("Erreur dans le chargement du formulaire");
    exit();
}

// on charge les données
$id = explode("/", $MIB_PAGE['info'])[0];
$form->loadData($id);
$event = $form->loadData($id);
$lang = strtoupper($MIB_PAGE['lang']);
$baseLink = $MIB_PAGE['rub'];

$description = $form->getFieldDisplayLang('description');

$content1 = $form->getFieldDisplayLang('summary');
$isVisible1 = !empty($content1);
$title1 = $form->getFieldDisplay('title');

$content2 = $form->getFieldDisplayLang('description');
$isVisible2 = !empty($content2);

?>
<!--<div class="simple-slider">-->
<!--    <div class="swiper-container">-->
<!--        <div class="swiper-wrapper">-->
<!--            <div class="swiper-slide">-->
<!--            </div>-->
<!--            <div class="swiper-pagination"></div>-->
<!--            <div class="swiper-button-prev"></div>-->
<!--            <div class="swiper-button-next"></div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
</div>
    <div class="Container Container--medium">
        {{widget:website site_events_slide}}
        <div class=" grid grid-1-columns">

            <h3 class=" "></h3>
            <?= __('Titre') ?>
            <div class="text-bold text-uppercase">
                <?php if ($isVisible1): ?>
                    <?php if (!empty($title1)): ?>
                        <h2><?= $title1 ?></h2>
                    <?php endif; ?>

            </div>
            <div class="row">
                <div class="text-bold text-uppercase">
                    <?= __('Résumé') ?>
                    <div><?= $content1 ?></div>
                    <?php endif ?>
                </div>

                <?= __('Descriptions') ?>
                <div class="text-bold text-uppercase">
                    <?php if ($isVisible2): ?>
                        <?php if (!empty($title2)): ?>
                            <h2><?= $title2 ?></h2>
                        <?php endif ?>
                        <div><?= $content2 ?></div>
                    <?php endif ?>

                    <?= $form->getFieldDisplayLang('description') ?>
                </div>
            </div>
        </div>
</section>


