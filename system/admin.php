<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
if (!defined('MIB') || !defined('MIB_MANAGE'))
	exit;

// Charge le script des fonctions spécifiques (admin)
if(file_exists(MIB_PATH_SYS.'functions_admin.php'))
	require MIB_PATH_SYS.'functions_admin.php';

// Charge le script commun spécifiques (admin)
if(file_exists(MIB_PATH_SYS.'common_admin.php'))
	require MIB_PATH_SYS.'common_admin.php';

// Charge le script des fonctions spécifiques (admin)
if(file_exists(MIB_PATH_VAR.'functions_admin.php'))
	require MIB_PATH_VAR.'functions_admin.php';

// Charge le script commun spécifiques (admin)
if(file_exists(MIB_PATH_VAR.'common_admin.php'))
	require MIB_PATH_VAR.'common_admin.php';

// Utilisateur logué
if (!$MIB_USER['is_guest']) {
	$errors = array();

	// on test si l'utilisateur à accès au BO
	$errors = array_merge($errors, validate_bo_perms());

	if(empty($errors)) { // aucune erreur

		if(empty($MIB_PAGE['rub'])) // Charge la page d'accueil du BO
			$MIB_PAGE['rub'] = 'bo';
		else { // Charge le plugin admin demandé
			if(defined('MIB_JS') || defined('MIB_CSS') || defined('MIB_IMG') || $MIB_PAGE['rub'] == 'bo' || is_valid_bo_plugin($MIB_PAGE['rub'])) // javascript ou css
				define('MIB_PLUGIN_MANAGE', 1);
			else
				error(__('Impossible de charger l\'extension. L\'extension à peut être été désactivée ou supprimée du Back Office.'));
		}
	}
	else { // Pas d'autorisation de connexion ou autorisation périmée !

		// requette JSON de l'auto-connexion/auto-déconnexion ou de déconnexion
		if(defined('MIB_JSON')) { 
			if($MIB_PAGE['rub'] == 'bo' && (current(explode('/', $MIB_PAGE['info'])) == 'idle' || current(explode('/', $MIB_PAGE['info'])) == 'logout'))
				return; // Autorisé
			else
				error('<p>'.implode('</p><p>', $errors).'</p>');
		}

		if(defined('MIB_AJAX'))
			error('<p>'.implode('</p><p>', $errors).'</p>');

		// On le déconnecte : Suppression du cookie d'autoconnexion
		$expire = time() + 1209600;
		mib_setcookie(COOKIE_NAME, base64_encode('1|'.mib_random_key(8, false, true).'|'.$expire.'|'.mib_random_key(8, false, true)), $expire);

		if(defined('MIB_JS') || defined('MIB_CSS') || defined('MIB_IMG'))
			exit;
		else
			mib_header(MIB_ADMIN_DIR.'/'); // on redirige vers la page de connexion du BO
	}
}
// Non connecté
else {
	// requette JSON de l'auto-connexion/auto-déconnexion
	if(defined('MIB_JSON')) { 
		if($MIB_PAGE['rub'] == 'bo' && current(explode('/', $MIB_PAGE['info'])) == 'idle')
			return; // Autorisé
		else
			error(__('Vous n\'avez pas la permission d\'accéder au Back Office.'));
	}

	// Requette AJAX 
	if(defined('MIB_AJAX'))
		error(__('Vous n\'avez pas la permission d\'accéder au Back Office.'));

	// Ne charge pas les JS et CSS d'admin si l'user n'est pas logué
	if(defined('MIB_JS') || defined('MIB_IMG') )
		exit;
	if(defined('MIB_CSS'))
		return;

	$MIB_PAGE['rub'] = 'bo'; // Charge le back Office (normalement le login)
}