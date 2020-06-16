<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_JSON') or exit; // assurons nous que le script n'est pas executé "directement"

// stock les infos json dans un tableau
$JSON = array();

// charge un plugin si il existe
if ( !empty($MIB_PAGE['rub']) && mib_file_exists('plugins/'.$MIB_PAGE['rub']) ) {
	$MIB_PLUGIN = mib_load_plugin($MIB_PAGE['rub']);
}

// rien n'a été chargé, on essaye de charger le fichier
if ( !defined('MIB_PLUGIN') && empty($MIB_PLUGIN['json']) ) {
	$JSON['return'] = load_file();
	if ( !empty($JSON['return']) ) $JSON['return'] = mib_tpl_replace($JSON['return']);
}
else {
	$JSON['return'] = (isset($MIB_PLUGIN['json']) && is_array($MIB_PLUGIN['json'])) ? $MIB_PLUGIN['json'] : array();
}

// Fin de la DB transaction
$MIB_DB->end_transaction();

// Ferme la conection à la DB
$MIB_DB->close();

// Envoi des header (no-cache)
mib_headers_no_cache();

// Envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose
if( isset($_FILES) ) // Hack pour l'upload de fichier avec réponse en JSON depuis le BO
	header('Content-type: text/html; charset=utf-8');
else
	header('Content-type: application/json; charset=utf-8');

// rien n'a été chargé
if ( empty($JSON['return']) )
	error(__('Aucune réponse JSON.'), __FILE__, __LINE__);
else
	exit(json_encode($JSON['return']));