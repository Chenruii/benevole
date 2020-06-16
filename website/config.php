<?php

// Configuration de la base de données
//define('DB_TYPE', 'mysqli');
//define('DB_HOST', 'xxxxxxxxxxx');
//define('DB_NAME', 'xxxxxxxxxx');
//define('DB_USERNAME', 'xxxxxxxx');
//define('DB_PASSWORD', 'xxxxxxxxxxx');
//define('DB_PREFIX', 'mib_');


define('DB_TYPE', 'mysqli');
define('DB_HOST', 'localhost');
define('DB_NAME', 'benevole');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_PREFIX', 'mib_');


// Sécuritée du cookie d'authentification
define('COOKIE_NAME', '93ec66');
define('COOKIE_SEED', '4303bf84c91cbbcc464c944ce8f27998');


// Thème par défaut
define('MIB_THEME', 'website');
define('MIB_USE_FORM', true);


// DEBUG : N'ACTIVEZ PAS cela sur un environnement de production !
define('MIB_DEBUG', 0);
define('MIB_DEBUG_DISPLAY', 0);
define('MIB_DEBUG_LOG', 1);


define('MIB_CONFIG', 1);