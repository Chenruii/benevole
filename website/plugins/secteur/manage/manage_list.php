<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/06/2018
 * Time: 21:21
 */


// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

/*--------------------------------------------*/
/*  chargement du formulaire                  */
/*--------------------------------------------*/
$list = MibboFormManager::getList($MIB_PLUGIN['name'], __DIR__ . DIRECTORY_SEPARATOR . "..");
if (empty($list)) {
    error("Erreur dans le chargement de la liste ");
    exit();
}
// on ajoute les sources de données dynamiques définies dans  xxxx_manage.php
$list->sources = empty($sources) ? [] : $sources;

$list->editUrl = $MIB_PLUGIN['name'] . '/edit';
$list->deleteUrl = $MIB_PLUGIN['name'] . '/delete';
$list->listUrl = $MIB_PLUGIN['name'];

$state = $list->getState();
$datas = $list->loadData($state);


/*--------------------------------------------*/
/*  Rendu du formulaire                       */
/*--------------------------------------------*/
?>

    <h1><?= $MIB_PLUGIN['name'] ?> </h1>
    <br>
    <hr>
    <a href="<?php echo $MIB_PLUGIN['name'] ?>/create" class="Link">
        <svg viewBox="0 0 24 24" width="20" height="20">
            <g>
                <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      d="M12 7.5v9"></path>
                <path class="a" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      d="M7.5 12h9"></path>
                <circle class="a" cx="12" cy="12" r="11.25" fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round"></circle>
            </g>
        </svg>
        Ajouter
    </a> <br>
<?php echo $list->renderTable($datas, $state);






