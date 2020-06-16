<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

$list = MibboFormManager::getList($MIB_PLUGIN['name'],__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR);

if (empty($list)) {
    error("Erreur dans le chargement de la liste ");
    exit("Erreur dans le chargement de la liste");
}




$baseLink = $MIB_PAGE['rub'] ;
$items = $list->loadData();
?>

<?php if (empty($items)) : ?>
    <div class="text-center"> Pas de d'Annonce</div>
<?php else  : ?>
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
                    <div class="">
                        <?php foreach ($items as $index => $item)
                            include __DIR__ . DIRECTORY_SEPARATOR . "list-item.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <hr />
    </section>
<?php endif;