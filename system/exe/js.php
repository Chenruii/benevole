<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

	Charge le fichier "nomdufichier.js" demandé.
	Si MIB_COMPRESS_JS est activé, on essai de charger
	"nomdufichier.min.js" à la place. Si ce fichier n'existe pas,
	on compresse le js à la volée avec JSMin
*/

// Assurons nous que le script n'est pas executé "directement"
if ( !defined('MIB') ||  ( !defined('MIB_JS') && !defined('MIB_LIBS')) ||  ( $MIB_PAGE['rub'] != 'js' &&  $MIB_PAGE['rub'] != 'libs')  || $MIB_PAGE['ext'] != 'js' )
	exit;

$JS = array(
	'info'							=> $MIB_PAGE['info'],
	'name'							=> mib_get_request_infos(-1),
	'path'							=> substr($MIB_PAGE['info'], 0, -(strlen(mib_get_request_infos(-1)))),
	'packed'						=> false,
);


$paths = explode('/', $JS['info']);
// protection du path pour ne pas remonter à la racine avec des ../../
if ( is_array($paths) && !empty($paths) ) {
	$new_paths = array();
	foreach ( $paths as $cur_path ) {
		if ( !empty($cur_path) && $cur_path != '..' && $cur_path != '.' )
			$new_paths[] = $cur_path;
	}
	$JS['info'] = implode('/', $new_paths);
}
$isLibFile =  defined('MIB_LIBS');



// il y a "min" à la fin, le js demandé est déjà commpressé
if ( in_array(substr($JS['name'], -4), array('-min','.min')) ) $JS['packed'] = true;

// recherche le fichier
$JS['found'] = false;
if ($isLibFile){
    $try_paths= array(MIB_PATH_SYS.'libs/'.$JS['path'].$JS['name']);
}else {
    $try_paths = array(
        // dans le répertoire "js" du theme
        MIB_THEME_DIR.'js/'.$JS['path'].$JS['name'],
        MIB_THEME_DEFAULT_DIR.'js/'.$JS['path'].$JS['name'],
        // dans le répertoire du thème directement
        MIB_THEME_DIR.$JS['path'].$JS['name'],
        MIB_THEME_DEFAULT_DIR.$JS['path'].$JS['name'],
    );
    if ( defined('MIB_MANAGE') ) {
        $try_paths[] = MIB_THEME_DIR.'admin/js/'.$JS['path'].$JS['name'];
        $try_paths[] = MIB_THEME_DEFAULT_DIR.'admin/js/'.$JS['path'].$JS['name'];
        $try_paths[] = MIB_THEME_DIR.'admin/'.$JS['path'].$JS['name'];
        $try_paths[] = MIB_THEME_DEFAULT_DIR.'admin/'.$JS['path'].$JS['name'];
    }
}

// on cherche le javascript source
foreach( $try_paths as $p ) {
	if ( file_exists($p.'.js') ) {
		$JS['found'] = $p.'.js';
		$JS['lastmod'] = filemtime($JS['found']);

		if ( defined('MIB_COMPRESS_JS') && !$JS['packed'] ) {
			if ( file_exists($p.'.min.js') && filemtime($p.'.min.js') >= $JS['lastmod'] ) {
				$JS['found'] = $p.'.min.js';
				$JS['packed'] = true;
			}
		}
		break;
	}
}

if ( $JS['found'] ) {
	// Si la connection à la DB avait été établie on la ferme
	$MIB_DB->end_transaction(); $MIB_DB->close();

	// id unique pour la mise en cache
	$JS['uid'] = $JS['found'].$JS['lastmod'].((defined('MIB_COMPRESS_JS') || $JS['packed']) ? 'min' : '');
	$JS['hash'] = mib_hash($JS['uid']);

	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $JS['lastmod']).' GMT');
	header('Etag: '.$JS['hash']);
	header('Cache-Control: public');

	if ( @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $JS['lastmod'] || (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && mib_trim($_SERVER['HTTP_IF_NONE_MATCH']) == $JS['hash']) ) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	$JS['tpl'] = file_get_contents($JS['found']);
	$JS['tpl'] = mib_tpl_replace($JS['tpl']);

	// compress le js
	if ( defined('MIB_COMPRESS_JS') && !$JS['packed'] && mib_core_class('JSMin') ) {
		$JS['tpl'] = JSMin::minify($JS['tpl']);
		$JS['packed'] = true;
	}

	header('Content-type: application/javascript; charset=utf-8');
	header('Content-Length: '.strlen($JS['tpl']));

	exit($JS['tpl']);
}
else
	mib_error_404();