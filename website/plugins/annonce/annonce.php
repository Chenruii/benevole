<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

//---------------------------------------------------------------------------------
// ici éventuellement le require d'un fichier de fonction sépcifique au besoin
// require_once 'xxxxx_function.php';

//---------------------------------------------------------------------------------
// définit éventuellement les sources dynamiques  pour les select des formulaires
$sources = [];
//$sources['category'] = [
//    ['id'=>'1','label'=>'category1'],
//    ['id'=>'2','label'=>'category2'],
//    ['id'=>'3','label'=>'category3'],
//    ['id'=>'4','label'=>'category4']
//];

//---------------------------------------------------------------------------------
// Chargement des pages concernées
if(empty($MIB_PAGE['info'])){
    include  __DIR__ . DIRECTORY_SEPARATOR."front".DIRECTORY_SEPARATOR."list.php" ;
}else {
    include  __DIR__ . DIRECTORY_SEPARATOR."front".DIRECTORY_SEPARATOR."detail.php" ;
}





