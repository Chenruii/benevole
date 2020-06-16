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
$description = $list->getFieldDisplay($index,'description');

?>
<hr>
<section id="portfolio" class="portfolio">
    <div class="container">
        <div class="row">
            <div class="main_portfolio_area m-y-3">
                <div class="head_title center wow fadeInUp">
                    <h2>Nos Annonces</h2>
                    <p style="text-align: left">Lorsqu’on a besoin de faire quelque chose (travaux, cuisine, etc.), on rencontre souvent plusieurs problématiques :
                        <br>Manque de ressources précises et besoin de connaissances du sujet
                        <br>Manque de technique ou de savoir-faire
                        <br>Manque d’interaction et donc de ressource humaine lors de ces di-travaux
                    </p>
                </div>

                <div class="main_portfolio_content center wow fadeInUp">
                    <div id="mixcontent" class="mixcontent  wow zoomIn">
                        <div class="col-md-4 mix cat4 cat2 cat1 cat3">
                            <div class="single_portfolio_img">
                                <?php if (!empty($photoPath)): ?>
                                <img src="<?= $photoPath ?>" alt=""/>
                                    <figcaption ><small><?= $photoLegend ?> </small></figcaption>
                                <?php else: ?>
                                    <img src="https://placehold.it/220x250" alt=""/>
                                <?php endif; ?>
                                <br><br>
                                <a href="<?=$baseLink."/".$id ?>"> <?= __('Voir le détail')  ?>  </a>
                            </div>
                            <div class="single_mixi_portfolio center">
                                    <div class="single_portfolio_text">
                                    <h3><?= $list->getFieldDisplay($index,'title') . ' ' . $list->getFieldDisplay($index,'dateEvent') ?></h3>
                                        <?php if (!empty($description)): ?>
                                            <p><?= $description ?></p><br>
                                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- End of col-md-4 -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr />
</section>