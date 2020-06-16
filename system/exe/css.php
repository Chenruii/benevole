<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

	Charge le fichier "nomdufichier.css" demandé.
	Si MIB_COMPRESS_CSS est activé, on essai de charger
	"nomdufichier.min.css" à la place. Si ce fichier n'existe pas,
	on compresse le css à la volée avec Minify_CSS_Compressor
*/

// Assurons nous que le script n'est pas executé "directement"
if ( !defined('MIB') ||  ( !defined('MIB_CSS') && !defined('MIB_LIBS')) ||  ( $MIB_PAGE['rub'] != 'css' &&  $MIB_PAGE['rub'] != 'libs') || $MIB_PAGE['ext'] != 'css' )
	exit;


$paths = !empty($CSS['info']) ?  explode('/', $CSS['info']) : [];

$CSS = array(
	'info'							=> $MIB_PAGE['info'],
	'name'							=> mib_get_request_infos(-1),
	'path'							=> substr($MIB_PAGE['info'], 0, -(strlen(mib_get_request_infos(-1)))),
	'packed'						=> false,
);


$paths = explode('/', $CSS['info']);

// protection du path pour ne pas remonter à la racine avec des ../../
if ( is_array($paths) && !empty($paths) ) {
	$new_paths = array();
	foreach ( $paths as $cur_path ) {
		if ( !empty($cur_path) && $cur_path != '..' && $cur_path != '.' )
			$new_paths[] = $cur_path;
	}
	$CSS['info'] = implode('/', $new_paths);
}

// on traite les cas des libs
$isLibFile =  defined('MIB_LIBS');

// il y a "min" à la fin, le css demandé est déjà commpressé
if ( in_array(substr($CSS['name'], -4), array('-min','.min')) ) $CSS['packed'] = true;

$CSS['found'] = false;

// CSS admin
if ( $CSS['info'] == 'admin' ) {
	if ( !defined('MIB_MANAGE') ) mib_error_404();

	$CSS['found'] = $CSS['info'];
	$CSS['tpl'] = '';
	$CSS['lastmod'] = 0;

	foreach ( array('admin/admin','css/mibbo-admin') as $css ) {
		$found = $tpl = $lastmod = false;
		if ( $found = file_exists(MIB_THEME_DIR.$css.'.css') ) {
			$tpl = file_get_contents(MIB_THEME_DIR.$css.'.css');
			$lastmod = filemtime(MIB_THEME_DIR.$css.'.css');
			$CSS['theme'] = MIB_THEME;
		}
		else if ( $found = file_exists(MIB_THEME_DEFAULT_DIR.$css.'.css') ) {
			$tpl = file_get_contents(MIB_THEME_DEFAULT_DIR.$css.'.css');
			$lastmod = filemtime(MIB_THEME_DEFAULT_DIR.$css.'.css');
			$CSS['theme'] = MIB_THEME_DEFAULT;
		}

		if ( $found ) {
			$CSS['tpl'] .= str_replace('{{tpl:MIB_THEME}}', '../../../theme/'.$CSS['theme'], $tpl);
			if ( $lastmod && $CSS['lastmod'] < $lastmod ) $CSS['lastmod'] = $lastmod;
		}
	}

	// ajoute la couleur personnalisée du BO
	if ( !empty($MIB_CONFIG['bo_color']) ) {
		$MIB_CONFIG['bo_color'] = '#'.preg_replace("/[^0-9A-Fa-f]/", '', $MIB_CONFIG['bo_color']);

		if ( !empty($CSS['tpl']) ) $CSS['tpl'] .= "\n";

		$CSS['tpl'] .= '.admin a, .admin a:hover, #login h1 span, .menu li a:hover span, ';
		$CSS['tpl'] .= '.admin .button:hover, .admin .button:focus, .admin .input:focus, ';
		$CSS['tpl'] .= '#MIB_pannel li a:hover, #MIB_page fieldset legend, .nav-result strong, ';
		$CSS['tpl'] .= '#MIB_page .input:focus, .acp .input:focus, ';
		$CSS['tpl'] .= 'input.input[type="checkbox"]:focus + label:after ';
		$CSS['tpl'] .= '{ color: '.$MIB_CONFIG['bo_color'].'; }';
		$CSS['tpl'] .= "\n";
		$CSS['tpl'] .= '#MIB_pannel li a:hover, .admin .button:hover, ';
		$CSS['tpl'] .= '.admin .button:focus, .admin .input:focus, ';
		$CSS['tpl'] .= 'input.input[type="checkbox"]:focus + label:before, ';
		$CSS['tpl'] .= 'input.input[type="radio"]:focus + label:before ';
		$CSS['tpl'] .= '{ border-color: '.$MIB_CONFIG['bo_color'].'; }';
		$CSS['tpl'] .= "\n";
		$CSS['tpl'] .= 'fieldset.toggle legend:before, fieldset.toggle legend:after, ';
		$CSS['tpl'] .= 'input.input[type="radio"]:focus + label:after ';
		$CSS['tpl'] .= '{ background-color: '.$MIB_CONFIG['bo_color'].'; }';
		$CSS['tpl'] .= "\n";
	}
}
else if ( $isLibFile && mib_file_exists('libs/'.$CSS['info'].'.css') ) {
    $CSS['found'] = MIB_PATH_SYS.'libs/'.$CSS['info'].'.css';
    $CSS['tpl'] = file_get_contents($CSS['found']);
    $CSS['lastmod'] = filemtime($CSS['found']);
    $CSS['location'] = 'system';
}
else if ( mib_file_exists('plugins/'.$CSS['info'].'/'.$CSS['info'].'.css') ) {
	$CSS['found'] = 'plugins/'.$CSS['info'].'/'.$CSS['info'].'.css';

	if ( file_exists(MIB_PATH_VAR.$CSS['found']) ) {
		$CSS['found'] = MIB_PATH_VAR.$CSS['found'];
		$CSS['tpl'] = file_get_contents($CSS['found']);
		$CSS['lastmod'] = filemtime($CSS['found']);
		$CSS['location'] = 'website';
	}
	else if ( file_exists(MIB_PATH_SYS.$CSS['found']) ) {
		$CSS['found'] = MIB_PATH_SYS.$CSS['found'];
		$CSS['tpl'] = file_get_contents($CSS['found']);
		$CSS['lastmod'] = filemtime($CSS['found']);
		$CSS['location'] = 'system';
	}

	// Remplace {{tpl:MIB_PLUGIN}} par le répertoire du plugin en cours
	$replace = '../plugin/'.$CSS['location'].'/'.$CSS['info'];
	if ( defined('MIB_MANAGE') )
		$replace = '../../'.$replace;
	else if ( $MIB_PAGE['base_url'] != $MIB_CONFIG['base_url'] )
		$replace = '../'.$replace;

	$CSS['tpl'] = str_replace('{{tpl:MIB_PLUGIN}}', $replace, $CSS['tpl']);
}
else {
	// CSS: répertoire CSS par defaut
	if ( mib_theme_exists('css/'.$CSS['info'].'.css') )
		$CSS['found'] = 'css/'.$CSS['info'].'.css';
	// CSS: répertoire indiqué
	else if ( mib_theme_exists($CSS['info'].'.css') )
		$CSS['found'] = $CSS['info'].'.css';
	else
		mib_error_404();

	if ( file_exists(MIB_THEME_DIR.$CSS['found']) ) {
		$CSS['found'] = MIB_THEME_DIR.$CSS['found'];
		$CSS['tpl'] = file_get_contents($CSS['found']);
		$CSS['lastmod'] = filemtime($CSS['found']);
		$CSS['theme'] = MIB_THEME;
	}
	else if ( file_exists(MIB_THEME_DEFAULT_DIR.$CSS['found']) ) {
		$CSS['found'] = MIB_THEME_DEFAULT_DIR.$CSS['found'];
		$CSS['tpl'] = file_get_contents($CSS['found']);
		$CSS['lastmod'] = filemtime($CSS['found']);
		$CSS['theme'] = MIB_THEME_DEFAULT;
	}
}

if ( $CSS['found'] ) {
	// Si la connection à la DB avait été établie on la ferme
	$MIB_DB->end_transaction(); $MIB_DB->close();

	// id unique pour la mise en cache
	$CSS['uid'] = $CSS['found'].$CSS['lastmod'].((defined('MIB_COMPRESS_CSS') || $CSS['packed']) ? 'min' : '');
	$CSS['hash'] = mib_hash($CSS['uid']);

	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $CSS['lastmod']).' GMT');
	header('Etag: '.$CSS['hash']);
	header('Cache-Control: public');

	if ( @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $CSS['lastmod'] || (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && mib_trim($_SERVER['HTTP_IF_NONE_MATCH']) == $CSS['hash']) ) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// remplace {{tpl:MIB_THEME}} par le répertoire du theme en cours
    $th = empty($CSS['theme']) ? 'website': $CSS['theme'];
	$replace = '../theme/'.$th;
	if ( defined('MIB_MANAGE') )
		$replace = '../../'.$replace;
	else if ( $MIB_PAGE['base_url'] != $MIB_CONFIG['base_url'] )
		$replace = '../'.$replace;

	$CSS['tpl'] = str_replace('{{tpl:MIB_THEME}}', $replace, $CSS['tpl']);
	$CSS['tpl'] = mib_tpl_replace($CSS['tpl']);

	// compress le css
	if ( defined('MIB_COMPRESS_CSS') && !$CSS['packed'] && mib_core_class('Minify_CSS_Compressor') ) {
		$CSS['tpl_compress'] = Minify_CSS_Compressor::process($CSS['tpl']);
		if ( $CSS['tpl_compress'] && !empty($CSS['tpl_compress']) ) {
			$CSS['tpl'] = $CSS['tpl_compress'];
			$CSS['packed'] = true;
		}
	}

	header('Content-type: text/css; charset=utf-8');
	header('Content-Length: '.strlen($CSS['tpl']));

	exit($CSS['tpl']);
}
else
	mib_error_404();