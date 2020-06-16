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
mib_confirm_current_url($MIB_PAGE['base_url'].'/json/bo/idle/'.$MIB_PLUGIN['user_id'].'/'.$MIB_PLUGIN['csrf_token']);

// Requette de Déconnexion
if(isset($_POST['logout'])) {
	// L'utilisateur est tjs logué
	if(!$MIB_USER['is_guest']) {
		// On a le bon id, email, et token
		if($MIB_PLUGIN['user_id'] == $MIB_USER['id'] && $MIB_PLUGIN['csrf_token'] == mib_hmac(mib_remote_address(), $MIB_USER['salt']) && utf8_strtolower(base64_decode(mib_trim($_POST['logout']))) == utf8_strtolower($MIB_USER['email'])) {
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

			// Suppression du cookie d'autoconnexion en gardant le timeout au cas ou la case "Se souvenir de moi à été coché"
			$cookie_data = @explode('|', base64_decode($_COOKIE[COOKIE_NAME]));

			$expire = ($cookie_data[2] > time() + $MIB_CONFIG['timeout_visit']) ? time() + 1209600 : time() + $MIB_CONFIG['timeout_visit'];
			mib_setcookie(COOKIE_NAME, base64_encode('1|'.mib_random_key(8, false, true).'|'.$expire.'|'.mib_random_key(8, false, true)), $expire);
		}
		else
			error(__('Un problème est survenu lors de l\'inactivitée.'));
	}

	// On ajoute l'opacité de l'overlay à 0.9
	$MIB_PLUGIN['json'] = array(
		'element'		=> array(
			'idle-box-overlay' => array(
				'morph' => array(
					'opacity'		=> '0.95'
				)
			)
		)
	);
}
// Connexion
else if(isset($_POST['login'])) {
	// L'utilisateur n'est pas logué
	if($MIB_USER['is_guest']) {
		$form_email = utf8_strtolower(base64_decode(mib_trim($_POST['login'])));
		$form_password = mib_trim($_POST['password']);
		$authorized = false;

		// Si l'adresse email est valide
		if(mib_valid_email($form_email)) {
			// Sélectionne les infos correspondant à l'email
			$query = array(
				'SELECT'	=> 'u.id, u.group_id, u.password, u.salt',
				'FROM'		=> 'users AS u'
			);

			if (DB_TYPE == 'mysql' || DB_TYPE == 'mysqli' || DB_TYPE == 'mysql_innodb' || DB_TYPE == 'mysqli_innodb')
				$query['WHERE'] = 'email=\''.$MIB_DB->escape($form_email).'\'';
			else
				$query['WHERE'] = 'LOWER(email)=LOWER(\''.$MIB_DB->escape($form_email).'\')';

			$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			list($user_id, $group_id, $db_password_hash, $salt) = $MIB_DB->fetch_row($result);

			if (!empty($db_password_hash)) {
				$form_password_hash = mib_hmac($form_password, $salt);

				// Le password correspond
				if ($db_password_hash == $form_password_hash) { 

					$authorized = true;

					// Supprime cet utilisateur "invité" de la "online list"
					$query = array(
						'DELETE'	=> 'online',
						'WHERE'		=> 'ident=\''.$MIB_DB->escape(mib_remote_address()).'\''
					);

					// regénère le cookie
					$cookie_data = @explode('|', base64_decode($_COOKIE[COOKIE_NAME]));

					$expire = ($cookie_data[2] > time() + $MIB_CONFIG['timeout_visit']) ? time() + 1209600 : time() + $MIB_CONFIG['timeout_visit'];
					mib_setcookie(COOKIE_NAME, base64_encode($user_id.'|'.$form_password_hash.'|'.$expire.'|'.sha1($salt.$form_password_hash.mib_hmac($expire, $salt))), $expire);

					$MIB_PLUGIN['json'] = array(
						'title'		=> __('Connexion effectuée'),
						'value'		=> __('Vous êtes maintenant reconnecté au Back Office.'),
						'options'		=> array('type' => 'valid'),
						'idle'		=> 'online',
						'element'		=> array(
							'idle-box' => array(
								'setStyles' => array(
									'visibility'		=> 'hidden',
									'opacity'		=> '0'
								),
								'erase'	=> 'id'
							),
							'idle-box-overlay' => array(
								'morph' => array(
									'opacity'		=> '0'
								)
							)
						)
					);

				}
			}
		}
	}
	else
		error(__('Un problème est survenu lors de l\'inactivitée.'));

	if (!$authorized) {
		$MIB_PLUGIN['json']['title'] = __('Erreur');
		$MIB_PLUGIN['json']['options']['type'] = 'error';
		$MIB_PLUGIN['json']['value'] = __('Mot de passe incorrect !');

		// Un petit sleep pour éviter les attaques de type "brutforce"
		if (!defined('MIB_DEBUG'))
			sleep(rand(1, 5));
	}
}
else
	error(__('Un problème est survenu lors de l\'inactivitée.'));