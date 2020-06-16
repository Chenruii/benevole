<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net

	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL

	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/
defined('MIB_ROOT') or exit;

// version minimum de PHP
define('PHP_VERSION_MIN', '7.1');

// version du système et révison de la base de donnée
define('MIB_SYS_NAME', 'Mibbo');
define('MIB_SYS_WEBSITE_URL', 'http://mibbo.net');
define('MIB_SYS_WEBSITE_URL_API', MIB_SYS_WEBSITE_URL.'/api/1'); // toutes les requettes vers l'api doivent être au format .json
define('MIB_SYS_VERSION', '1.7.0');



// url des différents répertoires du système
if ( !defined('MIB_URL_SYS') ) define('MIB_URL_SYS', 'system');
if ( !defined('MIB_URL_VAR') ) define('MIB_URL_VAR', 'website');

// path des différents répertoires du système
if ( !defined('MIB_PATH_SYS') ) define('MIB_PATH_SYS', MIB_ROOT.'system/');
if ( !defined('MIB_PATH_VAR') ) define('MIB_PATH_VAR', MIB_ROOT.'website/');


require_once MIB_PATH_SYS."libs/form/MibboFormManager.php";
require_once 'MibboEngine.php';

$mibboEngine = new MibboEngine();

// --- netoyage POST/ Initialisation et correction de requête
$mibboEngine->initCore();

// chargement des fichiers necessaires au démmarage de Mibbo
require_once MIB_PATH_SYS.'boot/locale.php';		// locales du système
require_once MIB_PATH_SYS.'boot/date.php';		// fonctions de date
require_once MIB_PATH_SYS.'boot/errors.php';		// gestionnaire d'erreurs du système
require_once MIB_PATH_SYS.'boot/utf8.php';		// prise en charge de l'encodage UTF-8
require_once MIB_PATH_SYS.'boot/cache.php';		// fonctions de cache

$mibboEngine->initConstant();

// Installation ?
if ( defined('MIB_INSTALL') ) {
    $mibboEngine->install();
}

// toutes les configurations essentielles on bien été chargées
define('MIB', 1);

// On créer  les variables qui vont être alimentés par les le MibboEngine
$MIB_PAGE = array();
$MIB_CONFIG = array();
$MIB_USER = array();
$MIB_DB = null;
$MIB_PLUGIN = null;
$MIB_WIDGETS = array();

global  $MIB_PAGE, $MIB_CONFIG,$MIB_DB,$MIB_PLUGIN,$MIB_USER;


$mibboEngine->registerPlugins();

$mibboEngine->initPage();

$mibboEngine->initDb();

$mibboEngine->initCache();

session_start(); // Active la prise en charge des sessions
session_regenerate_id(); // Secure la session en renouvellant l'id

$mibboEngine->preProcessSeoRequest(); // sitemap et robots.txt

$mibboEngine->preProcessRequest();

$mibboEngine->processRewrite();

$mibboEngine->processAssets();

// Chargement requette AJAX ou JSON
if ( defined('MIB_AJAX') )
	$mibboEngine->processAjax();
else if ( defined('MIB_JSON') )
    $mibboEngine->processJson();

if ( $mibboEngine->isPluginRequest()){
    $mibboEngine->loadPlugin();
}

$content = $mibboEngine->loadContent();
$mibboEngine->finalizeTemplate($content);

// Fin de la DB transaction
if(!empty($MIB_DB))
    $MIB_DB->end_transaction();

$mibboEngine->printPage();

// Ferme la conection à la DB
if(!empty($MIB_DB))
    $MIB_DB->close();

mib_headers_no_cache(); // Envoi des header (no-cache)
header('Content-type: text/html; charset=utf-8'); // Envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose

// Affiche la page
exit($MIB_PAGE['main']);
