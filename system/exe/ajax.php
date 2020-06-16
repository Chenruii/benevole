<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
if (!defined('MIB') || !defined('MIB_AJAX'))
	exit;

// Un petit sleep pour afficher les indicateurs de chargement en local ;)
if (defined('MIB_DEBUG'))
	sleep(1);

//On stock les infos ajax dans un tableau
$AJAX = array();

// Charge un plugin si il existe
if(!empty($MIB_PAGE['rub']) && mib_file_exists('plugins/'.$MIB_PAGE['rub']))
	$MIB_PLUGIN = mib_load_plugin($MIB_PAGE['rub']);

// Aucun plugin n'a été chargé, on essaye de charger le fichier
if (!defined('MIB_PLUGIN'))
	$AJAX['tpl'] = load_file();
else {
	if (!defined('MIB_AJAXED')) // Si on a chargé plugin qui n'était pas de l'ajax on exit
		exit;
	$AJAX['tpl'] = $MIB_PLUGIN['tpl'];
}

// Fin de la DB transaction
$MIB_DB->end_transaction();

// Remplace les balises de template(url rewriting, etc...)
$AJAX['tpl'] = mib_tpl_replace($AJAX['tpl']);

// Compression du code HTML avec Minify
if (defined('MIB_COMPRESS_HTML') && mib_core_class('Minify_HTML')) {
	$AJAX['tpl_compress'] = Minify_HTML::minify($AJAX['tpl']);

	if($AJAX['tpl_compress'] && !empty($AJAX['tpl_compress']))
		$AJAX['tpl'] = $AJAX['tpl_compress'];
}

// Ferme la conection à la DB
$MIB_DB->close();

mib_headers_no_cache(); // Envoi des header (no-cache)
header('Content-type: text/html; charset=utf-8'); // Envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose

exit($AJAX['tpl']);