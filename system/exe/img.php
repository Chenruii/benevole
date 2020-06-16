<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
if ( !defined('MIB') || !defined('MIB_IMG') || $MIB_PAGE['rub'] != 'img' )
	exit;

$IMG = array(
	'info'							=> $MIB_PAGE['info'],
	'name'							=> mib_get_request_infos(-1),
	'path'							=> substr($MIB_PAGE['info'], 0, -(strlen(mib_get_request_infos(-1)))),
	'ext'							=> strtolower($MIB_PAGE['ext']),
	'ext_priority'					=> array('png','jpg','jpeg','gif','bmp'), // ordre de priorité du format source de l'image demandée
	'width'							=> null,
	'height'						=> null,
	'resize'						=> 'crop', // type de redimensionnement si necessaire (crop|max|min)
	'quality'						=> 70,
	'placeholder' 					=> false, // Est-ce un placeholder demandé ?
	'placeholder_background_color'	=> isset($_GET['b']) ? mib_trim($_GET['b']) : '#DDD',
	'placeholder_text'				=> isset($_GET['t']) ? mib_trim($_GET['t']) : false,
	'placeholder_text_color'		=> isset($_GET['c']) ? mib_trim($_GET['c']) : '#333',
);

// protection du path pour ne pas remonter à la racine avec des ../../
$paths = explode('/', $IMG['info']);
if ( is_array($paths) && !empty($paths) ) {
	$new_paths = array();
	foreach ( $paths as $cur_path ) {
		if ( !empty($cur_path) && $cur_path != '..' && $cur_path != '.' )
			$new_paths[] = $cur_path;
	}
	$IMG['info'] = implode('/', $new_paths);
}

// détermine le type demandé
if ( in_array($IMG['ext'], array('jpg','jpeg')) )
	$IMG['type'] = IMAGETYPE_JPEG;
else if ( $IMG['ext'] == 'gif' )
	$IMG['type'] = IMAGETYPE_GIF;
else if ( $IMG['ext'] == 'png' )
	$IMG['type'] = IMAGETYPE_PNG;
else
	mib_error_404(); // format non pris en charge

// il y a "max" ou "min" à la fin, l'image demandée doit surement être redimensionnée/découpée
$limit = substr($IMG['name'], -6);
if ( in_array($limit, array('px-max','px-min')) ) {
	$IMG['name'] = substr($IMG['name'], 0, -4);
	$IMG['resize'] = substr($limit, 3);
}

// il y a "px" la fin, l'image demandée doit surement être redimensionnée/découpée
if ( substr($IMG['name'], -2) == 'px' ) {
	$IMG['name'] = substr($IMG['name'], 0, -2);

	// Placeholder demandé ? car ne comprend que des nombres ou un x (pas de "-" ni de "/")
	if ( preg_match("#^[0-9x]+$#", substr($IMG['info'], 0, -2)) ) {
		if ( strpos($IMG['name'], 'x') === false ) { // pas de "x" précisant largeur et hauteur
			if ( is_numeric($IMG['name']) ) {
				$IMG['placeholder'] = true;
				$IMG['width'] = intval($IMG['name']);
				$IMG['height'] = intval($IMG['name']);
			}
		}
		else {
			$sizes = explode('x', $IMG['name']);
			if ( count($sizes) == 1 && is_numeric($sizes[0]) ) {
				$IMG['placeholder'] = true;
				$IMG['width'] = intval($sizes[0]);
				$IMG['height'] = intval($sizes[0]);
			}
			else if ( count($sizes) == 2 && is_numeric($sizes[0]) && is_numeric($sizes[1]) ) {
				$IMG['placeholder'] = true;
				$IMG['width'] = intval($sizes[0]);
				$IMG['height'] = intval($sizes[1]);
			}
		}
	}

	// Ce n'est pas un placeholder, séparation de dimension
	if ( !$IMG['placeholder'] && strpos($IMG['name'], '-') !== false ) {
		$size = substr(strrchr(strtolower($IMG['name']),'-'), 1);
		if ( strpos($size, 'x') === false ) { // pas de "x" précisant largeur et hauteur
			if ( is_numeric($size) ) {
				$IMG['width'] = intval($size);
				$IMG['height'] = intval($size);
			}
		}
		else {
			$sizes = explode('x', $size);
			if ( count($sizes) == 1 && is_numeric($sizes[0]) ) {
				$IMG['width'] = intval($sizes[0]);
				$IMG['height'] = intval($sizes[0]);
			}
			else if ( count($sizes) == 2 && is_numeric($sizes[0]) && is_numeric($sizes[1]) ) {
				$IMG['width'] = intval($sizes[0]);
				$IMG['height'] = intval($sizes[1]);
			}
		}

		if ( $IMG['width'] && $IMG['height'] ) $IMG['name'] = substr($IMG['name'], 0, -(strlen($size)+1));
	}

	// ce n'est pas un placeholder, ni un redimensionnement, on remet le "px" à la fin (au cas ou l'image s'appelle "xpx")
	if ( !$IMG['width'] && !$IMG['height'] ) {
		$IMG['name'] = mib_get_request_infos(-1);
		$IMG['resize'] = 'crop';
	}
}

// affiche le placeholder
if ( $IMG['placeholder'] ) {
	mib_image_placeholder($IMG['width'], $IMG['height'], array(
		'type'			=> $IMG['type'],
		'background'	=> $IMG['placeholder_background_color'],
		'text'			=> $IMG['placeholder_text'],
		'color'			=> $IMG['placeholder_text_color'],
		'quality'		=> $IMG['quality'],
		'interlace'		=> 1,
	));
}
// recherche l'image demandée
else {
	$found = false;
	$name = mib_get_request_infos(-1);
	$try = array_merge(array($IMG['ext']), $IMG['ext_priority']);
	$try_paths = array();

	// image du dossier public (prioritaire, car permet de modifier les images de themes et plugin directement dans le dossier public)
	$try_paths = array_merge($try_paths, array(
		MIB_PUBLIC_DIR.$IMG['path'].$name				=> $name, // nom du fichier original
		MIB_PUBLIC_DIR.$IMG['path'].$IMG['name']		=> $IMG['name'], // nom modifié (sans les px)
	));

	// image d'un plugin
	if ( mib_get_request_infos() == 'plugin' ) {
		$plugin = mib_trim(substr($IMG['path'], strlen('plugin/')), '/');
		if ( !empty($plugin) ) { // il faut au minimum qu'un plugin soit précisé
			$try_paths = array_merge($try_paths, array(
				// dossier website
				MIB_PATH_VAR.'plugins/'.$plugin.'/img/'.$name					=> $name, // nom du fichier original
				MIB_PATH_VAR.'plugins/'.$plugin.'/img/'.$IMG['name']			=> $IMG['name'], // nom modifié (sans les px)
				// dossier system
				MIB_PATH_SYS.'plugins/'.$plugin.'/img/'.$name					=> $name, // nom du fichier original
				MIB_PATH_SYS.'plugins/'.$plugin.'/img/'.$IMG['name']			=> $IMG['name'], // nom modifié (sans les px)
			));
		}
	}

	// image d'un theme
	if ( mib_get_request_infos() == 'theme' ) {
		$path = substr($IMG['path'], strlen('theme/'));
		$try_paths = array_merge($try_paths, array(
			// dossier du thème
			MIB_THEME_DIR.'img/'.$path.$name					=> $name, // nom du fichier original
			MIB_THEME_DIR.'img/'.$path.$IMG['name']				=> $IMG['name'], // nom du fichier original
			// dossier du thème par défaut
			MIB_THEME_DEFAULT_DIR.'img/'.$path.$name			=> $name, // nom du fichier original
			MIB_THEME_DEFAULT_DIR.'img/'.$path.$IMG['name']		=> $IMG['name'], // nom du fichier original
		));
	}

	// on cherche l'image source
	foreach( $try_paths as $p => $n ) {
		foreach( $try as $ext ) {
			if ( file_exists($p.'.'.$ext) ) {
				$found = $p.'.'.$ext;
				if ( $n != $IMG['name'] ) { // nom du fichier original
					$IMG['name'] = $n;
					$IMG['width'] = $IMG['height'] = false;
				}
				break;
			}
		}
		if ( $found ) break;
	}

	if ( $found ) {
		mib_image($found, array(
			'type'			=> $IMG['type'],
			'width'			=> $IMG['width'],
			'height'		=> $IMG['height'],
			'resize'		=> $IMG['resize'],
			'quality'		=> $IMG['quality'],
		));
	}
	else
		mib_error_404();
}