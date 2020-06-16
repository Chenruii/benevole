<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB_ROOT') or exit;

/**
 * Est ce que le fichier / Dossier de cache est disponible en écriture
 * 
 * 
 *
 * @param string $location Fichier de destination
 */
function mib_cache_is_writable($location = false) {
	if(!$location)
		$location = MIB_CACHE_DIR;
	
	if(!@is_writable($location))
		mib_error(sprintf(__('Impossible d\'écrire dans le répertoire cache. Merci de vérifier si PHP à accès en écriture dans le répertoire %s'), '<code>'.MIB_CACHE_DIR.'</code>'));
	else
		return true;
}


/**
 * Extrait la langue d'un fichier .po pour en générer un array php dans le cache
 * 
 * 
 *
 * @param string $po_file Fichier PO
 * @param string $php_file Ficher Cache
 * @param string $define_file Variable utilisé pour define()
 * @param bool $reverse
 */
function mib_generate_lang_cache($po_file, $php_file, $define_file, $reverse = false) {

	if (!file_exists($po_file) || empty($php_file) || empty($define_file))
		return false;

	$fc = implode('',file($po_file)); // Charge le fichier .po dans un tableau
	
	$res = array();
	
	$matched = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+'.
	'(msgstr\s+("([^"]|\\\\")*?(?<!\\\)"\s*)+)/',
	$fc, $matches);

	if (!$matched)
		return false;

	if ($reverse) {
		$smap = array('"', "\n", "\t", "\r");
		$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
	} else {
		$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\"/');
		$rmap = array('', "\n", "\r", "\t", '"');
	}

	for ($i=0; $i<$matched; $i++) {
		$msgid = preg_replace('/\s*msgid\s*"(.*)"\s*/s','\\1',$matches[1][$i]);
		$msgstr= preg_replace('/\s*msgstr\s*"(.*)"\s*/s','\\1',$matches[4][$i]);
		
		if ($reverse) {
			$msgstr = trim((string) str_replace($smap, $rmap, $msgstr));
			if($msgstr)
				$msgid = trim((string) str_replace($smap, $rmap, $msgid));
		}
		else {
			$msgstr = trim((string) preg_replace($smap, $rmap, $msgstr));
			if($msgstr)
				$msgid = trim((string) preg_replace($smap, $rmap, $msgid));
		}
		
		if($msgstr)
			$res[$msgid] = $msgstr;
	}

	if (!empty($res[''])) {
		$meta = $res[''];
		unset($res['']);
	}

	if(mib_cache_is_writable()) {
		$fh = @fopen($php_file, 'wb');
		if (!$fh)
			mib_error(sprintf(__('Impossible d\'écrire le fichier de cache %1$s dans le répertoire cache. Merci de vérifier si PHP à accès en écriture dans le répertoire %2$s'), '<code>'.$php_file.'</code>', '<code>'.MIB_CACHE_DIR.'</code>'));

		fwrite($fh, '<?php'."\n\n".'$MIB_LANG = array_merge($MIB_LANG, '.var_export($res, true).');'."\n\n".'if (!defined(\''.$define_file.'\')) define(\''.$define_file.'\', 1);'."\n\n".'?>');
	}
}


/**
 * Génération du cache de configuration system et website du site
 * 
 * @uses $MIB_DB
 */
function mib_generate_configs_cache() {
	global $MIB_DB;

	// Réception des configurations du site depuis la DB
	$query = array(
		'SELECT'	=> 'c.conf_name, c.conf_value',
		'FROM'		=> 'configs AS c',
		'WHERE'		=> 'conf_type=\'system\' OR conf_type=\'website\''
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$output = array();
	while ($cur_config_item = $MIB_DB->fetch_row($result))
		$output[$cur_config_item[0]] = $cur_config_item[1];

	$fh = @fopen(MIB_CACHE_DIR.'cache_configs.php', 'wb');
	if (!$fh)
		error(sprintf(__('Impossible d\'écrire le fichier de cache %1$s dans le répertoire cache. Merci de vérifier si PHP à accès en écriture dans le répertoire %2$s'), '<code>cache_configs.php</code>', '<code>'.MIB_CACHE_DIR.'</code>'), __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'$MIB_CONFIG = '.var_export($output, true).';'."\n\n".'define(\'MIB_LOADED_CONFIGS\', 1);'."\n\n".'?>');

	fclose($fh);
}


/**
 * Génération du cache de l'optimisation des urls
 * 
 * @uses $MIB_DB
 */
function mib_generate_urls_cache() {
	global $MIB_DB;

	// Réception de la liste des bans du site depuis la DB
	$query = array(
		'SELECT'	=> 'u.*',
		'FROM'		=> 'urls AS u',
		'ORDER BY'	=> 'u.url DESC',
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	// Tableau avec url en key
	$output_url = array();
	while ($cur_url = $MIB_DB->fetch_assoc($result))
		$output_url[$cur_url['url']] = $cur_url;

	// Tableau avec url_rewrited en key
	$output_url_rewrited = array();
	foreach($output_url as $o_url => $o_info) {
		// Ne prend pas en compte les URL de base
		if($o_info['url'] != '/' && !array_key_exists($o_info['url'], mib_locale_languages_list())) {

			$o_info['url_rewrited_full'] = current(explode('/', $o_info['url'])).'/'; // Ajoute la langue

			// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
			if(count(explode('/', $o_info['url'])) > 2) {
				foreach ($output_url as $r_url => $r_info) {
					if(count(explode('/', $r_info['url'])) == 2 && strpos($o_info['url'], $r_info['url']) === 0) { // Rubrique uniquement + Présente dans l'URL
						$o_info['url_rewrited_full'] .= $r_info['url_rewrited'].'/';
						break;
					}
				}
			}

			$o_info['url_rewrited_full'] .= $o_info['url_rewrited'];
			$o_info['url_rewrited_full'] = mib_trim($o_info['url_rewrited_full'], '/');

			$output_url_rewrited[$o_info['url_rewrited_full']] = $o_info['url'];
		}
	}

	$fh = @fopen(MIB_CACHE_DIR.'cache_urls.php', 'wb');
	if (!$fh)
		error(sprintf(__('Impossible d\'écrire le fichier de cache %1$s dans le répertoire cache. Merci de vérifier si PHP à accès en écriture dans le répertoire %2$s'), '<code>cache_urls.php</code>', '<code>'.MIB_CACHE_DIR.'</code>'), __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'$MIB_URL_REWRITED = '.var_export($output_url_rewrited, true).';'."\n\n".'$MIB_URL = '.var_export($output_url, true).';'."\n\n".'define(\'MIB_LOADED_URLS\', 1);'."\n\n".'?>');

	fclose($fh);
}


/**
 * Génération du cache de configuration des plugins
 * 
 * 
 * 
 * @uses $MIB_DB
 */
function mib_generate_plugins_cache() {
	global $MIB_DB;

	// Réception des configurations des plugins du site depuis la DB
	$query = array(
		'SELECT'	=> 'c.conf_ref, c.conf_name, c.conf_value',
		'FROM'		=> 'configs AS c',
		'WHERE'		=> 'conf_type=\'plugin\'',
		'ORDER BY'	=> 'c.conf_ref'
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$output = array();
	while ($cur_config_plugin = $MIB_DB->fetch_assoc($result)) {
		$output[$cur_config_plugin['conf_ref']][$cur_config_plugin['conf_name']] = $cur_config_plugin['conf_value'];
	}

	$fh = @fopen(MIB_CACHE_DIR.'cache_plugins.php', 'wb');
	if (!$fh)
		error(sprintf(__('Impossible d\'écrire le fichier de cache %1$s dans le répertoire cache. Merci de vérifier si PHP à accès en écriture dans le répertoire %2$s'), '<code>cache_plugins.php</code>', '<code>'.MIB_CACHE_DIR.'</code>'), __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'$MIB_PLUGINS = '.var_export($output, true).';'."\n\n".'define(\'MIB_LOADED_PLUGINS\', 1);'."\n\n".'?>');

	fclose($fh);
}

define('MIB_LOADED_CACHE_FUNCTIONS', 1);