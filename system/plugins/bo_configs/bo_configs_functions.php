<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

/**
 * Vérification des valeurs
 *
 * @param {array} $values
 * @param {string} $dbtable
 * @param {int} $id
 */
function bo_configs_verif($values) {
	global $MIB_CONFIG;

	$verif = array(
		'site_title'		=> !empty($values['site_title']) ? mib_clean($values['site_title']) : '',
		'site_email'		=> !empty($values['site_email']) ? strtolower(mib_clean($values['site_email'])) : '',
		'bo_color'			=> !empty($values['bo_color']) ? mib_clean($values['bo_color']) : '',
		'server_timezone'	=> !empty($values['server_timezone']) ? mib_clean($values['server_timezone']) : '',
		'timeout_visit'		=> !empty($values['timeout_visit']) ? intval($values['timeout_visit']) : '',
		'timeout_online'	=> !empty($values['timeout_online']) ? intval($values['timeout_online']) : '',
	);

	if ( utf8_strlen($verif['site_title']) > 50 )
		mib_error_set(__bo('Le titre est trop long.'), 'site_title');
	else if ( empty($verif['site_title']) )
		mib_error_set(__bo('Veuillez indiquer un titre.'), 'site_title');

	if ( !mib_valid_email($verif['site_email']) )
		mib_error_set(__bo('L\'adresse e-mail du site est invalide.'), 'site_email');

	if ( !empty($verif['bo_color']) ) {
		$verif['bo_color'] = preg_replace("/[^0-9A-Fa-f]/", '', $verif['bo_color']);
		if ( strlen($verif['bo_color']) == 3 )
			$verif['bo_color'] = $verif['bo_color'][0].$verif['bo_color'][0].$verif['bo_color'][1].$verif['bo_color'][1].$verif['bo_color'][2].$verif['bo_color'][2];
		else if ( strlen($verif['bo_color']) != 6 )
			mib_error_set(__bo('La couleur est invalide.'), 'bo_color');
	}

	if ( empty($verif['server_timezone']) )
		mib_error_set(__bo('Veuillez indiquer un fuseau horaire.'), 'server_timezone');
	else if ( !mib_multi_array_key_exists($verif['server_timezone'],mib_date_timezones()) )
		mib_error_set(__bo('Le fuseau horaire est invalide.'), 'server_timezone');

	if ( $verif['timeout_online'] >= $verif['timeout_visit'] )
		mib_error_set(__bo('La valeur de "Temps mort en ligne" doit être inférieure que la valeur de "Temps mort de visite"'), 'timeout_online');

	return $verif;
}


/**
 * Update
 * 
 * @param {array} $values
 * @param {string} $dbtable
 */
function bo_configs_update($values, $dbtable) {
	global $MIB_CONFIG, $MIB_DB;

	// vérification des données
	$verifed = bo_configs_verif($values);

	if ( !mib_error_exists() ) { // aucune erreur

		foreach( $verifed as $k => $v ) {
			// mise à jours
			if ( array_key_exists($k, $MIB_CONFIG) ) {
				if ( $MIB_CONFIG[$k] != $v ) { // met a jour seulement si il y a un changement
					$query = array(
						'UPDATE'	=> $dbtable,
						'SET'		=> 'conf_value='.($v != '' || is_int($v) ? '\''.$MIB_DB->escape($v).'\'' : 'NULL'),
						'WHERE'		=> 'conf_name=\''.$MIB_DB->escape($k).'\' AND conf_type=\'system\'',
					);
					$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
				}
			}
			// insert
			else {
				$query = array(
					'INSERT'	=> 'conf_type,conf_name,conf_value',
					'INTO'		=> $dbtable,
					'VALUES'	=> '\'system\',\''.$MIB_DB->escape($k).'\','.($v != '' || is_int($v) ? '\''.$MIB_DB->escape($v).'\'' : 'NULL')
				);
				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			}
		}

		return $verifed;
	}

	return false;
}