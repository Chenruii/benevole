<?php

$blogSlider = BlogPlugin::getInstance();
/* ---------------------------------- Slider -------------------------------------*/

$state['take'] = 6;
$state['sort_by'] = 'date';
$state['sort_dir'] = 'desc';
$list = $blogSlider->getList();
$filters = [['field' => 'visibleSlider', 'value' => 'true']];
$blog->modifyFilters($filters);
$datas = $list->loadDataByFilter($state, $filters);
$list->datas = $datas;
$images = [] ;
?>

<div class="HomeSlider">
<?php foreach ($datas as $index => $data) :

    $imageSlider = $list->getFieldDisplay($index, 'imageSlider');
    if (empty($imageSlider)) {
        $imageSlider = "../theme/website/img/slider-default.jpg";
    }
    $imageSliderMobile = $list->getFieldDisplay($index, 'imageSliderMobile');
    if (empty($imageSliderMobile)) {
        $imageSliderMobile = "../theme/website/img/slider-default-mobile.jpg";
    }
    $images[$index]= ['normal'=>$imageSlider,'small'=>$imageSliderMobile];
    ?>


    <div class="HomeSlider-item flex-column align-items-center justify-center" id="HomeSlider-<?= $index?>">
        <h2 class="HomeSlider-itemTitle"><?= $list->getFieldDisplay($index, 'title') ?> </h2>
        <a href="<?= $list->getFieldDisplay($index, 'slug') ?>" class="button">
            <span aria-hidden="true">></span>
            En savoir
            <span aria-hidden="true">+</span>
        </a>
    </div>
<?php endforeach; ?>
</div>
<?php foreach ($images as $index => $imgs)  :?>
    <style type="text/css">
        #HomeSlider-<?= $index?>{
            background-image: url('<?= $imgs['normal'] ?>');
        }
        @media screen and (max-width: 599px) {
            #HomeSlider-<?= $index?>{
                background-image: url('<?= $imgs['small'] ?>');
            }
        }

    </style>
<?php endforeach; ?>


<script src="js/flickity.pkgd.min.2.2.js"></script>
<script type="text/javascript">
(function () {
    var elem = document.querySelector('.HomeSlider')
    var flkty = new Flickity(elem, {
        cellAlign: 'left',
        contain: true,
        autoPlay: 5000
    })
})()
</script>