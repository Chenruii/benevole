<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/06/2018
 * Time: 21:21
 */


//---------------------------------------------------------------------------------
// Page chargée quand on est sur le BO

if (!defined('MIB') || !defined('MIB_MANAGE'))
    exit;

if ( defined('MIB_AJAX') )
    define('MIB_AJAXED', 1);

//---------------------------------------------------------------------------------
// ON récupères les informations depuis l'URL
if (isset($MIB_PAGE['info'])) {
    $MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info']);
    $MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "remove", "create", "edit", "default"
    $MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id"
}

//---------------------------------------------------------------------------------
// ici éventuellement le require d'un fichier de fonction sépcifique au besoin
// require_once 'xxxxx_function.php';


//---------------------------------------------------------------------------------
// définit éventuellement les sources pour les
// select des formulaire
$sources = [];

//$sources['category'] = [
//    ['id'=>'1','label'=>'category1'],
//    ['id'=>'2','label'=>'category2'],
//    ['id'=>'3','label'=>'category3'],
//    ['id'=>'4','label'=>'category4']
//];
$sources['publishState'] = [
    ['id'=>'1','label'=>'Brouillon'],
    ['id'=>'2','label'=>'Prêt à publier'],
    ['id'=>'3','label'=>'Publié']
];

$sources['theme'] = [
    ['id'=>'1','label'=>'Gastronomie / Cuisine'],
    ['id'=>'2','label'=>'Vie quotidien / Astuce'],
    ['id'=>'3','label'=>'Loisir / Création'],
    ['id'=>'4','label'=>'Culture / Sport'],
    ['id'=>'5','label'=>'Offre & Service'],
    ['id'=>'6','label'=>'Baby Sitting']
];

//---------------------------------------------------------------------------------
// Chargement des pages concernées
 if($MIB_PLUGIN['action']==='edit' || $MIB_PLUGIN['action']==='create' || $MIB_PLUGIN['action']==='delete') {
     require(__DIR__.DIRECTORY_SEPARATOR.'manage/manage_detail.php');
 }  else {
     require(__DIR__.DIRECTORY_SEPARATOR.'manage/manage_list.php');
 }
