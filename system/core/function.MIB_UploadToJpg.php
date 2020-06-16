<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit;

function MIB_UploadToJpg($img_uploaded, $img_destination, $max_width = 0, $max_height = 0, $resize = false, $quality = 100) {
	$errors = array();

	// Détermine la couleur d'arrière plan blanche lors d'un crop ou resize pour les images transparentes
	$background = 'FFFFFF';
	// Split the HTML color representation
	$bg['hex'] = str_split($background, 2); 
	// Convert HEX values to DECIMAL
	$bg['r'] = hexdec("0x{$bg['hex'][0]}");
	$bg['g'] = hexdec("0x{$bg['hex'][1]}");
	$bg['b'] = hexdec("0x{$bg['hex'][2]}");

	// Valide l'envois du fichier
	$errors = array_merge($errors, mib_core_function('MIB_isUploaded', $img_uploaded));

	// Charge l'image avant de la travailler
	if (empty($errors)) {
		list($open_image, $open_errors) =  mib_core_function('MIB_OpenImg', $img_uploaded['tmp_name'], 'jpg,gif,png');
		if ( $open_errors )
			$errors = array_merge($errors, $open_errors);
	}

	if (empty($errors)) {
		// Quelle est la taille de l'image
		list($open_image_width, $open_image_height) = getimagesize($img_uploaded['tmp_name']);

		// Si on doit resize l'image
		if( ($max_width > 0 || $max_height > 0) && ($open_image_width > $max_width || $open_image_height > $max_height) ) {
			$max_width = $max_width > 0 ? $max_width : $open_image_width;
			$max_height = $max_height > 0 ? $max_height : $open_image_height;

			// crop, on découpe l'image
			if($resize == 'crop') {
				// On découpe en fonction de la hauteur ou la largeur ?
				$ratio_width = $max_width / $open_image_width;
				$ratio_height = $max_height / $open_image_height;

				// Image plus haute que large
				if($ratio_width > $ratio_height) {
					$new_image_ratio = $ratio_width;
					$new_y_position = ($open_image_height * $new_image_ratio / 2) - ($max_height / 2);
					$new_x_position = 0;
				}
				else {
					$new_image_ratio = $ratio_height;
					$new_x_position = ($open_image_width * $new_image_ratio / 2) - ($max_width / 2);
					$new_y_position = 0;
				}
			}
			// resize
			else {
				// La largeur est trop grande
				if($open_image_width > $max_width)
					$new_image_ratio_width = $max_width / $open_image_width;
				else
					$new_image_ratio_width = 1;

				// La hauteur est trop grande
				if($open_image_height > $max_height)
					$new_image_ratio_height = $max_height / $open_image_height;
				else
					$new_image_ratio_height = 1;

				// On garde le ratio le plus petit
				if($new_image_ratio_width <= $new_image_ratio_height)
					$new_image_ratio = $new_image_ratio_width;
				else
					$new_image_ratio = $new_image_ratio_height;
			}
		}

		if(!$new_image_ratio) $new_image_ratio = 1;
		if(!$new_x_position) $new_x_position = 0;
		if(!$new_y_position) $new_y_position = 0;

		// Création de l'image
		$tmp_image_resize = imagecreatetruecolor(round($open_image_width*$new_image_ratio), round($open_image_height*$new_image_ratio));
		// On colorize l'arrière plan
		$tmp_image_bg = imagecolorallocate($tmp_image_resize, $bg['r'], $bg['g'], $bg['b']);
		ImageFilledRectangle($tmp_image_resize, 0, 0, round($open_image_width*$new_image_ratio), round($open_image_height*$new_image_ratio), $tmp_image_bg); 
		// Création de l'image
		imagecopyresampled($tmp_image_resize,$open_image,0,0,0,0,round($open_image_width*$new_image_ratio),round($open_image_height*$new_image_ratio),$open_image_width,$open_image_height);

		// on découpe l'image
		if( $new_image_ratio != 1 && $resize == 'crop' ) {
			// On découpe
			$new_image_resize = imagecreatetruecolor($max_width, $max_height);
			// On colorize l'arrière plan
			$new_image_bg = imagecolorallocate($new_image_resize, $bg['r'], $bg['g'], $bg['b']);
			ImageFilledRectangle($new_image_resize, 0, 0, $max_width, $max_height, $new_image_bg); 
			//imagecopy ( resource   dst_im  , resource   src_im  , int   dst_x  , int   dst_y  , int   src_x  , int   src_y  , int   src_w  , int   src_h  )
			imagecopy($new_image_resize, $tmp_image_resize, 0, 0, $new_x_position, $new_y_position, round($open_image_width*$new_image_ratio), round($open_image_height*$new_image_ratio));
		}
		else
			$new_image_resize = $tmp_image_resize;

		@imagejpeg($new_image_resize, $img_destination, $quality);

		// Libération de la mémoire
		@imagedestroy($tmp_image_resize);
		// Libération de la mémoire
		@imagedestroy($new_image_resize);

		if (!file_exists($img_destination)) {
			@unlink($img_destination);
			$errors[] = __('Erreur inconnue. Veuillez vérifier les droits en écriture pour le dossier de destination de votre image.');
		}
	}

	return $errors;
}