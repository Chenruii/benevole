<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

define("MIB_ACCOUNT_PATH", MIB_PATH_VAR."plugins".DIRECTORY_SEPARATOR.'account'.DIRECTORY_SEPARATOR);
define("MIB_ACCOUNT_HTMLPARTS", MIB_ACCOUNT_PATH."html-parts".DIRECTORY_SEPARATOR);


if(!class_exists('AccountPlugin'))
    require_once 'AccountPluginClass.php';





