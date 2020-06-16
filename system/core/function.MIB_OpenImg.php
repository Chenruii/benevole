<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit;

function MIB_OpenImg($image, $format = false) {
	$errors = array();

	//Check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		$errors[] = __('La librairie GB n\'est pas chargée !');
		return array(false, $errors);
	}

	// Autorise t'on qu'un type de format ?
	if($format) $format = explode(',',$format);

	// Chargement JPEG
	if(!$format || (is_array($format) && in_array('jpg', $format)) || (is_array($format) && in_array('jpeg', $format))) {
		$image_open = @imagecreatefromjpeg($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement GIF
	if(!$format || (is_array($format) && in_array('gif', $format))) {
		$image_open = @imagecreatefromgif($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement PNG
	if(!$format || (is_array($format) && in_array('png', $format))) {
		$image_open = @imagecreatefrompng($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement GD2 File
	if(!$format || (is_array($format) && in_array('gd2', $format))) {
		$image_open = @imagecreatefromgd2($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement WBMP
	if(!$format || (is_array($format) && in_array('bmp', $format))) {
		$image_open = @imagecreatefromwbmp($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement XBM
	if(!$format || (is_array($format) && in_array('xbm', $format))) {
		$image_open = @imagecreatefromxbm($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Chargement XPM
	if(!$format || (is_array($format) && in_array('xpm', $format))) {
		$image_open = @imagecreatefromxpm($image);
		if ($image_open !== false) { return array($image_open, false); }
	}

	// Dernier essais de chargement depuis le string !!!!
	if(!$format || (is_array($format) && in_array('string', $format))) {
		$image_open = @imagecreatefromstring(file_get_contents($image));
		if ($image_open !== false) { return array($image_open, false); }
	}

	$errors[] = __('Impossible de charger l\'image envoyée. Format non reconnu.');
	return array(false, $errors);
}