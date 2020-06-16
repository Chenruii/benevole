<?php

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


pageHead(__('Evenement') ,'','');

echo  MibboFormManager::getCssAndJsLInks();
if(!class_exists('EventPlugin'))
    require_once 'EventPluginClass.php';
//
//$event = EventPlugin::getInstance();


if(empty($MIB_PAGE['info'])){
    include 'event-list.php';
} else {

    include 'event-detail.php';
}
