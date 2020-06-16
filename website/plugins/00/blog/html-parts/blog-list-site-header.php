<?php


// on va chercher l'image d'entete sur la page du site en question .
$pageKeySite = MibboFormManager::processId($MIB_PAGE['lang'].'_'.$blog->site['href']);
$formSite = MibboFormManager::getPageForm('page-vie-des-sites',$pageKeySite,$MIB_PAGE['lang'] );
$imageHeader = $formSite->getFieldDisplay('imageheader');


$imageHeader = empty($imageHeader)? '{{tpl:MIB_THEME}}/img/categories/category-recherche-header.jpg':$imageHeader;

?>


<div class="CategoryBanner text-center" style="background-image: url(<?=$imageHeader?>);">
    <h1 class="CategoryBanner-title"> <?= $blog->site['label'] ?> </h1>
    <div class="CategoryBanner-logo">
        <img src="{{tpl:MIB_THEME}}/img/categories/category-recherche-white.png" alt="Recherche">
    </div>
</div>


<div class="DataBar text-uppercase">
    <div class="Container">
        <h2> <?= $blog->site['label'] ?></h2>
</div>
</div>
