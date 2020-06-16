<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/03/2019
 * Time: 15:48
 */

class MibboEngine
{

    public function initPage()
    {

        global $MIB_PAGE;


        // Le header et le footer sont stockés dans un tableau
        $MIB_PAGE['footer'] = $MIB_PAGE['header'] = array();
        // Theme en cours d'utilisation
        $MIB_PAGE['cur_theme'] = MIB_THEME;

        // Enregistre l'heure de début d'execution du script (sera utilisé pour calculer le temps qu'une page a mis pour être générée)
        list($usec, $sec) = explode(' ', microtime());
        $MIB_PAGE['start'] = ((float)$usec + (float)$sec);

        $MIB_PAGE['uniqid'] = 'MIB' . md5(uniqid(rand(), true));
        $MIB_PAGE['time'] = time();
        $MIB_PAGE['language_packs'] = mib_locale_languages_list();

        // Détermine le path du script, car on doit séparer le path des données rewrités
        $MIB_PAGE['path_to_script'] = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if (substr($MIB_PAGE['path_to_script'], -1) != '/')
            $MIB_PAGE['path_to_script'] = $MIB_PAGE['path_to_script'] . '/';

        // Création de notre propre REQUEST_URI avec le path supprimé et seulement les parties rewrités inclues
        $MIB_PAGE['uri']['request'] = substr(urldecode($_SERVER['REQUEST_URI']), strlen($MIB_PAGE['path_to_script']));
        if (strpos($MIB_PAGE['uri']['request'], '?') !== false)
            $MIB_PAGE['uri']['request'] = substr($MIB_PAGE['uri']['request'], 0, strpos($MIB_PAGE['uri']['request'], '?'));

        // Enlève le / à la fin et au début de $MIB_PAGE['uri']['request'] si il y en a un
        $MIB_PAGE['uri']['request'] = mib_trim($MIB_PAGE['uri']['request'], '/');

        // Sépare les infos de $MIB_PAGE['uri']['request']
        @list($MIB_PAGE['uri']['lang'], $MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['request'], 3);

        // Si il n'y a pas de langue valable indiqué
        if (!array_key_exists(strtolower($MIB_PAGE['uri']['lang']), $MIB_PAGE['language_packs'])) {
            // if ( !mib_core_function('MIB_Lang', $MIB_PAGE['uri']['lang'])) {
            @list($MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['request'], 2);
            unset($MIB_PAGE['uri']['lang']);
        }


        // Si c'est une requette admin (on réatribut la langue et la rubrique)
        if (defined('MIB_ADMIN_DIR') && $MIB_PAGE['uri']['rub'] == MIB_ADMIN_DIR) {
            define('MIB_MANAGE', 1);

            $MIB_PAGE['manage'] = MIB_ADMIN_DIR;
            $MIB_PAGE['uri']['request_admin'] = $MIB_PAGE['uri']['infos'];
            // Réatribut les infos car $MIB_PAGE['uri']['rub']=MIB_ADMIN_DIR
            if (isset($MIB_PAGE['uri']['lang'])) // Si il y a déjà une langue valide
                @list($MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['request_admin'], 2);
            else {
                @list($MIB_PAGE['uri']['lang'], $MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['request_admin'], 3);

                // Si il n'y a pas de langue valable indiqué
                if (!array_key_exists(strtolower($MIB_PAGE['uri']['lang']), $MIB_PAGE['language_packs'])) {
                    //if ( !mib_core_function('MIB_Lang', $MIB_PAGE['uri']['lang'])) {
                    @list($MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['request_admin'], 2);
                    unset($MIB_PAGE['uri']['lang']);
                }
            }
        }

        // Si aucune langue valide, on charge celle du navigateur
        if (empty($MIB_PAGE['uri']['lang']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $MIB_PAGE['lang_brower'] = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $MIB_PAGE['lang_brower'] = strtolower(substr(chop($MIB_PAGE['lang_brower'][0]), 0, 2));

            if (array_key_exists($MIB_PAGE['lang_brower'], $MIB_PAGE['language_packs']))
                $MIB_PAGE['uri']['lang'] = $MIB_PAGE['lang_brower'];
        }

        if (empty($MIB_PAGE['uri']['lang']))
            $MIB_PAGE['uri']['lang'] = strtolower(MIB_LANG); // langage par defaut

        // Charge la langue du système détectée (url ou navigateur)
        mib_load_locale('system', $MIB_PAGE['uri']['lang']);

    }

    public function initCore()
    {

        global $MIB_CONFIG;

        // charge le fichier de configuration
        if (file_exists(MIB_PATH_VAR . 'config.php'))
            include MIB_PATH_VAR . 'config.php';

        // le fichier de configuration est manquant ou corrompu
        if (!defined('MIB_CONFIG'))
            define('MIB_INSTALL', 1);

        // initialisation de Mibbo
        require_once MIB_PATH_SYS . 'functions.php';

        // bloque les requete de type "prefetch"
        if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch') {
            header('HTTP/1.1 403 Prefetching Forbidden');
            // envoi des headers (no-cache)
            mib_headers_no_cache();
            exit;
        }


        // mémoire atribuée au système
        if (!defined('MIB_MEMORY_LIMIT'))
            define('MIB_MEMORY_LIMIT', '128M');

        if (function_exists('memory_get_usage') && ((int)@ini_get('memory_limit') < abs(intval(MIB_MEMORY_LIMIT))))
            @ini_set('memory_limit', MIB_MEMORY_LIMIT);

        // supprime l'effet de register_globals
        mib_unregister_GLOBALS();

        // désactive magic_quotes_runtime
        //if ( get_magic_quotes_runtime() )
        //	set_magic_quotes_runtime(0);


        // strip slashes de GET/POST/COOKIE/REQUEST/FILES (si magic_quotes_gpc est actif)
        if ((function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != 'off'))) {
            function stripslashes_array($array)
            {
                return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
            }

            $_GET = stripslashes_array($_GET);
            $_POST = stripslashes_array($_POST);
            $_COOKIE = stripslashes_array($_COOKIE);
            $_REQUEST = stripslashes_array($_REQUEST);
            $_FILES = stripslashes_array($_FILES);
        }

        if (!isset($_SERVER['HTTP_REFERER']))
            $_SERVER['HTTP_REFERER'] = '';

        if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1'))
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

        // fixe REQUEST_URI si on peu, depuis que IIS6 et IIS7 ne l'implémente pas bien
        if (!isset($_SERVER['REQUEST_URI']) || (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['REQUEST_URI'], '?') === false)) {

            if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) // IIS Mod-Rewrite
                $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
            else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS Isapi_Rewrite
                $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
            else {
                // On utilise ORIG_PATH_INFO s'il n'y a pas PATH_INFO.
                if (!isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO']))
                    $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];

                // Certaines configurations IIS + PHP insère le script-name dans le path-info
                if (isset($_SERVER['PATH_INFO'])) {
                    if ($_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'])
                        $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
                    else
                        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
                }

                // Ajoute le query string s'il existe et n'est pas nul.
                if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        // lighttpd's 404 handler ne génere aucun query string, nous devons donc en créer un et l'implémenter dans $_GET
        if ((!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])) && strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI']);
            $_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']['query']) ? $_SERVER['QUERY_STRING']['query'] : '';
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        // bonne version de PHP ?
        $minVersion = '7.0';
        if (!function_exists('version_compare') || version_compare(PHP_VERSION, $minVersion, '<'))
            exit(sprintf('[EN] You are running %1$s. %2$s requires at least version %3$s to run properly.<br><br>[FR] Votre serveur utilise actuellement %1$s. %2$s a besoin de la version %3$s au minimum pour fonctionner.', 'PHP ' . PHP_VERSION, MIB_SYS_NAME, $minVersion));

    }

    public function initConstant()
    {
        if (!defined('MIB_BASE_URL'))
            define('MIB_BASE_URL', mib_get_base_url());

        // langue par défaut dans le code source principal
        define('MIB_LANG_SOURCE', 'fr');

        // langue par défaut du système
        if (!defined('MIB_LANG'))
            define('MIB_LANG', MIB_LANG_SOURCE);

        // Validation de la langue par défaut
        if (!array_key_exists(MIB_LANG, mib_locale_languages_list()))
            mib_error(mib_sprintftpl('La langue par défaut ne peut pas être [[%lang_iso%]] car le pack de langue correspondant est manquant.', array('lang_iso' => '<code>' . MIB_LANG . '</code>')));

        // nom du cookie par défaut
        if (!defined('COOKIE_NAME'))
            define('COOKIE_NAME', '');

        // path du cookie
        if (!defined('COOKIE_PATH'))
            define('COOKIE_PATH', '/');

        // domaine du cookie
        if (!defined('COOKIE_DOMAIN'))
            define('COOKIE_DOMAIN', '');

        // sécurisation du cookie
        if (!defined('COOKIE_SECURE'))
            define('COOKIE_SECURE', 0);

        // phrase unique de sécurisation du cookie
        if (!defined('COOKIE_SEED'))
            define('COOKIE_SEED', 'Mibbo Default Cookie Seed !');

        // préfixe de la base de donnée par defaut
        if (!defined('DB_PREFIX'))
            define('DB_PREFIX', 'mib_');

        // connexion persistante ?
        if (!defined('DB_P_CONNECT'))
            define('DB_P_CONNECT', false);

        // répertoire d'administration par défaut de Mibbo
        if (!defined('MIB_ADMIN_DIR'))
            define('MIB_ADMIN_DIR', 'admin');

        // définit plusieurs constantes très pratiques concernant l'id des groupes qui sont fixes
        define('MIB_G_UNVERIFIED', 0);
        define('MIB_G_ADMIN', 1);
        define('MIB_G_GUEST', 2);

        // répertoire public
        if (!defined('MIB_PUBLIC_DIR'))
            define('MIB_PUBLIC_DIR', MIB_ROOT . 'public/');

        // répertoire public de cache
        if (!defined('MIB_PUBLIC_CACHE_DIR'))
            define('MIB_PUBLIC_CACHE_DIR', MIB_PUBLIC_DIR . 'cache/');

        // répertoire de cache
        if (!defined('MIB_CACHE_DIR'))
            define('MIB_CACHE_DIR', MIB_PATH_VAR . 'cache/');

        // nom du theme par defaut
        if (!defined('MIB_THEME_DEFAULT'))
            define('MIB_THEME_DEFAULT', 'default');

        // répertoire du theme par défaut
        if (!defined('MIB_THEME_DEFAULT_DIR'))
            define('MIB_THEME_DEFAULT_DIR', MIB_PATH_VAR . 'themes/' . MIB_THEME_DEFAULT . '/');

        // le répertoire du theme par défaut n'héxiste pas. (obligatoire)
        if (!file_exists(MIB_THEME_DEFAULT_DIR))
            mib_error('Le thème par défaut est manquant. Même si vous utilisez votre propre thème, la présence du thème par défaut est obligatoire.');

        // nom du theme à utiliser
        if (!defined('MIB_THEME'))
            define('MIB_THEME', MIB_THEME_DEFAULT);

        // le theme à utiliser n'héxiste pas
        if (!file_exists(MIB_PATH_VAR . 'themes/' . str_replace('/', '', MIB_THEME) . '/'))
            mib_error('Le répertoire <code>' . MIB_THEME . '</code> corespondant au thème indiqué dans le fichier de configuration est manquant.');

        // répertoire du theme à utiliser
        if (!defined('MIB_THEME_DIR'))
            define('MIB_THEME_DIR', MIB_PATH_VAR . 'themes/' . str_replace('/', '', MIB_THEME) . '/'); // theme du site

        // Active le buffering
        if (defined('MIB_GZIP_OUTPUT')) {
            // Pour une raison très bizarre, "Norton Internet Security" supprime ceci
            $_SERVER['HTTP_ACCEPT_ENCODING'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

            // Pouvons nous utiliser la conmpression gzip?
            if (extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
                ob_start('ob_gzhandler');
            else
                ob_start();
        }
    }

    public function install()
    {
        if (file_exists(MIB_PATH_VAR . 'install.php'))
            include MIB_PATH_VAR . 'install.php';

        // si on arrive jusqu'ici c'est qu'il y a une erreur, ou que le fichier install.php  n'éxiste pas
        mib_error('Le fichier <code>config.php</code> n\'existe pas ou est corrompu. Ce fichier est nécessaire pour utiliser Mibbo.');
    }

    public function initDb()
    {

        global $MIB_DB;
        // Chargement DB requètes et connections
        if (mib_core_class('MIB_DbLayerX')) {
            // On créer un nouvel objet
            $MIB_DB = new MIB_DbLayerX(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PREFIX, DB_P_CONNECT);
        } else
            mib_error(__('Impossible de charger le layer de la base de donnée. Veuillez vérifier qu\'il est bien installé.'));

        // On commence une transaction
        $MIB_DB->start_transaction();
    }

    public function initCache()
    {
        global $MIB_PAGE, $MIB_CONFIG, $MIB_URL_REWRITED, $MIB_LANG, $MIB_URL;

        // Génération automatique des fichiers caches, si le mode debug est activé
        if (defined('MIB_DEBUG')) {
            if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS . 'cache.php'; // Si les fonctions de cache n'ont pas été chargées
            mib_generate_configs_cache();
            mib_generate_urls_cache();
            mib_generate_plugins_cache();
        }

        // On charge les configs en cache
        if (file_exists(MIB_CACHE_DIR . 'cache_configs.php'))
            @include MIB_CACHE_DIR . 'cache_configs.php';
        if (!defined('MIB_LOADED_CONFIGS')) {
            if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS . 'cache.php'; // Si les fonctions de cache n'ont pas été chargées
            mib_generate_configs_cache();
            require MIB_CACHE_DIR . 'cache_configs.php';
        }
        $MIB_CONFIG['base_url'] = MIB_BASE_URL;
        $MIB_CONFIG['time'] = $MIB_PAGE['time']; // Pour ne pas avoir à charger global $MIB_PAGE si on a déjà charger global $MIB_CONFIG juste pour avoir time();
        $MIB_CONFIG['languages'] = mib_locale_languages_list(); // Langages disponibles pour le site
        $MIB_CONFIG['like_command'] = (DB_TYPE == 'pgsql') ? 'ILIKE' : 'LIKE'; // Commande d'une requette en db de type "LIKE"

        // met à jour le timezone
        mib_date_timezone_set($MIB_CONFIG['server_timezone']);

        // On charge les urls en cache
        if (file_exists(MIB_CACHE_DIR . 'cache_urls.php'))
            @include MIB_CACHE_DIR . 'cache_urls.php';
        if (!defined('MIB_LOADED_URLS')) {
            if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS . 'cache.php'; // Si les fonctions de cache n'ont pas été chargées
            mib_generate_urls_cache();
            require MIB_CACHE_DIR . 'cache_urls.php';
        }
        if (!empty($MIB_URL['/']['page_title']))
            $MIB_CONFIG['site_title'] = $MIB_URL['/']['page_title'];

        // On charge les plugins en cache
        if (file_exists(MIB_CACHE_DIR . 'cache_plugins.php'))
            @include MIB_CACHE_DIR . 'cache_plugins.php';
        if (!defined('MIB_LOADED_PLUGINS')) {
            if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS . 'cache.php'; // Si les fonctions de cache n'ont pas été chargées
            mib_generate_plugins_cache();
            require MIB_CACHE_DIR . 'cache_plugins.php';
        }

    }

    public function preProcessSeoRequest()
    {
        global $MIB_PAGE;
        // Fichier SITEMAP
        if (strtolower($MIB_PAGE['uri']['rub']) == 'sitemap.xml') {
            if (file_exists(MIB_PATH_VAR . 'sitemap.php'))
                include MIB_PATH_VAR . 'sitemap.php';
        }

        // Fichier ROBOTS
        if (strtolower($MIB_PAGE['uri']['rub']) == 'robots.txt' && file_exists(MIB_PATH_SYS . 'robots.php'))
            include MIB_PATH_SYS . 'robots.php';
    }

    public function preProcessRequest()
    {

        global $MIB_PAGE;

        // Requette AJAX (on réatribut la rubrique)
        if (strtolower($MIB_PAGE['uri']['rub']) == 'ajax') {
            define('MIB_AJAX', 1);
            @list($MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['infos'], 2);
        }

        // Requette JSON (on réatribut la rubrique)
        if (strtolower($MIB_PAGE['uri']['rub']) == 'json') {
            define('MIB_JSON', 1);
            @list($MIB_PAGE['uri']['rub'], $MIB_PAGE['uri']['infos']) = explode('/', $MIB_PAGE['uri']['infos'], 2);
        }

        // Requette CSS (on réatribut la rubrique)
        if (strtolower($MIB_PAGE['uri']['rub']) == 'css')
            define('MIB_CSS', 1);

        // Requette CSS (on réatribut la rubrique)
        if (strtolower($MIB_PAGE['uri']['rub']) == 'libs')
            define('MIB_LIBS', 1);

        // Requette JS
        if (strtolower($MIB_PAGE['uri']['rub']) == 'js')
            define('MIB_JS', 1);

        // Requette IMG
        if (strtolower($MIB_PAGE['uri']['rub']) == 'img')
            define('MIB_IMG', 1);
    }

    public function processRewrite()
    {

        global $MIB_PAGE, $MIB_URL_REWRITED, $MIB_CONFIG, $MIB_USER;
        // redirection 301 si c'est une url rewrité et qu'on essaye d'y accéder sans le rewrite
        if (!empty($MIB_PAGE['uri']['rub'])) {
            $MIB_PAGE['uri']['rewrite'] = $MIB_PAGE['uri']['request'];
            // ajoute la langue de base à l'url si elle est manquante
            if ($MIB_PAGE['uri']['lang'] == strtolower(MIB_LANG) && strpos($MIB_PAGE['uri']['rewrite'], $MIB_PAGE['uri']['lang'] . '/') !== 0) {

                // redirige uniquement si ce n'est pas une image, ou un iso de langue
                if ($MIB_PAGE['uri']['rub'] != 'img' && $MIB_PAGE['uri']['rub'] != 'css' && $MIB_PAGE['uri']['rub'] != 'js' && !mib_language($MIB_PAGE['uri']['rub']))
                    $MIB_PAGE['uri']['rewrite'] = $MIB_PAGE['uri']['lang'] . '/' . $MIB_PAGE['uri']['rewrite'];
            }

            foreach ($MIB_URL_REWRITED as $cur_rewrited => $cur_url) {
                // Ne prend pas en compte les URL de base
                if ($cur_url != '/' && !array_key_exists($cur_url, $MIB_CONFIG['languages'])) {
                    if (strpos($MIB_PAGE['uri']['rewrite'] . '/', $cur_url . '/') === 0)
                        $MIB_PAGE['uri']['rewrite'] = $cur_rewrited . substr($MIB_PAGE['uri']['rewrite'], strlen($cur_url));
                }
            }

            if ($MIB_PAGE['uri']['rewrite'] != $MIB_PAGE['uri']['request']) {
                if (empty($MIB_PAGE['uri']['infos'])) $MIB_PAGE['uri']['rewrite'] .= '/';
                if (isset($_GET) && !empty($_GET)) $MIB_PAGE['uri']['rewrite'] .= '?' . http_build_query($_GET);
                // ici le rewrite de la langue
                mib_header($MIB_PAGE['uri']['rewrite']);
            } else
                unset($MIB_PAGE['uri']['rewrite']);
        }


        // Cette url est elle optimisée/redirigée ?
        foreach ($MIB_URL_REWRITED as $cur_rewrited => $cur_url) {
            if ($cur_rewrited == $MIB_PAGE['uri']['request']) {
                $MIB_PAGE['uri']['rewrite'] = $cur_url;
                break;
            } else if (strpos($MIB_PAGE['uri']['request'], $cur_rewrited . '/') === 0) { // URL avec plusieurs sous rubriques
                $MIB_PAGE['uri']['rewrite'] = $cur_url . '/' . substr($MIB_PAGE['uri']['request'], strlen($cur_rewrited . '/'));
                break;
            }
        }

        // Cette rubrique est elle optimisée/redirigée ?
        if (empty($MIB_PAGE['uri']['rewrite']) && !empty($MIB_PAGE['uri']['rub'])) {
            foreach ($MIB_URL_REWRITED as $cur_rewrited => $cur_url) {
                if ($cur_rewrited == $MIB_PAGE['uri']['lang'] . '/' . $MIB_PAGE['uri']['rub']) {
                    $MIB_PAGE['uri']['rewrite'] = $cur_url;

                    if (!empty($MIB_PAGE['uri']['infos']))
                        $MIB_PAGE['uri']['rewrite'] .= '/' . $MIB_PAGE['uri']['infos'];
                    break;
                }
            }
        }

        // Si il y a Rewriting
        if (!empty($MIB_PAGE['uri']['rewrite']))
            @list($MIB_PAGE['lang'], $MIB_PAGE['rub'], $MIB_PAGE['infos']) = explode('/', $MIB_PAGE['uri']['rewrite'], 3);
        else {
            $MIB_PAGE['lang'] = $MIB_PAGE['uri']['lang'];
            $MIB_PAGE['rub'] = $MIB_PAGE['uri']['rub'];
            $MIB_PAGE['infos'] = $MIB_PAGE['uri']['infos'];
        }

        // Si la langue redirigée n'est pas valable
        if (!mib_language($MIB_PAGE['lang'])) {
            $MIB_PAGE['infos'] = $MIB_PAGE['rub'] . (!empty($MIB_PAGE['infos']) ? '/' . $MIB_PAGE['infos'] : '');
            $MIB_PAGE['rub'] = $MIB_PAGE['lang'];
            $MIB_PAGE['lang'] = MIB_LANG; // langue par defaut
        }

        // Si l'url contient des infos
        if (!empty($MIB_PAGE['infos'])) {
            // Sépare l'extension des infos
            $MIB_PAGE['info'] = current(preg_split('/\.([^\.]{2,4}$)/', $MIB_PAGE['infos']));
            // si il y en a une extension
            if ($MIB_PAGE['info'] != $MIB_PAGE['infos'])
                $MIB_PAGE['ext'] = str_replace($MIB_PAGE['info'] . '.', '', $MIB_PAGE['infos']);

            //unset($MIB_PAGE['infos']);
        } else
            $MIB_PAGE['info'] = '';

        // Si on a une rubrique / page
        if (!empty($MIB_PAGE['rub'])) {
            // Sépare l'extension de la rubrique si il y en a une et si il n'ya pas d'info
            if (empty($MIB_PAGE['info'])) {
                $MIB_PAGE['rubs'] = $MIB_PAGE['rub'];
                $MIB_PAGE['rub'] = current(preg_split('/\.([^\.]{2,3}$)/', $MIB_PAGE['rubs']));
                // si il y en a une extension
                if ($MIB_PAGE['rub'] != $MIB_PAGE['rubs'])
                    $MIB_PAGE['ext'] = str_replace($MIB_PAGE['rub'] . '.', '', $MIB_PAGE['rubs']);
                else
                    unset($MIB_PAGE['rubs']);
            }
        }

        // Charge les langues définitive
        mib_load_locale('system', $MIB_PAGE['lang']);
        mib_load_locale('website', $MIB_PAGE['lang']);

        // Vérifie/met à jour/définit le cookie et charge les infos de l'utilisateur
        cookie_login($MIB_USER);

        // met à jour le timezone
        mib_date_timezone_set($MIB_USER['timezone']);

        // On définit $MIB_PAGE['base_url']
        $MIB_PAGE['base_url'] = defined('MIB_MANAGE') ? $MIB_CONFIG['base_url'] . '/' . $MIB_PAGE['lang'] . '/' . MIB_ADMIN_DIR : $MIB_CONFIG['base_url'] . '/' . $MIB_PAGE['lang'];

        // ajoute url
        $MIB_PAGE['uri']['url'] = mib_trim($MIB_PAGE['uri']['rub'] . '/' . $MIB_PAGE['uri']['infos'], '/');
        $MIB_PAGE['url'] = mib_trim($MIB_PAGE['rub'] . '/' . $MIB_PAGE['infos'], '/');


    }

    public function processAssets()
    {
        global $MIB_PAGE, $MIB_CONFIG, $MIB_DB, $MIB_USER;

        // Charge le script des fonctions spécifiques (website)
        if (file_exists(MIB_PATH_VAR . 'functions.php'))
            require MIB_PATH_VAR . 'functions.php';

        // Charge le script commun spécifiques (website)
        if (file_exists(MIB_PATH_VAR . 'common.php'))
            require MIB_PATH_VAR . 'common.php';

        // Connexion réservée à l'admin
        if (defined('MIB_MANAGE'))
            include MIB_PATH_SYS . 'admin.php';

        // Gestion des feuilles de styles css
        if (defined('MIB_CSS'))
            include MIB_PATH_SYS . 'exe/css.php';

        // Gestion des javascript
        if (defined('MIB_JS'))
            include MIB_PATH_SYS . 'exe/js.php';

        if (defined('MIB_LIBS')) {
            $ext = $MIB_PAGE['ext'];
            if ($ext === 'css')
                include MIB_PATH_SYS . 'exe/css.php';
            if ($ext === 'js')
                include MIB_PATH_SYS . 'exe/js.php';

            // pour les images
            if (in_array($ext, ['png', 'gif', 'svg', 'jpg', 'jpeg'])) {
                header('Content-type: text/html; charset=utf-8');
                include MIB_PATH_SYS . 'libs/' . $MIB_PAGE['info'] . '.' . $ext;
                exit();
            }
        }

        // Gestion des images
        if (defined('MIB_IMG'))
            include MIB_PATH_SYS . 'exe/img.php';


    }

    public function processAjax()
    {
        global $MIB_DB, $MIB_PAGE;
        include MIB_PATH_SYS . 'exe/ajax.php';
    }

    public function processJson()
    {
        global $MIB_DB, $MIB_PAGE;
        include MIB_PATH_SYS . 'exe/json.php';
    }

    public function isPluginRequest()
    {
        global $MIB_PAGE;
        return !empty($MIB_PAGE['rub']) && mib_file_exists('plugins/' . $MIB_PAGE['rub']);
    }

    public function loadPlugin()
    {
        global $MIB_PLUGIN, $MIB_PAGE;
        $MIB_PLUGIN = mib_load_plugin($MIB_PAGE['rub']);
    }

    public function finalizeTemplate(string $tpl)
    {
        global $MIB_CONFIG, $MIB_PAGE, $MIB_WIDGET, $MIB_URL;

        $MIB_PAGE['tpl'] = $tpl;
        // START SUBST - Charge les configuration d'une page {{page:patern info}}
        $cur_pattern = array(
            'template',        // template spécial pour la page
            'title',        // titre de la page
            'meta_robots',        // meta de la balise robots
            'meta_description',    // meta de la balise description
            'meta_keywords'        // meta de la balise keywords
        );

        $MIB_PAGE['pattern']['page'] = '#{{page:(' . implode('|', $cur_pattern) . ')(\s(.*?))?\}}#is';

        while (preg_match($MIB_PAGE['pattern']['page'], $MIB_PAGE['tpl'], $cur_match)) {
            $cur_match_value = mib_trim($cur_match[3]);
            if (!empty($cur_match_value)) { // Une valeur existe
                $cur_match_type = utf8_strtolower($cur_match[1]);

                if ($cur_match_type == 'template') {
                    if (mib_theme_exists('tpl/' . $cur_match_value . '.html'))
                        $MIB_PAGE['template'] = mib_theme_get_contents('tpl/' . $cur_match_value . '.html');
                } else
                    $MIB_PAGE[$cur_match_type] = $cur_match_value;
            }

            $MIB_PAGE['tpl'] = str_replace($cur_match[0], '', $MIB_PAGE['tpl']);
        }
        // on essait de déterminer le titre à partir du 1er <h1> rencontré
        if (empty($MIB_PAGE['title']) && preg_match_all('#<h1[^>]*>(.*)</h1>#siU', $MIB_PAGE['tpl'], $cur_match)) {
            if (!empty($cur_match[1][0]))
                $MIB_PAGE['title'] = mib_strip_tags($cur_match[1][0], false);
        }

        // Chargement du template si il n'a pas encore été chargé
        if (empty($MIB_PAGE['template'])) {
            if (defined('MIB_MANAGE') && mib_theme_exists('admin/tpl/main.html'))
                $MIB_PAGE['template'] = mib_theme_get_contents('admin/tpl/main.html');
            else if (mib_theme_exists('tpl/main.html'))
                $MIB_PAGE['template'] = mib_theme_get_contents('tpl/main.html');
        }

        if (empty($MIB_PAGE['template']))
            error(sprintf(__('Impossible de charger le template %s. Vérifiez si il est bien installé.'), '<code>main.html</code>'), __FILE__, __LINE__);

        // On intègre le contenu de la page dans le template préalablement chargé
        $MIB_PAGE['main'] = str_replace('{{tpl:MIB_MAIN}}', mib_trim($MIB_PAGE['tpl']), $MIB_PAGE['template']);

        // Charge les widgets

        $MIB_PAGE['pattern']['widget'] = '#{{widget:(.*?)(\s(.*?))(\s(.*?))?\}}#is';
        while (preg_match($MIB_PAGE['pattern']['widget'], $MIB_PAGE['main'], $cur_match)) {
            $cur_match_plugin = mib_trim($cur_match[1]);
            $cur_match_widget = mib_trim($cur_match[3]);

            $MIB_PAGE['widget_infos'] = false;
            if (isset($cur_match[4])) $cur_match[4] = mib_trim($cur_match[4]);
            if (!empty($cur_match[4])) $MIB_PAGE['widget_infos'] = mib_trim($cur_match[4]);

            // Charge un widget si le plugin existe
            if (!empty($cur_match_plugin) && !empty($cur_match_widget) && mib_file_exists('plugins/' . $cur_match_plugin)) {
                $MIB_WIDGET = mib_load_widget($cur_match_plugin, $cur_match_widget);

                // Le widget n'est pas vide
                if (!empty($MIB_WIDGET['tpl']))
                    $MIB_PAGE['main'] = str_replace($cur_match[0], mib_trim($MIB_WIDGET['tpl']), $MIB_PAGE['main']);
            }

            $MIB_PAGE['main'] = str_replace($cur_match[0], '', $MIB_PAGE['main']);
        }

        // optimisation du cache des images
        $MIB_IMAGES = array();
        $i = 0;
        while (preg_match('#("|\'|/)img/(.*?).(jpg|jpeg|png|gif)#is', $MIB_PAGE['main'], $cur_match)) {
            $i++;

            $MIB_IMAGES[$i] = $cur_match;
            $MIB_PAGE['main'] = str_replace($cur_match[0], '{{MIB_IMG_TEMP_' . $i . '}}', $MIB_PAGE['main']);
        }
        foreach ($MIB_IMAGES as $i => $cur_match) {
            $MIB_PAGE['main'] = str_replace('{{MIB_IMG_TEMP_' . $i . '}}', $cur_match[0], $MIB_PAGE['main']);
        }

        // Construction du header
        // Le header est utilisé uniquement pour les pages normale (non json,ajax,css,etc...)
        $MIB_PAGE['header']['base'] = '<base href="' . $MIB_PAGE['base_url'] . '/">';

        // TITRE de la page
        if (defined('MIB_MANAGE')) { // TITRE du BO
            if (!empty($MIB_PAGE['title']))
                $MIB_PAGE['title'] = mib_html($MIB_CONFIG['site_title']) . ' &rsaquo; ' . mib_html($MIB_PAGE['title']);
            else
                $MIB_PAGE['title'] = mib_html($MIB_CONFIG['site_title']);
        } else {
            // L'URL de base n'a pas de titre optimisé
            if (empty($MIB_URL['/']['title']))
                $MIB_URL['/']['title'] = $MIB_CONFIG['site_title'];

            // Cette url à un titre optimisé
            if (isset($MIB_PAGE['uri']['rewrite']) && !empty($MIB_URL[$MIB_PAGE['uri']['rewrite']]['title']))
                $MIB_PAGE['title'] = mib_html($MIB_URL[$MIB_PAGE['uri']['rewrite']]['title']) . ' | ' . mib_html($MIB_URL['/']['title']);
            // Cette url fait partie d'une rubrique avec un titre optimisée
            else if (!empty($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['title'])) {
                if (!empty($MIB_PAGE['title']) && $MIB_PAGE['title'] != $MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['title'])
                    $MIB_PAGE['title'] = mib_html($MIB_PAGE['title']) . ' | ' . mib_html($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['title']) . ' | ' . mib_html($MIB_URL['/']['title']);
                else
                    $MIB_PAGE['title'] = mib_html($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['title']) . ' | ' . mib_html($MIB_URL['/']['title']);
            } // Page d'accueil d'une langue
            else if (!empty($MIB_URL[$MIB_PAGE['lang']]['title']) && $MIB_PAGE['uri']['request'] == $MIB_PAGE['uri']['lang'])
                $MIB_PAGE['title'] = mib_html($MIB_URL[$MIB_PAGE['lang']]['title']) . ' | ' . mib_html($MIB_URL['/']['title']);
            // Cette url à déjà un titre (définit par un plugin par exemple)
            else if (!empty($MIB_PAGE['title']) && $MIB_PAGE['title'] != $MIB_URL['/']['title'])
                $MIB_PAGE['title'] = mib_html($MIB_PAGE['title']) . ' | ' . mib_html($MIB_URL['/']['title']);
            // Aucun titre trouvé, on met le titre par défaut
            else
                $MIB_PAGE['title'] = mib_html($MIB_URL['/']['title']);

            //$MIB_PAGE['header']['top'] = '<link rel="top" href="'.$MIB_PAGE['base_url'].'/" title="'.mib_html($MIB_URL['/']['title']).'">';
        }
        $MIB_PAGE['header']['title'] = '<title>' . $MIB_PAGE['title'] . '</title>';

        // META-ROBOTS
        if (defined('MIB_MANAGE'))
            $MIB_PAGE['meta_robots'] = 'noindex, nofollow, noarchive';
        else {
            // L'URL de base n'a pas de meta_robots optimisé
            if (empty($MIB_URL['/']['meta_robots']))
                $MIB_URL['/']['meta_robots'] = 'index,follow';

            // Cette url est optimisée et à un meta_robots optimisé
            if (!empty($MIB_PAGE['uri']['rewrite']) && !empty($MIB_URL[$MIB_PAGE['uri']['rewrite']]['meta_robots']))
                $MIB_PAGE['meta_robots'] = mib_html($MIB_URL[$MIB_PAGE['uri']['rewrite']]['meta_robots']);
            // Cette url n'est pas optimisée mais à un meta_robots optimisée
            else if (!empty($MIB_URL[$MIB_PAGE['uri']['request']]['meta_robots']))
                $MIB_PAGE['meta_robots'] = mib_html($MIB_URL[$MIB_PAGE['uri']['request']]['meta_robots']);
            // Cette url à déjà un meta_robots (définit par un plugin par exemple)
            else if (!empty($MIB_PAGE['meta_robots']))
                $MIB_PAGE['meta_robots'] = mib_html($MIB_PAGE['meta_robots']);
            // Cette url fait partie d'une rubrique avec un meta_robots optimisée
            else if (!empty($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['meta_robots']))
                $MIB_PAGE['meta_robots'] = mib_html($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['meta_robots']);
            // Aucun meta_robots trouvé, on met le meta_robots par défaut
            else
                $MIB_PAGE['meta_robots'] = mib_html($MIB_URL['/']['meta_robots']);
        }
        $MIB_PAGE['header']['meta_robots'] = '<meta name="robots" content="' . $MIB_PAGE['meta_robots'] . '">';

        // META-DESCRIPTION
        if (!defined('MIB_MANAGE')) {
            // L'URL de base de la langue en cours à un meta_description optimisé
            if (!empty($MIB_URL[$MIB_PAGE['lang']]['meta_description']))
                $MIB_URL['/']['meta_description'] = $MIB_URL[$MIB_PAGE['lang']]['meta_description'];

            // Cette url est optimisée et à un meta_description optimisé
            if (!empty($MIB_PAGE['uri']['rewrite']) && !empty($MIB_URL[$MIB_PAGE['uri']['rewrite']]['meta_description']))
                $MIB_PAGE['meta_description'] = mib_html($MIB_URL[$MIB_PAGE['uri']['rewrite']]['meta_description']);
            // Cette url n'est pas optimisée mais à un meta_description optimisée
            else if (!empty($MIB_URL[$MIB_PAGE['uri']['request']]['meta_description']))
                $MIB_PAGE['meta_description'] = mib_html($MIB_URL[$MIB_PAGE['uri']['request']]['meta_description']);
            // Cette url à déjà un meta_description (définit par un plugin par exemple)
            else if (!empty($MIB_PAGE['meta_description']))
                $MIB_PAGE['meta_description'] = mib_html($MIB_PAGE['meta_description']);
            // Cette url fait partie d'une rubrique avec un meta_description optimisée
            else if (!empty($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['meta_description']))
                $MIB_PAGE['meta_description'] = mib_html($MIB_URL[$MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub']]['meta_description']);
            // Aucun meta_description trouvé, on met le meta_description par défaut
            else
                $MIB_PAGE['meta_description'] = mib_html($MIB_URL['/']['meta_description']);

            // Si on a un meta_description on l'inclu dans le header
            if (!empty($MIB_PAGE['meta_description']))
                $MIB_PAGE['header']['meta_description'] = '<meta name="description" content="' . $MIB_PAGE['meta_description'] . '">';
        }
    }

    public function printPage()
    {

        global $MIB_PAGE, $MIB_PLUGIN;

        ob_start();
        // admin
        if (defined('MIB_MANAGE'))
            echo "\t" . '<link rel="stylesheet" href="css/admin.css">' . "\n";
        // plugin
        if (defined('MIB_PLUGIN') && mib_file_exists('plugins/' . $MIB_PLUGIN['name'] . '/' . $MIB_PLUGIN['name'] . '.css'))
            echo "\t" . '<link rel="stylesheet" href="css/' . $MIB_PLUGIN['name'] . '.css">' . "\n";

        $MIB_PAGE['stylesheet'] = ob_get_contents();
        ob_end_clean();

        $compress_CSS = defined('MIB_COMPRESS_CSS') && mib_core_class('Minify_CSS_Compressor') ? true : false;
        while (preg_match("#<style[^>]*>(.*?)</style>#is", $MIB_PAGE['main'], $cur_match)) { // <style[^>]*>(.*?)</style> --- clean les CSS

            $cur_CSS = $cur_match[1];
            if ($compress_CSS) {
                $cur_CSS_compressed = Minify_CSS_Compressor::process(mib_trim($cur_CSS));
                if ($cur_CSS_compressed && !empty($cur_CSS_compressed))
                    $cur_CSS = $cur_CSS_compressed;
            }

            if (mib_trim($cur_CSS) != '')
                $MIB_PAGE['main'] = str_replace($cur_match[0], '{{MIB_style}}' . $cur_CSS . '{{MIB_/style}}', $MIB_PAGE['main']);
            else
                $MIB_PAGE['main'] = str_replace($cur_match[0], '', $MIB_PAGE['main']);
        }
        $MIB_PAGE['main'] = str_replace('{{MIB_style}}', '<style>', $MIB_PAGE['main']);
        $MIB_PAGE['main'] = str_replace('{{MIB_/style}}', '</style>', $MIB_PAGE['main']);

        /*
            JS
        */
        $compress_JS = defined('MIB_COMPRESS_JS') && mib_core_class('JSMin') ? true : false;
        while (preg_match("/<script((?:(?!src=).)*?)>(.*?)<\/script>/smix", $MIB_PAGE['main'], $cur_match)) {

            $cur_JS = $cur_match[2];
            if ($compress_JS) {
                $cur_JS_compressed = JSMin::minify($cur_JS);
                if ($cur_JS_compressed && !empty($cur_JS_compressed))
                    $cur_JS = $cur_JS_compressed;
            }

            if (mib_trim($cur_JS) != '')
                $MIB_PAGE['main'] = str_replace($cur_match[0], '{{MIB_script}}' . $cur_JS . '{{MIB_/script}}', $MIB_PAGE['main']);
            else
                $MIB_PAGE['main'] = str_replace($cur_match[0], '', $MIB_PAGE['main']);
        }
        $MIB_PAGE['main'] = str_replace('{{MIB_script}}', '<script>', $MIB_PAGE['main']);
        $MIB_PAGE['main'] = str_replace('{{MIB_/script}}', '</script>', $MIB_PAGE['main']);

        /*
            EMAIL encode
        */
        if (!defined('MIB_MANAGE') && defined('MIB_ENCODE_EMAIL') && mib_core_class('emailcode')) {
            $encode_EMAILS = array();
            $i = 0;

            while (preg_match('#(\s|[<>.:,;!?])[a-zA-Z0-9\-_]?[a-zA-Z0-9.\-_]+[a-zA-Z0-9\-_]?@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\s|[<>.:,;!?])#i', $MIB_PAGE['main'], $cur_match)) {
                $encode_EMAILS['MIB_emails_' . $i] = mib_trim($cur_match[0], " \t\n\r<>.:,;!?");
                $MIB_PAGE['main'] = str_replace($cur_match[0], $cur_match[1] . '{{tpl:MIB_emails_' . $i . '}}' . $cur_match[2], $MIB_PAGE['main']);
                $i++;
            }

            if (!empty($encode_EMAILS)) {
                $emailcode = new ClassEmailcode();
                foreach ($encode_EMAILS as $k => $v) {
                    $MIB_PAGE['main'] = str_replace('"{{tpl:' . $k . '}}"', $v, $MIB_PAGE['main']); // ne pas remplacer les email entre "" (utile pour les formulaires)
                    $MIB_PAGE['main'] = str_replace('\'{{tpl:' . $k . '}}\'', $v, $MIB_PAGE['main']); // ne pas remplacer les email entre '' (utile pour les formulaires et JS)
                    $MIB_PAGE['main'] = str_replace('{{tpl:' . $k . '}}', '<script>' . $emailcode->htmlgetencode('<a href="mailto:' . $v . '">' . $v . '</a>') . '</script>', $MIB_PAGE['main']);
                }
            }
        }

        // Prépare les balises $MIB_PAGE['head'] et $MIB_PAGE['foot']
        if (!empty($MIB_PAGE['header']))
            $MIB_PAGE['head'] = "\t" . implode("\n\t", $MIB_PAGE['header']);
        if (!empty($MIB_PAGE['footer']))
            $MIB_PAGE['foot'] = "\t" . implode("\n\t", $MIB_PAGE['header']);

        // Remplace les balises de template(url rewriting, etc...)
        $MIB_PAGE['main'] = mib_tpl_replace($MIB_PAGE['main']);

        // Compression du code HTML avec Minify
        if (defined('MIB_COMPRESS_HTML') && mib_core_class('Minify_HTML')) {
            $MIB_PAGE['main_compress'] = Minify_HTML::minify($MIB_PAGE['main']);

            if ($MIB_PAGE['main_compress'] && !empty($MIB_PAGE['main_compress']))
                $MIB_PAGE['main'] = $MIB_PAGE['main_compress'];
        }

        // Affiche les infos du debug (si actif)
        if (defined('MIB_DEBUG') && MIB_DEBUG_DISPLAY) {
            // START SUBST - <MIB_Debug>
            ob_start();

            mib_core_function('MIB_Debug');

            $cur_debug = trim(ob_get_contents());
            $MIB_PAGE['main'] = str_replace('</body>', $cur_debug . "\n" . '</body>', $MIB_PAGE['main']);
            ob_end_clean();
            // END SUBST - <MIB_Debug>
        }

    }

    public function loadContent()
    {

        global $MIB_PAGE, $MIB_PLUGIN;

        if (defined('MIB_PLUGIN'))
            return $MIB_PLUGIN['tpl'];

        $cur_page = null;

        // Fichier avec une extension
        if (!isset($MIB_PAGE['ext'])) {
            $uri = MibboFormManager::getPageKey($MIB_PAGE);
            $tplData = MibboFormManager::getPageTemplate($uri);
            $tpl  = !empty($tplData['key'])? $tplData['key'] : null;
            $MIB_PAGE['page_key'] = $uri;
            $handled = empty($tpl) ? false : true ;
            if($handled){
                if ( empty($tplData['handler']) || $tplData['handler'] === 'page') { // gestion par le système de page
                    $MIB_PAGE['handler'] = 'page';
                    $form = MibboFormManager::getPageForm($tpl, $uri, $MIB_PAGE['lang']);
                    if (!empty($form)) {
                        $cur_page = MibboFormManager::getPageContent($form);
                    }
                } else { // gestion par un plugin // on cherche un fichier {plugin}-page.php dans le répertoire du plugin
                    $file = MIB_PATH_VAR.'plugins'.DIRECTORY_SEPARATOR.$tplData['handler'].DIRECTORY_SEPARATOR.$tplData['handler'].'_handlePage.php';
                    $MIB_PAGE['handler'] = $tplData['handler'];
                    if(file_exists($file)){
                        ob_start();
                        include $file;
                        $cur_page = ob_get_contents();
                        ob_end_clean();
                    }
                }
            }
            else {
                $MIB_PAGE['handler'] = 'static';
                // On a une rubrique et des infos
                if (!empty($MIB_PAGE['rub']) && !empty($MIB_PAGE['infos'])) {
                    // Charge la page si elle existe
                    if (file_exists(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub'] . '/' . $MIB_PAGE['infos'] . '.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub'] . '/' . $MIB_PAGE['infos'] . '.html');
                    else if (file_exists(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/' . $MIB_PAGE['rub'] . '/' . $MIB_PAGE['infos'] . '.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/' . $MIB_PAGE['rub'] . '/' . $MIB_PAGE['infos'] . '.html');
                } // On a uniquement une rubrique
                else if (!empty($MIB_PAGE['rub'])) {
                    // Charge la page si elle existe
                    if (file_exists(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub'] . '.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/' . $MIB_PAGE['rub'] . '.html');
                    elseif (file_exists(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/' . $MIB_PAGE['rub'] . '.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/' . $MIB_PAGE['rub'] . '.html');
                } // Page d'accueil ?
                else {
                    // Charge la page si elle existe
                    if (file_exists(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/index.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . $MIB_PAGE['lang'] . '/index.html');
                    else if (file_exists(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/index.html'))
                        $cur_page = file_get_contents(MIB_PATH_VAR . 'pages/' . MIB_LANG . '/index.html');
                }
            }

        }

        // Si aucun fichier/page n'a été trouvée
        if (empty($cur_page)) {
            if (defined('MIB_AJAX'))
                error(__('Aucune réponse AJAX.'), __FILE__, __LINE__);
            else if (defined('MIB_JSON'))
                error(__('Aucune réponse JSON.'), __FILE__, __LINE__);
            else if (defined('MIB_MANAGE'))
                mib_header(MIB_ADMIN_DIR);
            else
                mib_error_404();
        }

        return $cur_page;
    }

    public function registerPlugins()
    {

        // on référence la classe pour éventuellement ajouter des choses dessus
        $mibboEngine = $this;
        // on va chercher dans les plugins s'il y des fichier [[plugin]]-regiter.php et les intégre le cas échéant
        $pluginDir = MIB_PATH_VAR.'plugins'.DIRECTORY_SEPARATOR;
        if(is_dir($pluginDir)){
           $handle = opendir($pluginDir) ;
            while (false !== ($entry = readdir($handle))) {
                $registerFile = $pluginDir.DIRECTORY_SEPARATOR.$entry.DIRECTORY_SEPARATOR.$entry.'-register.php';
               if(file_exists($registerFile)){
                   require_once $registerFile;
               }
            }
            closedir($handle);
        }
    }
}