<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit;

mib_headers_no_cache(); // Envoi des header (no-cache)
header('Content-type: text/xml; charset=utf-8'); // Envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose

// Header du sitemap
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<?xml-stylesheet type="text/xsl" href="'.$MIB_CONFIG['base_url'].'/sitemap.xsl"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

foreach ($MIB_URL as $cur_url => $cur_info) {
	if(floatval($cur_info['sitemap_priority']) > 0 && current(explode(',', $cur_info['meta_robots'])) == 'index' && !empty($cur_info['url_rewrited'])) {
		if($cur_info['url_rewrited'] == '/')
			$cur_info['url_sitemap'] = '';
		else if(array_key_exists($cur_info['url_rewrited'], $MIB_CONFIG['languages']))
			$cur_info['url_sitemap'] = $cur_info['url_rewrited'];
		else {
			$cur_info['url_sitemap'] = current(explode('/', $cur_info['url'])).'/'; // Affiche la langue
			// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
			if(count(explode('/', $cur_info['url'])) > 2) {
				foreach ($MIB_URL as $url => $info) {
					if($info['url'] != $cur_info['url'] && count(explode('/', $info['url'])) == 2 && strpos($cur_info['url'].'/', $info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
						$cur_info['url_sitemap'] .= $info['url_rewrited'] .'/';
						break;
					}
				}
			}
			$cur_info['url_sitemap'] .= $cur_info['url_rewrited'];
		}

		echo "\t".'<url>'."\n";
		echo "\t\t".'<loc>'.$MIB_CONFIG['base_url'].'/'.$cur_info['url_sitemap'].'</loc>'."\n";
		echo "\t\t".'<priority>'.$cur_info['sitemap_priority'].'</priority>'."\n";
		echo "\t".'</url>'."\n";
	}
}

echo '</urlset>';

exit;