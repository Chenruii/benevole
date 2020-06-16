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
    <div class="text-center"> Pas de XXXXX données</div>
<?php else  : ?>
    <section class="">
        <h2 class="">
            Secteur
        </h2>
        <div class="">
            <div class="">
                <?php foreach ($items as $index => $item)
                    include __DIR__ . DIRECTORY_SEPARATOR . "list-item.php";
                ?>
            </div>
        </div>
    </section>
<?php endif;