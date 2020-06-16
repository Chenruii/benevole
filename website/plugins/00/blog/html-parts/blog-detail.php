<?php


// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

$site = $form->getFieldDisplay('site');
$content1 = $form->getFieldDisplay('contentParagraph1');
$isVisible1 = !empty($content1);
$title1 = $form->getFieldDisplay('titleParagraph1');

$content2 = $form->getFieldDisplay('contentParagraph2');
$isVisible2 = !empty($content2);
$title2 = $form->getFieldDisplay('titleParagraph2');
$video = $form->getFieldDisplay('videoLink');

$image = $form->getFieldDisplay('image');
$imageInfo =$form->getFieldRawDatas('image');
$imageTitle = !empty($imageInfo['title']) ? $imageInfo['title'] : '';

$imagetop = $form->getFieldDisplay('imagetop');
$imagetopInfo =$form->getFieldRawDatas('imagetop');
$imagetopTitle = !empty($imagetopInfo['title']) ? $imagetopInfo['title'] : '';

$imagemiddle = $form->getFieldDisplay('imagemiddle');
$imagemiddleINfo =$form->getFieldRawDatas('imagemiddle');
$imagemiddleTitle = !empty($imagemiddleINfo['title']) ? $imagemiddleINfo['title'] : '';

$sliderInfos = $form->getFieldRawDatas('slider');
$tags = $form->getFieldDisplay( 'tag');
if (!empty($tags)) {
    $tags = explode(';', $tags);
    $tags = array_filter($tags, function($el){
        if(!empty($el)){
            return $el ;
        }
    });
}
$tags = !empty($tags) ? ('#' . join(' | #', $tags) ) : '';


?>
<div class="CategoryBanner  CategoryBanner-<?= $blog->category['id'] ?> text-center" style="background-image: url('{{tpl:MIB_THEME}}/img/categories/category-<?= $blog->category['id'] ?>-header.jpg');">
    <h1 class="CategoryBanner-title"> <?=  $blog->category['label'] ?></h1>
    <div class="CategoryBanner-logo">
        <img src="{{tpl:MIB_THEME}}/img/categories/category-<?= $blog->category['id'] ?>-white.png" alt="<?=  $blog->category['label'] ?>">
    </div>
</div>

<div class="Container flex justify-space-between">
    <div class="PostList PostDetail Box flex-grow">
        <h2 class="Post-title"><?= $form->getFieldDisplay('title') ?></h2>
        <div class="Post-date">
            <?php if (!empty($site)) : ?>
                <?= $form->getFieldDisplay('site') ?> |
            <?php endif; ?>
            <?= $form->getFieldDisplay('category') ?> |
            <?= $form->getFieldDisplay('date') ?>
        </div>
        <div class="Post-date  mb4">  <?= $tags ?></div>
        <?php if (!empty($imagetop)):
            ?>
            <div class="Post-image">
                <figure>
                    <img src="<?= $imagetop ?>" title="<?= $imagetopTitle ?>"/>
                    <figcaption><?= $imagetopTitle ?></figcaption>
                </figure>
            </div>
        <?php endif; ?>

        <?php if ($isVisible1): ?>
            <?php if (!empty($title1)): ?>
                <h2><?= $title1 ?></h2>
            <?php endif; ?>
            <div><?= $content1 ?></div>
        <?php endif ?>
        <?php if (!empty($imagemiddle)): ?>
            <div class="Post-image">
                <figure>
                <img src="<?= $imagemiddle ?>" title="<?= $imagemiddleTitle ?>"/>
                    <figcaption><?= $imagemiddleTitle ?></figcaption>
                </figure>
            </div>
        <?php endif; ?>
        <?php if ($isVisible2): ?>
            <?php if (!empty($title2)): ?>
                <h2><?= $title2 ?></h2>
            <?php endif ?>
            <div><?= $content2 ?></div>
        <?php endif ?>

        <?php
        $isInterview =  isset($form->datas['interviewArticle']) && $form->datas['interviewArticle']==='true' ;
        if($isInterview)
            require_once 'blog-interview.php';
        ?>

        <?php if (!empty($video)): ?>
            <?php if (strpos($video, 'vimeo.com/')!==false): ?>
                <?php $video = str_replace('/vimeo.com/','/player.vimeo.com/video/',$video) ?>
                <iframe class="Post-video" src="<?= $video ?>" frameborder="0" controls="0"allow="" allowfullscreen="1" mozallowfullscreen="1" webkitallowfullscreen="1"></iframe>
            <?php elseif (strpos($video, 'youtube.com/watch?v=')!==false): ?>
                <?php $video = str_replace('youtube.com/watch?v=','youtube.com/embed/',$video) ?>
                <iframe class="Post-video" src="<?= $video ?>?controls=0&modestbranding=1&rel=0" frameborder="0" controls="0" allow=""></iframe>
            <?php else : ?>
                <iframe class="Post-video" src="<?= $video ?>?controls=0&modestbranding=1&rel=0" frameborder="0" controls="0" allow=""></iframe>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($image)): ?>
            <div class="Post-image">
                <figure>
                    <img src="<?= $image ?>" title="<?= $imageTitle ?>"/>
                    <figcaption><?= $imageTitle ?></figcaption>
                </figure>

            </div>
        <?php endif; ?>

        <?php if (!empty($sliderInfos)):  ?>
            <div class="Post-slider">
                <?php foreach ($sliderInfos as $sliderInfo) : ?>
                    <div class="Post-slider-item "
                         style="background-image: url(<?= $sliderInfo['path'] ?>);">
                        <?php if (!empty($sliderInfo['title']) ) : ?>
                            <div class="Post-slider-title"> <?=$sliderInfo['title'] ?> </div>
                        <?php endif ?>
                        <?php if (!empty($sliderInfo['legend'])  ) : ?>
                            <div class="Post-slider-title " style="font-style: italic"> <?=$sliderInfo['legend'] ?></div>
                        <?php endif ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <script src="js/flickity.pkgd.min.2.2.js"></script>
            <script>
                (function () {
                    var elem = document.querySelector('.Post-slider')
                    var flick = new Flickity(elem, {
                        percentPosition: 100,
                        autoPlay: 5000
                    })
                    setTimeout(function(){flick.resize();},50);
                })()
            </script>
        <?php endif; ?>

    </div>

    <?php

    $blogHighlight = BlogPlugin::getInstance();
    $st = ['take' => 4];
    $filters = [['field' => 'visibleHighLight', 'value' => 'true']];
    $blog->modifyFilters($filters);
    $blogHighlight->GetListByFilter($filters, $st);
    $list = $blogHighlight->getList();
    ?>

    <aside class="Post-sidebar">
        <div class="Box">
            <h3 class="Post-title text-uppercase">Articles à la une</h3>
                <?php foreach ($list->datas as $index => $data):
                    $image = $list->getFieldDisplay($index, 'thumb');
                    if (empty($image)) {
                        $image = "{{tpl:MIB_THEME}}/img/home-post.jpg";
                    }
                    ?>
                    <article class="PostHighLight ">
                        <img class="PostHighLight-img" src="<?= $image ?>"
                             alt="<?= $list->getFieldDisplay($index, 'title') ?>">

                        <span class="PostHighLight-title">
                                <a href="<?= $list->getFieldDisplay($index, 'slug') ?>">
                                <?= $list->getFieldDisplay($index, 'title') ?>
                                </a>
                            </span>
                    </article>

                <?php endforeach; ?>

        </div>

        <?php

        $feeds = $blog->getFeeds('instagram',1);
        if (!empty($feeds)):
            foreach ($feeds as $feed): ?>
            <a href="<?= $feed['link'] ?>" target="_blank" title="<?= $feed['title'] ?>" class="Feed Feed--<?=$feed['type'] ?>">
                <img src="<?= $feed['img'] ?>" alt="" width="270" height="270" />
            </a>
            <?php endforeach; ?>
       <?php endif; ?>
<!--        <a href="#">-->
<!--            <img src="{{tpl:MIB_THEME}}/img/tile-facebook.png" alt="" width="270" height="270">-->
<!--        </a>-->
<!--        <a href="#">-->
<!--            <img src="{{tpl:MIB_THEME}}/img/tile-linkedin.png" alt="" width="270" height="270">-->
<!--        </a>-->
    </aside>

</div>
<script type="text/javascript">

// gestion des callout sur les liste
$('ul>li>.Post-callout').each(function(){
    $(this).parent().parent().addClass('Post-callout');
})


</script>




