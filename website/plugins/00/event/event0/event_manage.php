<?php

if (!defined('MIB') || !defined('MIB_MANAGE'))
    exit;

if ( defined('MIB_AJAX') )
    define('MIB_AJAXED', 1);


if (isset($MIB_PAGE['info'])) {
    $MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info']);
    $MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "remove", "create", "edit", "default"
    $MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id du groupe"
}

// chargement des listes de catégorie  source des Select ( ex : pays , fonctions , ... )
$sources = [];


if($MIB_PLUGIN['action']==='edit' || $MIB_PLUGIN['action']==='create' || $MIB_PLUGIN['action']==='delete') {
    require('manage_detail.php');
}  else {
    require('manage_list.php');
}