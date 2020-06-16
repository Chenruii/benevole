<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
defined('MIB_MANAGE') or exit;

// Requette JSON
defined('MIB_JSON') or exit;
define('MIB_JSONED', 1);

define('MIB_QUIET_VISIT', 1);

if (isset($MIB_PAGE['info'])) {
	$MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info']);
	$MIB_PLUGIN['user_id'] = intval($MIB_PLUGIN['options'][1]);
	$MIB_PLUGIN['csrf_token'] = mib_trim($MIB_PLUGIN['options'][2]);
}

// On vérifie si on à la bonne URL sans "truc" en plus !
mib_confirm_current_url($MIB_PAGE['base_url'].'/json/bo/logout/'.$MIB_PLUGIN['user_id'].'/'.$MIB_PLUGIN['csrf_token']);

if(!$MIB_USER['is_guest']) {

	if($MIB_PLUGIN['user_id'] == $MIB_USER['id'] && $MIB_PLUGIN['csrf_token'] == mib_hmac(mib_remote_address(), $MIB_USER['salt'])) {
		// On supprime l'utilisateur de la liste des connectés
		$query = array(
			'DELETE'	=> 'online',
			'WHERE'		=> 'user_id='.$MIB_USER['id']
		);
		$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

		// Update last_visit (assurons nous qu'il y a quelque chose à update)
		if (isset($MIB_USER['logged'])) {
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'last_visit='.$MIB_USER['logged'],
				'WHERE'		=> 'id='.$MIB_USER['id']
			);

			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		}
	}

	// Suppression du cookie d'autoconnexion
	$expire = time() + 1209600;
	mib_setcookie(COOKIE_NAME, base64_encode('1|'.mib_random_key(8, false, true).'|'.$expire.'|'.mib_random_key(8, false, true)), $expire);
}

$MIB_PLUGIN['json'] = array(
	'title'		=> __('Déconnexion'),
	'value'		=> __('Veuillez patienter pendant la déconnexion.'),
	'options'		=> array(
		'type' 		=> 'valid',
		'duration' 	=> -1
	),
	'location'		=> $MIB_PAGE['base_url'] // On redirige vers la page de login du BO
);