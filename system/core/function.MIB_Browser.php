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
 * Permet de détecté le browser utilisé
 * 
 * @param string $return Valeur de retour
 *
 * @return string|array
 */
function MIB_Browser($return = false) {
	global $MIB_Browser;

	if($return)
		$return = utf8_strtolower($return);

	// Navigateur connu
	if($return && $return == 'browsers') {
		return array(
			'firefox'		=> 'browser',
			'shiretoko'		=> 'browser', // nom de code de firefox 3.5 
			'msie'			=> 'browser',
			'opera'			=> 'browser',
			'chrome'		=> 'browser',
			'safari'		=> 'browser',
			'mozilla'		=> 'browser',
			'seamonkey'		=> 'browser',
			'konqueror'		=> 'browser',
			'netscape'		=> 'browser',
			'gecko'			=> 'browser',
			'navigator'		=> 'browser',
			'mosaic'		=> 'browser',
			'lynx'			=> 'browser',
			'amaya'			=> 'browser',
			'omniweb'		=> 'browser',
			'avant'			=> 'browser',
			'camino'		=> 'browser',
			'flock'			=> 'browser',
			'aol'			=> 'browser',

			'googlebot'		=> 'spider',
			'msnbot'		=> 'spider',
			'yahoo'			=> 'spider',
			'ask'			=> 'spider',

			'itunes'		=> 'other',
		);
	}
	// Plateforme connue
	else if($return && $return == 'platforms') {
		return array(
			'android'		=> array('android'),
			'freebsd'		=> array('freebsd'),
			'linux'			=> array('linux'),
			'iphone'		=> array('iphone'),
			'zune'			=> array('zune'),
			'mac'			=> array('macintosh', 'mac platform x', 'mac os x'),
			'win'			=> array('windows', 'win32'),
		);
	}

	if(!isset($MIB_Browser)) {
		$MIB_Browser = array(
			'name'			=> false,
			'version'		=> 0,
			'platform'		=> false,
			'userAgent'		=> $_SERVER['HTTP_USER_AGENT'] 
		);

		// Navigateur + Version
		foreach(MIB_Browser('browsers') as $browser => $type) {
			if (preg_match("#($browser)[/ ]?([0-9.]*)#", strtolower($MIB_Browser['userAgent']), $match)) {
				$MIB_Browser['name'] = $match[1];
				// Hack safari
				if($MIB_Browser['name'] == 'safari' && preg_match("#version[/ ]?([0-9.]*)#", strtolower($MIB_Browser['userAgent']), $match)) {
					$MIB_Browser['version'] = $match[1];
				}
				else
					$MIB_Browser['version'] = $match[2];

				if($type == 'spider')
					$MIB_Browser['platform'] = $type;

				break;
			}
		}

		// OS
		if(!$MIB_Browser['platform']) {
			foreach(MIB_Browser('platforms') as $platform => $values) {
				if(!$MIB_Browser['platform']) {
					foreach($values as $value) {
						if (strpos(strtolower($MIB_Browser['userAgent']), $value)) {
							$MIB_Browser['platform'] = $platform;
							break;
						}
					}
				}
				else
					break;
			}
		}
	}

	if($return && isset($MIB_Browser[$return]))
		return $MIB_Browser[$return];
	else if(!$return)
		return $MIB_Browser;
	else
		return false;
}