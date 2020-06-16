<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

/*
	EN:
	FR: Le driver de votre base de donnée
*/
define('DB_TYPE', 'YourDBdriver');

/*
	EN:
	FR:
*/
define('DB_HOST', 'YourDBhost');

/*
	EN:
	FR:
*/
define('DB_NAME', 'YourDBname');

/*
	EN:
	FR:
*/
define('DB_USERNAME', 'YourDBusername');

/*
	EN:
	FR:
*/
define('DB_PASSWORD', 'YourDBpassword');

/*
	EN:
	FR: préfixe de la base de donnée par defaut
*/
define('DB_PREFIX', 'mib_');



/*
	EN: Enable DEBUG mode
	FR: Active le mode DEBUG

	define('MIB_DEBUG', 1); // Active le mode débug : affiche les erreurs, log les erreurs dans le fichier website/debug.log, affiche la barre de débug
	define('MIB_DEBUG_DISPLAY', 0); // ne pas afficher les erreurs
	define('MIB_DEBUG_LOG', 0); // ne pas log les erreurs
*/



/*
	EN: The following configurations are optional. Use only if you know what you made.
	FR: Les configurations suivantes sont optionnelles. Utilisez lès uniquement si vous savez ce que vous faite.
*/

/*
	EN:
	FR: répertoire d'administration par défaut

	define('MIB_ADMIN_DIR', 'admin');
*/

/*
	EN:
	FR: répertoire des fichiers public

	define('MIB_PUBLIC_DIR', MIB_ROOT.'public/');
*/

/*
	EN:
	FR: répertoire des fichiers de cache

	define('MIB_CACHE_DIR', MIB_PATH_VAR.'cache/');
*/

/*
	EN: 
	FR: Active l'encodage des adresses emails pour limiter le spam

	define('MIB_ENCODE_EMAIL', 1);
*/

/*
	EN: 
	FR: Active la compression gzip

	define('MIB_GZIP_OUTPUT', 1);
*/

/*
	EN: 
	FR: Active la compression du code HTML

	define('MIB_COMPRESS_HTML', 1);
*/

/*
	EN: 
	FR: Active la compression du code JAVASCRIPT

	define('MIB_COMPRESS_JS', 1);
*/

/*
	EN: 
	FR: Active la compression du code CSS

	define('MIB_COMPRESS_CSS', 1);
*/

/*
	EN: 
	FR: Force l'url de base si le script n'arrive pas à la détecter correctement.

	define('MIB_BASE_URL', 'http://www.domain.com');
*/

/*
	EN: 
	FR: langue par défaut du système.

	define('MIB_LANG', 'YourISOlang');
*/

/*
	EN: 
	FR: nom du cookie par défaut pour prévenir des colisions de cookies

	define('COOKIE_NAME', 'YourCookieName');
*/

/*
	EN: 
	FR: path du cookie

	define('COOKIE_PATH', 'YourCookiePath');
*/

/*
	EN: 
	FR: domaine du cookie

	define('COOKIE_DOMAIN', 'YourCookieDomain');
*/

/*
	EN: 
	FR: sécurisation du cookie

	define('COOKIE_SECURE', 'YourCookieSecure');
*/

/*
	EN: 
	FR: phrase unique de sécurisation du cookie

	define('COOKIE_SEED', 'YourCookieSeed');
*/

/*
	EN: 
	FR: connexion persistante ?

	define('DB_P_CONNECT', true);
*/

/*
	EN: 
	FR: sommes nous derière un "reverse proxy" (proxy inverse)

	define('MIB_IS_BEHIND_REVERSE_PROXY', true);
*/



/*
	EN: DONT TOUCH THIS !
	FR: NE PAS TOUCHER !
*/
define('MIB_CONFIG', 1);
