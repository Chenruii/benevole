<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

// charge les fonctions dédiés
if ( file_exists($MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php') ) require_once $MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php';

// séparation des actions par fichier pour une meilleur lisibilité
$MIB_PLUGIN['action'] = mib_get_request_infos();
if ( $MIB_PLUGIN['action'] && file_exists($MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php') ) {
	require_once $MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php';

	return;
}
// erreur, l'action n'a pas de fichier correspondant
else if ( $MIB_PLUGIN['action'] ) {
	if ( defined('MIB_JSON') )
		define('MIB_JSONED', 1);
	else
		define('MIB_AJAXED', 1);

	if ( $MIB_PLUGIN['action'] != 'process_all' ) // ce n'est pas une demande de process détaillés
		error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));
}
else if ( !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax pour la page d'accueil du plugin

/*
	Accueil
*/
if ( file_exists($MIB_PLUGIN['path'].'manage/server.php') )
	require_once $MIB_PLUGIN['path'].'manage/server.php';

if ( file_exists($MIB_PLUGIN['path'].'manage/system.php') )
	require_once $MIB_PLUGIN['path'].'manage/system.php';