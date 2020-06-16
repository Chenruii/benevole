<?php
/*
Script:
	Rendu du fichier robots

Author:
	Jonathan OCHEJ, <jonathan.ochej@gmail.com>

Copyright (C):
	2010-2015 2BO&CO. This file is part of Mibbo.
*/

// Assurons nous que le script n'est pas executé "directement"
if (!defined('MIB'))
	exit;

mib_headers_no_cache(); // Envoi des header (no-cache)
header('Content-type: text/plain; charset=utf-8'); // Envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose

echo 'User-agent: *'."\n";
echo 'Sitemap: '.$MIB_CONFIG['base_url'].'/sitemap.xml';

echo "\n".'Disallow: /css/';
echo "\n".'Disallow: /js/';
echo "\n".'Disallow: /theme/';
echo "\n".'Disallow: /'.MIB_URL_SYS.'/';
echo "\n".'Disallow: /'.MIB_URL_VAR.'/';

foreach ($MIB_URL as $cur_url => $cur_info) {
	// Ne pas indexer cette URL
	if(strpos($cur_info['meta_robots'], 'noindex') !== false && !empty($cur_info['url_rewrited'])) {
		if($cur_info['url_rewrited'] == '/')
			$cur_info['url_robots'] = '';
		else if(array_key_exists($cur_info['url_rewrited'], $MIB_CONFIG['languages']))
			$cur_info['url_robots'] = $cur_info['url_rewrited'];
		else {
			$cur_info['url_robots'] = current(explode('/', $cur_info['url'])).'/'; // Affiche la langue
			// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
			if(count(explode('/', $cur_info['url'])) > 2) {
				foreach ($MIB_URL as $url => $info) {
					if($info['url'] != $cur_info['url'] && count(explode('/', $info['url'])) == 2 && strpos($cur_info['url'].'/', $info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
						$cur_info['url_robots'] .= $info['url_rewrited'] .'/';
						break;
					}
				}
			}
			$cur_info['url_robots'] .= $cur_info['url_rewrited'];
		}

		echo "\n".'Disallow: /'.$cur_info['url_robots'];
	}
}

exit;