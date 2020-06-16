


<h1> <?= $form->getFieldDisplay('pageTitle') ?> </h1>

<p><?= $form->getFieldDisplay('pageTitle') ?></p>

<p><?= $form->getFieldDisplay('pageSummary') ?></p>


<?php
$content1 = $form->getFieldDisplay('PageParagraph1Content');
$isVisible1 = !empty($content1);
$title1 = $form->getFieldDisplay('PageParagraph1Title');

$content2 = $form->getFieldDisplay('PageParagraph2Content');
$isVisible2 = !empty($content2);
$title2 = $form->getFieldDisplay('PageParagraph2Title');

if($isVisible1)  : ?>
    <section class="">
        <?php if(!empty($title1))  : ?>
        <h2 class=""><?= $title1 ?></h2>
        <?php endif; ?>
        <div class="">
            <?= $content1 ?>
        </div>
    </section>
<?php endif ; ?>

<?php if($isVisible2)  : ?>
    <section class="">
        <?php if(!empty($title2))  : ?>
        <h2 class=""><?= $title2 ?></h2>
        <?php endif; ?>
        <div class="">
            <?= $content2 ?>
        </div>
    </section>
<?php endif ; ?>


