<?php

/*---

	Copyright (c) 2010-2014 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit; // Assurons nous que le script n'est pas executé "directement"

/*
	FICHIER DE PROCESSUS COMMUN CHARGÉ PAR MIBBO
*/

// head
if ( !empty($MIB_PAGE['rub']) ) $MIB_PAGE['header']['canonical'] = '<link rel="canonical" href="'.$MIB_PAGE['base_url'].'/'.$MIB_PAGE['uri']['url'].'">';
else if ( $MIB_PAGE['uri']['request'] == $MIB_PAGE['lang'] ) $MIB_PAGE['header']['canonical'] = '<link rel="canonical" href="'.$MIB_PAGE['base_url'].'/">';

// Twitter
$MIB_PAGE['header']['twitter:card'] = '<meta name="twitter:card" content="summary">';
$MIB_PAGE['header']['twitter:title'] = '<meta name="twitter:title" content="'.mib_html($MIB_CONFIG['site_title']).'">';

// Facebook
if ( !empty($MIB_PAGE['rub']) ) $MIB_PAGE['header']['og:site_name'] = '<meta property="og:site_name" content="'.mib_html($MIB_CONFIG['site_title']).'">';
$MIB_PAGE['header']['og:type'] = '<meta property="og:type" content="website">';
$MIB_PAGE['header']['og:title'] = '<meta property="og:title" content="'.mib_html($MIB_CONFIG['site_title']).'">';
$MIB_PAGE['header']['og:url'] = '<meta property="og:url" content="'.(empty($MIB_PAGE['rub']) ? $MIB_CONFIG['base_url'] : $MIB_PAGE['base_url'].'/'.$MIB_PAGE['uri']['url']).'">';
if ( $MIB_PAGE['uri']['request'] == $MIB_PAGE['lang'] ) $MIB_PAGE['header']['og:url'] = '<meta property="og:url" content="'.$MIB_PAGE['base_url'].'/">';
if ( file_exists(MIB_ROOT.'logo-square-HD.png') ) $MIB_PAGE['header']['og:image'] = '<meta property="og:image" content="'.$MIB_CONFIG['base_url'].'/logo-square-HD.png">';
$MIB_PAGE['header']['og:description'] = '<meta property="og:description" content="{{tpl:MIBpage meta_description}}">';

// ajoute l'id #hp si on est sur la page d'accueil
$MIB_PAGE['body_id'] = empty($MIB_PAGE['rub']) ? 'hp' : 'page-'.$MIB_PAGE['rub'];

// active coming soon
//if ( !defined('MIB_MANAGE') && empty($MIB_PAGE['rub']) && mib_file_exists('plugins/coming-soon') )
//	$MIB_PAGE['rub'] = 'coming-soon';
