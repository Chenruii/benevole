<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit;

/**
 * Permet de tester si un fichier a bien été uploadé
 * 
 * 
 * 
 *
 * @param string $return Valeur de retour
 *
 * @return string|array
 */
function MIB_isUploaded($file = false) {
	$errors = array();

	if(isset($file) && !empty($file)) {
		// Assurons nous qu'il n'y a pas d'erreurs
		if (isset($file['error'])) {
			switch ($file['error']) {
				case 1:	// UPLOAD_ERR_INI_SIZE
					$errors[] = __('Le fichier est trop lourd ('.str_replace('M','Mo',ini_get('upload_max_filesize')).' max par fichier).');
					break;
				case 2:	// UPLOAD_ERR_FORM_SIZE
					$errors[] = __('Le fichier est trop lourd (> à FORM_SIZE).');
					break;
				case 3:	// UPLOAD_ERR_PARTIAL
					$errors[] = __('L\'envoi du fichier a échoué. Veuillez réessayer.');
					break;
				case 4:	// UPLOAD_ERR_NO_FILE
					$errors[] = __('Aucun fichier à envoyer !');
					break;
				case 6:	// UPLOAD_ERR_NO_TMP_DIR
					$errors[] = __('Il n\'y a pas de dossier TMP pour envoyer le fichier. Contactez l\'administrateur.');
					break;
				default:
					// No error occured, but was something actually uploaded?
					if ($file['size'] == 0)
						$errors[] = __('Le fichier que vous voulez envoyer à un poid égale à zero !');
					break;
			}
		}

		if (!is_uploaded_file($file['tmp_name']))
			$errors[] = __('Erreur inconnue, l\'envoi du fichier a échoué !');
	}
	else
		$errors[] = __('La variable $file est vide !');

	return $errors;
}