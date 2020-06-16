<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;



echo  MibboFormManager::getCssAndJsLInks();
if(!class_exists('BlogPlugin'))
    require_once 'BlogPluginClass.php';

$blog = BlogPlugin::getInstance();
//MibboFormManager::changePageTemplate('fr_article1','blog','blog');




require_once 'manage_detail.php';
