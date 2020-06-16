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
 * Ajouter define('MIB_DEBUG', 1); dans config.php
 * pour activer le mode DEBUG pendant le développement
 */
if ( defined('MIB_DEBUG') ) {
	error_reporting(-1);
	ini_set('display_errors', 0);

	// ajouter define('MIB_DEBUG_DISPLAY', false); dans config.php pour ne pas afficher les messages d'erreurs PHP & DATABASE
	if ( !defined('MIB_DEBUG_DISPLAY') )
		define('MIB_DEBUG_DISPLAY', 1);

	// ajouter define('MIB_DEBUG_LOG', false); dans config.php pour ne pas enregistrer les erreurs dans le fichier de log
	if ( !defined('MIB_DEBUG_LOG') || MIB_DEBUG_LOG ) {
		ini_set('log_errors', 1);

		if ( defined('MIB_INSTALL') )
			ini_set('error_log', MIB_PATH_VAR.'install.log');
		else
			ini_set('error_log', MIB_PATH_VAR.'debug.log');
	}
}
else { // assurons nous que PHP reporte toutes les erreurs sauf E_NOTICE et E_USER_NOTICE
	error_reporting(E_ALL ^ E_NOTICE ^ E_USER_NOTICE);
	@ini_set('display_errors', 0);

	if ( defined('MIB_INSTALL') ) {
		@ini_set('log_errors', 1);
		@ini_set('error_log', MIB_PATH_VAR.'install.log');
	}
}

//todo Remove in PROD
//error_reporting(E_ALL);
//@ini_set('display_errors', 1);

/**
 * Affiche un message d'erreur lié à Mibbo
 * 
 * @param string $message Message d'erreur
 */
function mib_error($message = false, $title= false) {
	if( !$message )
		$message = __('Erreur inconnue.');
	if( !$title )
		$title = __('Erreur');

	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol)
		$protocol = 'HTTP/1.0';

	header($protocol.' 500 Internal Server Error', true, 500);
	header('Content-Type: text/html; charset=utf-8');

	exit('<!doctype html><html><head><title>'.$title.'</title><link rel="stylesheet" href="'.(defined('MIB_BASE_URL') ? MIB_BASE_URL.'/' : '').'theme/default/admin/install.css"></head><body id="error"><header><a href="http://mibbo.net" target="_blank"><img style="max-width:300px;" src="'.(defined('MIB_BASE_URL') ? MIB_BASE_URL.'/' : '').'theme/default/img/logo-mibbo-full.png" alt="'.MIB_SYS_NAME.' '.MIB_SYS_VERSION.'"></a></header><article><h1>'.$title.'</h1><p>'.$message.'</p></article></body></html>');
}

/**
 * Ajoute une erreur à la liste (à cause d'un champ mal rempli dans un formulaire par exemple, ou d'une erreur de requette)
 *
 * @param {string} $message Message
 * @param {string} $uid Id unique concernant l'erreur
 */
function mib_error_set($message, $uid = false) {
	global $MIB_PAGE;

	if ( !$uid ) $uid = md5($message);

	// ajoute l'erreur à la liste des erreur du système
	$MIB_PAGE['error_form'][$uid] = $message;
}

/**
 * Est-ce qu'on a des erreurs de formulaire ?
 *
 * @return {bool}
 */
function mib_error_exists() {
	global $MIB_PAGE;

	if ( isset($MIB_PAGE['error_form']) && !empty($MIB_PAGE['error_form']) )
		return true;
	else
		return false;
}

/**
 * Renvois les erreurs rencontrées
 *
 * @return {array}
 */
function mib_error_get() {
	global $MIB_PAGE;

	if ( isset($MIB_PAGE['error_form']) && !empty($MIB_PAGE['error_form']) )
		return $MIB_PAGE['error_form'];
	else
		return array();
}

/**
 * Affiche les erreurs rencontrée dans le BO
 *
 * @return {array}
 */
function mib_error_notify($first = false) {
	if ( $first )
		error(current(mib_error_get()));
	else
		error('<p>'.implode('</p><p>', mib_error_get()).'</p>'); // affiche les erreurs
}

/**
 * Affiche un message d'erreur
 * 
 * @uses $MIB_CONFIG
 *
 * @param string $str Message à traduire
 *
 * @return string $str Message traduit si il y a la traduction
 */
function error() {
	global $MIB_CONFIG;

	/*
		Parse les arguments. Différentes signatures sont possibles:
		error('Error message.');
		error(__FILE__, __LINE__);
		error('Error message.', __FILE__, __LINE__);
	*/

	$num_args = func_num_args();
	if ($num_args == 3) {
		$message = func_get_arg(0);
		$file = func_get_arg(1);
		$line = func_get_arg(2);
	}
	else if ($num_args == 2) {
		$file = func_get_arg(0);
		$line = func_get_arg(1);
	}
	else if ($num_args == 1)
		$message = func_get_arg(0);

	// Definit un titre par défaut si le script "failed" avant que $MIB_CONFIG n'ai été chargé
	if (empty($MIB_CONFIG))
		$MIB_CONFIG['site_title'] = 'Mibbo';

	// Vide tous les buffers et stop le buffering
	while (@ob_end_clean());

	// On "Restart" le buffering si on utilise ob_gzhandler
	if (defined('MIB_GZIP_OUTPUT') && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
		ob_start('ob_gzhandler');

	// Erreur AJAX
	if(defined('MIB_AJAX')) {
?>
		<div class="message error">
			<h1><?php echo __('Une erreur a été rencontrée'); ?></h1>
			<?php if (isset($message)) { echo '<p>'.$message.'</p>'."\n"; } ?>
<?php
	// On à des arguments suplémentaire, et le mode DEBUG est actif
	if ($num_args > 1 && defined('MIB_DEBUG') && MIB_DEBUG_DISPLAY) {

		if (isset($file) && isset($line))
			echo "\t\t".'<p><em>'.sprintf(__('L\'erreur a eu lieu à la ligne %1$d dans %2$s'), $line, '<br><code>'.$file.'</code>').'</em></p>'."\n";

		$db_error = isset($GLOBALS['MIB_DB']) ? $GLOBALS['MIB_DB']->error() : array();
		if (!empty($db_error['error_msg'])) {
			echo "\t\t".'<p><strong>Database reported :</strong><br>'.mib_html($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '').'.</p>'."\n";

			if ($db_error['error_sql'] != '')
				echo '<p><strong>Failed query :</strong><br><code>'.mib_html($db_error['error_sql']).'</code></p>'."\n";
		}
	}
?>
		</div>
<?php
	}
	// Erreur JSON
	else if(defined('MIB_JSON')) {
		$error = array();
		$error['title'] = __('Une erreur a été rencontrée');
		if (isset($message))
			$error['error'] = mib_trim($message);
		else
			$error['error'] = __('Erreur inconnue.');

		// On à des arguments suplémentaire, et le mode DEBUG est actif
		if ($num_args > 1 && defined('MIB_DEBUG') && MIB_DEBUG_DISPLAY) {
			if (isset($file) && isset($line)) {
				$error['debug']['line'] = $line;
				$error['debug']['file'] = $file;
			}
			$db_error = isset($GLOBALS['MIB_DB']) ? $GLOBALS['MIB_DB']->error() : array();
			if (!empty($db_error['error_msg'])) {
				$error['debug']['database']['msg'] = $db_error['error_msg'];
				if($db_error['error_no'])
					$error['debug']['database']['no'] = $db_error['error_no'];
				if ($db_error['error_sql'] != '')
					$error['debug']['database']['sql'] = $db_error['error_sql'];
			}
		}
		echo json_encode($error);
	}
	// Erreur standard
	else {
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo mib_html($MIB_CONFIG['site_title']); ?> &rsaquo; <?php echo __('Erreur'); ?></title>
		<link rel="stylesheet" href="<?php if(defined('MIB_BASE_URL')) { echo MIB_BASE_URL.'/'; } ?>theme/default/admin/install.css" type="text/css">
	</head>
	<body id="error">
	<header><a href="http://mibbo.net" target="_blank"><img style="max-width:300px;" src="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/img/logo-mibbo-full.png" alt="<?php echo MIB_SYS_NAME.' '.MIB_SYS_VERSION; ?>"></a></header>
	<article>
		<h1><?php echo __('Une erreur a été rencontrée'); ?></h1>
		<?php if (isset($message)) { echo '<p>'.$message.'</p>'."\n"; } ?>
<?php
	// On à des arguments suplémentaire, et le mode DEBUG est actif
	if ($num_args > 1 && defined('MIB_DEBUG') && (defined('MIB_INSTALL') || MIB_DEBUG_DISPLAY) ) {

		if (isset($file) && isset($line))
			echo "\t\t".'<p><em>'.sprintf(__('L\'erreur a eu lieu à la ligne %1$d dans %2$s'), $line, '<br><code>'.$file.'</code>').'</em></p>'."\n";

		$db_error = isset($GLOBALS['MIB_DB']) ? $GLOBALS['MIB_DB']->error() : array();
		if (!empty($db_error['error_msg'])) {
			echo "\t\t".'<p><strong>Database reported :</strong><br>'.mib_html($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '').'.</p>'."\n";

			if ($db_error['error_sql'] != '')
				echo '<p><strong>Failed query :</strong><br><code>'.mib_html($db_error['error_sql']).'</code></p>'."\n";
		}
	}

	if( defined('MIB_DEBUG') && MIB_DEBUG_DISPLAY )
		mib_core_function('MIB_Debug');
?>
	</article>
	</body>
</html>
<?php
	}

	// Si la connection à la DB avait été établie (avant cette erreur) on la ferme
	if (isset($GLOBALS['MIB_DB']))
		$GLOBALS['MIB_DB']->close();

	exit;
}

/**
 * Gestionnaire d'affichage des erreurs SIMPLES type Apache 2
 *
 * @param {string} $message Message
 * @param {string} $title Titre
 * @param {array} $opt
 *	options:
 *		http_response - {string}
 *		redirect_url - {string} URL de redirection si on veut redirigé automatiquement après l'affichage de cette erreur
 *		redirect_delay - {int} Delay de redirection en seconde
 */
function mib_error_display($message = false, $title = false, $opt = array()) {
	global $MIB_PAGE;

	// fusionne les options
	$opt = array_merge(array(
		'http_response'		=> '200 OK',
		'redirect_url'		=> false,
		'redirect_delay'	=> 5,
	), (array)$opt);

	// vide tous les buffers et stop le buffering
	while ( @ob_end_clean() );

	// On "Restart" le buffering si on utilise ob_gzhandler
	if (defined('MIB_GZIP_OUTPUT') && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
		ob_start('ob_gzhandler');

	$title = !empty($title) ? $title : __('Erreur');
	$message = !empty($message) ? $message : __('Une erreur inconnue c\'est produite !');
	$http_response = mib_get_http_response_code($opt['http_response']) <= 0 ? '500 Internal Server Error' : $opt['http_response'];
	$protocol = ('HTTP/1.1' != $_SERVER['SERVER_PROTOCOL'] && 'HTTP/1.0' != $_SERVER['SERVER_PROTOCOL']) ? 'HTTP/1.0' : $_SERVER['SERVER_PROTOCOL'];

	// Si la connection à la DB avait été établie (avant cette erreur) on la ferme
	if ( isset($GLOBALS['MIB_DB']) )
		$GLOBALS['MIB_DB']->close();

	header($protocol.' '.$http_response, true, mib_get_http_response_code($http_response));
	mib_headers_no_cache(); // Envoi des header (no-cache)
	header('Content-Type: text/html; charset=utf-8');

	if ( mib_theme_exists('tpl/error.html') ) {
		$MIB_PAGE['template'] = mib_theme_get_contents('tpl/error.html');

		// Construit le header.
		$MIB_PAGE['header'] = array(
			'base'			=> '<base href="'.$MIB_PAGE['base_url'].'/">',
			'title'			=> '<title>'.mib_html($title).'</title>',
			'meta_robots'	=> '<meta name="robots" content="noindex, nofollow, noarchive">',
		);
		$MIB_PAGE['head'] = "\t".implode("\n\t", $MIB_PAGE['header']);

		// message d'erreur
		$MIB_PAGE['error_title'] = mib_html($title);
		$MIB_PAGE['error_message'] = mib_html($message);

		$MIB_PAGE['main'] = mib_tpl_replace($MIB_PAGE['template']);

		exit($MIB_PAGE['main']);
	}
	else {
?><!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title><?php e_html($http_response); ?></title>
</head><body>
<h1><?php e_html($title); ?></h1>
<p><?php e_html($message); ?></p>
</body></html><?php
		exit;
	}
}

/**
 * Racourcis pour affiche un message d'erreur 404
 *
 * @param {string} $message Message
 * @param {array} $opt
 */
function mib_error_404($message = false, $opt = array()) {

	// force les options
	$opt = array_merge((array)$opt, array(
		'http_response'		=> '404 Not Found'
	));

	if ( empty($message) )
		$message = __('Petit problème... Nous ne sommes pas parvenus à trouver la page que vous demandez !');

	// erreur 404
	mib_error_display($message, __('Page introuvable'), $opt);
}

/**
 * Racourcis pour affiche un message d'erreur 403
 *
 * @param {string} $message Message
 * @param {array} $opt
 */
function mib_error_403($message = false, $opt = array()) {

	// force les options
	$opt = array_merge((array)$opt, array(
		'http_response'		=> '403 Forbidden'
	));

	if ( empty($message) )
		$message = __('Petit problème... Vous n\'avez pas la permission d\'accéder à cette page !');

	// erreur 403
	mib_error_display($message, __('Accès interdit'), $opt);
}