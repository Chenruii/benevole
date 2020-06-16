<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

$list = MibboFormManager::getList('event');

$state = ['sort_by'=>'order', 'sort_dir'=>'desc'];
$events = $list->loadData($state);

$lang = strtoupper($MIB_PAGE['lang']);
//var_dump($MIB_PAGE);
$baseLink = $MIB_PAGE['rub'];

?>

<section class="SectionE white-background">

    <div class="simple-slider">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </div>
    <br>
    <div class="Container Container--medium">
            <div class=" grid grid-2-columns">
                <?php foreach ($events as  $index => $event) :
                $url = $baseLink . "/" . strtolower(mib_strtourl($event['title']));?>
                <div class="row">
                    <a href="<?= $url ?>"><p style="text-align: left;font-weight: bold; "><?php echo $list->getFieldDisplay($index,'title') ?></p></a>
                    <br/>
                    <p><?php echo $list->getFieldDisplay($index,'eventDate') ?></p>
                    <br/>
                    <div class="col-6">
                        <?php if(!empty($list->getFieldDisplay($index,'imagelist'))): ?>
                            <img style="width: 480px; height: 320px" src="<?= $list->getFieldDisplay($index,'imagelist') ?>" alt="" />
                        <?php else: ?>
                            <img src="https://placehold.it/300x300" alt="" />
                        <?php endif; ?>
                    </div>
                    <?php if (0 === $index or 3 === $index) : ?>
                    <div class="col-6">
                        <?php endif; ?>
                        <article >
                            <?= $event['summary' . $lang] ?>
                            <?= $event['description' . $lang] ?>
                            <!--                            <a href="--><?//= $url ?><!--" class="Button text-center">--><?//= __('Lire Plus') ?><!--</a>-->
                        </article>
                        <?php if (2 === $index or 4 === $index) : ?>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        <?php endforeach ?>
</section>


