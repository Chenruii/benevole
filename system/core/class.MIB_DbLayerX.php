<?php
/*
Script:
	Génération de la class $MIB_DB->

Author:
	Jonathan OCHEJ, <jonathan.ochej@gmail.com>

Copyright (C):
	2010-2015 2BO&CO. This file is part of Mibbo.

Examples of usage :
	$MIB_DB = new MIB_DbLayerX(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PREFIX, DB_P_CONNECT);
*/

// Assurons nous que le script n'est pas appelé "directement"
if (!defined('MIB'))
	exit;

// Revois le timestamp (avec les microsecondes) comme flotteur (utilisé pour dblayer)
if (defined('MIB_DEBUG')) {
	function get_microtime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}

if (file_exists($mib_class['MIB_DbLayerX']['path'].'class.'.$mib_class['MIB_DbLayerX']['name'].'/'.DB_TYPE.'.php'))
	require $mib_class['MIB_DbLayerX']['path'].'class.'.$mib_class['MIB_DbLayerX']['name'].'/'.DB_TYPE.'.php';
else
	mib_error(sprintf(__('%1$s n\'est pas un type de base de données supporté. Veuillez vérifier vos paramètres de configuration dans le fichier %2$s'), '<code>'.DB_TYPE.'</code>', '<code>config.php</code>'));