<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/
defined('MIB_ROOT') or exit; // Assurons nous que le script n'est pas executé "directement"

/*
	FICHIER CONTENANT L'ASSISTANT D'INSTALLATION AUTOMATIQUE.
	POUR PLUS DE SÉCURITÉ, SUPPRIMEZ CE FICHIER APRÈS L'INSTALLATION DE MIBBO.
*/

define('MIB', 1);

// Fonctions de Traduction
function __i($str) { return __($str); } // Version INSTALL
function _ei($str) { echo __($str); } // Version INSTALL

// phpinfo() en tableau ;)
function mib_phpinfo() {
	ob_start();phpinfo(-1);$phpinfo = ob_get_clean();

	$phpinfo = preg_replace(
		array(
			'#^.*<body>(.*)</body>.*$#ms',
			'#<h2>PHP License</h2>.*$#ms',
			'#<h1>Configuration</h1>#',
			"#\r?\n#",
			"#</(h1|h2|h3|tr)>#",
			'# +<#',
			"#[ \t]+#",
			'#&nbsp;#',
			'#  +#',
			'# class=".*?"#',
			'%&#039;%',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
			'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
			"# +#",
			'#<tr>#',
			'#</tr>#'
		),
		array(
			'$1',
			'',
			'',
			'',
			'</$1>'."\n",
			'<',
			' ',
			' ',
			' ',
			'',
			' ',
			'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'."\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
			'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
			'<tr><td>Zend Engine</td><td>$2</td></tr>'."\n".'<tr><td>Zend Egg</td><td>$1</td></tr>',
			' ',
			'%S%',
			'%E%'
		),
		$phpinfo
	);

	$phpinfo_sections = explode('<h2>', strip_tags($phpinfo, '<h2><th><td>'));
	unset($phpinfo_sections[0]);

	$phpinfo = array();

	foreach ( $phpinfo_sections as $section ) {
		$n = substr($section, 0, strpos($section, '</h2>'));

		preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);

		foreach ( $askapache as $m ) {
			if ( !isset($m[2]) && $n == 'Additional Modules' )
				$m[2] = '&nbsp;';
			if ( isset($m[2]) )
				$phpinfo[$n][$m[1]] = ( !isset($m[3]) || $m[2] == $m[3] ) ? $m[2] : array_slice($m,2);
		}
	}

	return $phpinfo;
}

// génération du fichier de configuration
function generate_mib_config_file($cfg) {
	$file = array();

	$file[] = '<?php'."\n";

	$file[] = '// Configuration de la base de données';
	$file[] = 'define(\'DB_TYPE\', \''.$cfg['db_type'].'\');';
	$file[] = 'define(\'DB_HOST\', \''.addslashes($cfg['db_host']).'\');';
	$file[] = 'define(\'DB_NAME\', \''.addslashes($cfg['db_name']).'\');';
	$file[] = 'define(\'DB_USERNAME\', \''.addslashes($cfg['db_username']).'\');';
	$file[] = 'define(\'DB_PASSWORD\', \''.addslashes($cfg['db_password']).'\');';
	$file[] = 'define(\'DB_PREFIX\', \''.addslashes($cfg['db_prefix']).'\');';

	$file[] = "\n";

	$file[] = '// Sécuritée du cookie d\'authentification';
	$file[] = 'define(\'COOKIE_NAME\', \''.addslashes(mib_random_key(6, false, true)).'\');';
	$file[] = 'define(\'COOKIE_SEED\', \''.addslashes(mib_random_key(32, false, true)).'\');';

	$file[] = "\n";

	$file[] = '// Thème par défaut';
	$file[] = 'define(\'MIB_THEME\', \'website\');';

	$file[] = "\n";

	$file[] = '// DEBUG : N\'ACTIVEZ PAS cela sur un environnement de production !';
	$file[] = '//define(\'MIB_DEBUG\', 1);';
	$file[] = '//define(\'MIB_DEBUG_DISPLAY\', 0);';
	$file[] = '//define(\'MIB_DEBUG_LOG\', 0);';

	$file[] = "\n";

	$file[] = 'define(\'MIB_CONFIG\', 1);';

	return implode("\n", $file);
}

// téléchargement du fichier de config
if ( isset($_POST['generate_config']) ) {
	header('Content-Type: text/x-delimtext; name="config.php"');
	header('Content-disposition: attachment; filename=config.php');

	echo generate_mib_config_file($_POST);
	exit;
}

// détermine les extensions disponibles pour la DB
$dual_mysql = false;
$db_extensions = array();
$mysql_innodb = false;
if ( function_exists('mysqli_connect') ) {
	$db_extensions[] = array('mysqli', 'MySQL Improved');
	$db_extensions[] = array('mysqli_innodb', 'MySQL Improved (InnoDB)');
	$mysql_innodb = true;
}

if ( empty($db_extensions) )
	mib_error('Aucune extension de connection à une base de donnée n\'est disponible !');

// étapes du processus d'installation
$steps = array(
	0	=> 'hi',
	1	=> 'config',
	2	=> 'gooooo',
);
$step_name = isset($_GET['step']) ? $_GET['step'] : false;
$step = !in_array($step_name, $steps) ? 0 : array_search($step_name, $steps);

// prend en compte le fichier config.php pour les test de réinstall
$form_inputs = array(
	'db_type'		=> defined('DB_TYPE') ? DB_TYPE : '',
	'db_host'		=> defined('DB_HOST') ? DB_HOST : 'localhost',
	'db_name'		=> defined('DB_NAME') ? DB_NAME : '',
	'db_username'		=> defined('DB_USERNAME') ? DB_USERNAME : '',
	'db_password'		=> defined('DB_PASSWORD') ? DB_PASSWORD : '',
	'db_prefix'		=> defined('DB_PREFIX') ? DB_PREFIX : '',
	'site_title'		=> '',
	'site_email'		=> '',
	'server_timezone'	=> '',
	'sa_name'		=> '',
	'sa_email'		=> '',
	'sa_password1'		=> '',
	'sa_password2'		=> '',
	'anonymous_report'	=> '0',
);
foreach ( $form_inputs as $k => $v ) {
	if ( isset($_POST['form_sent']) && isset($_POST[$k]) )
		$form_inputs[$k] = mib_trim($_POST[$k]);
}

// TEST les erreurs
$errors = array();
if ( isset($_POST['form_sent']) ) {
	if ( empty($form_inputs['db_host']) )
		$form_inputs['db_host'] = 'localhost';

	// validation
	if ( strlen($form_inputs['db_prefix']) > 0 && (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $form_inputs['db_prefix']) || strlen($form_inputs['db_prefix']) > 40) )
		$errors['db_prefix'] = __i('Le préfixe ne doit pas contenir de caractères invalides et ne peut avoir plus de 40 caractères.');

	if ( empty($form_inputs['db_type']) )
		$errors['db_type'] = __i('Le type de la base de données est manquant.');

	if ( empty($form_inputs['db_name']) )
		$errors['db_name'] = __i('Le nom de la base de données est manquant.');

	if ( empty($errors) ) {
		if ( !defined('DB_TYPE') )
			define('DB_TYPE', $form_inputs['db_type']);

		// chargement DB requètes et connections
		if( mib_core_class('MIB_DbLayerX') ) {
			// On créer un nouvel objet
			$MIB_DB = new MIB_DbLayerX($form_inputs['db_host'], $form_inputs['db_username'], $form_inputs['db_password'], $form_inputs['db_name'], $form_inputs['db_prefix'], false);
		}
		else
			mib_error(__i('Impossible de charger le layer de la base de donnée. Veuillez vérifier qu\'il est bien installé.'));

		// InnoDB est dispo ?
		if ( $form_inputs['db_type'] == 'mysql_innodb' || $form_inputs['db_type'] == 'mysqli_innodb' ) {
			$result = $MIB_DB->query('SHOW VARIABLES LIKE \'have_innodb\'');
			list (, $result) = $MIB_DB->fetch_row($result);
			if ( (strtoupper($result) != 'YES') )
				$errors['db_type'] = __i('InnoDB n\'est pas activé.');
		}

		// Est-ce que Mibbo est déjà installé ?
		$query = array(
			'SELECT'	=> '1',
			'FROM'		=> 'users',
			'WHERE'		=> 'id = 1'
		);
		$result = $MIB_DB->query_build($query);
		if ( $MIB_DB->num_rows($result) )
			$errors['db_name'] = mib_sprintftpl(__i('[[%sys_name%]] est déjà installé sur cette base de données !'), array('sys_name'=>MIB_SYS_NAME));
	}

	if ( empty($form_inputs['site_title']) )
		$errors['site_title'] = __i('Veuillez indiquer un nom pour le site.');
	else if ( strpos($form_inputs['site_name'], '<') !== false || strpos($form_inputs['site_title'], '>') !== false )
		$errors['site_title'] = __i('Le nom du site ne peut pas contenir de caractère HTML.');

	if ( !mib_valid_email($form_inputs['site_email']) )
		$errors['site_email'] = __i('L\'adresse e-mail du site est invalide.');

	if ( empty($form_inputs['server_timezone']) || !in_array($form_inputs['server_timezone'], mib_date_timezones(false)) )
		$errors['server_timezone'] = __i('Le fuseau horaire du serveur est invalide.');

	// Convertit les caractères blancs multiples en un seul (pour prévenir des personnes qui s'inscrive avec des nom d'utilisateur indistingable)
	$form_inputs['sa_name'] = mib_clean($form_inputs['sa_name']);

	if ( utf8_strlen($form_inputs['sa_name']) < 2 )
		$errors['sa_name'] = __i('Le nom doit être constitué d\'au moins 4 caractères.');
	else if ( utf8_strlen($form_inputs['sa_name']) > 50 )
		$errors['sa_name'] = __i('Le nom ne peut contenir plus de 50 caractères.');

	if ( !mib_valid_email($form_inputs['sa_email']) )
		$errors['sa_email'] = __i('L\'adresse e-mail est invalide.');

	// Valide le mot de passe
	if ( utf8_strlen($form_inputs['sa_password1']) < 4 )
		$errors['sa_password1'] = __i('Le mot de passe doit être constitué d\'au moins 4 caractères.');
	else {
		if ( $form_inputs['sa_password1'] != $form_inputs['sa_password2'] )
			$errors['sa_password1'] = __i('Les mots de passe ne correspondent pas.');
	}

	// erreur, on retourne à l'étape appropriée
	if ( !empty($errors) )
		$step = 1;
}


// ÉTAPE 0 : Accueil de l'installation
if ( $step == 0 ) {
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo mib_sprintftpl(__i('Installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME.' '.MIB_SYS_VERSION)); ?></title>
		<link rel="stylesheet" href="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/admin/install.css" type="text/css">
	</head>
	<body>
		<header>
			<a href="http://mibbo.net" target="_blank"><img style="max-width:300px;" src="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/img/logo-mibbo-full.png" alt="<?php echo MIB_SYS_NAME.' '.MIB_SYS_VERSION; ?>"></a>
		</header>
		<article>
			<h2><?php _ei('Installation'); ?></h2>
			<p><?php echo mib_sprintftpl(__i('Soyez le bienvenu dans le programme d\'installation de [[%sys_name%]].'), array('sys_name'=>'<strong>'.MIB_SYS_NAME.' '.MIB_SYS_VERSION.'</strong>')); ?></p>
			<p><?php echo mib_sprintftpl(__i('Si vous rencontrez des difficultés lors de l\'installation, merci de consulter la documentation sur [[%documentation_link%]].'), array('documentation_link'=>'<a href="'.mib_html(MIB_SYS_WEBSITE_URL).'" target="_blank">'.mib_html(MIB_SYS_WEBSITE_URL).'</a>')); ?></p>
			<p>Avant de commencer, nous avons besoins de plusieurs informations concernant la base de données. Vous allez devoir connaître les informations suivantes avant de lancer l'installation.</p>
			<ul>
				<li>Database type (MySQL, PostgreSQL ou SQLite)</li>
				<li>Database host</li>
				<li>Database name</li>
				<li>Database username</li>
				<li>Database password</li>
				<li>Table prefix (si vous voulez installez plusieurs Mibbo sur la même database) </li>
			</ul>
			<p>
				Dans tous les cas, les informations concernant votre base de données sont fournies par votre hébergeur.
				Si vous n'avez pas ces informations, vous devez le contacter avant de continuer.
			</p>

			<h2><?php _ei('Vérification du serveur'); ?></h2>
			<div id="phpinfo">
<?php
				$phpinfo = mib_phpinfo();

				foreach ( $phpinfo as $section => $section_values ) {
?>
					<h5><?php e_html($section); ?></h5>
					<div class="values">
<?php
					foreach ( $section_values as $value_name => $value ) {
?>
						<div class="row">
							<div class="name"><?php echo mib_trim($value_name); ?></div>
							<div class="value">
<?php
								// Local VS Master
								if ( is_array($value) ) {
									$value[0] = str_replace(array(',',';'), array(', ','; '), $value[0]);
									$value[1] = str_replace(array(',',';'), array(', ','; '), $value[1]);
									echo "\t\t\t\t\t\t\t";
									echo '<span class="local">'.$value[0].'</span>';
									echo ' ['.$value[1].']';
									echo "\n";
								}
								else {
									$value = str_replace(array(',',';'), array(', ','; '), $value);
									echo "\t\t\t\t\t\t\t".$value."\n";
								}
?>
							</div>
						</div>
<?php
					}
?>
					</div>
<?php
				}
?>
			</div>

			<div class="block-message">
				<p>
					<strong>Si pour une raison ou une autre, la création automatique du fichier <code>config.php</code> ne marche pas,
					ne vous inquiétez pas. Vous pourrez télécharger le fichier de configuration.
					Vous aurez simplement à enregistrer le fichier <code>config.php</code> dans le répertoire
					<code>website</code> de Mibbo.</strong>
				</p>
			</div>
			<p><a href="?step=config" class="button"><span class="ico-wand-hat"><?php _ei('Démarrer l\'assistant d\'installation'); ?></span></a></p>
		</article>
	</body>
</html>
<?php
	exit;
}
// ÉTAPE 1
else if ( $step == 1 ) {
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo mib_sprintftpl(__i('Installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME.' '.MIB_SYS_VERSION)); ?></title>
		<link rel="stylesheet" href="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/admin/install.css" type="text/css">
	</head>
	<body>
		<header>
			<a href="http://mibbo.net" target="_blank"><img style="max-width:300px;" src="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/img/logo-mibbo-full.png" alt="<?php echo MIB_SYS_NAME.' '.MIB_SYS_VERSION; ?>"></a>
		</header>
		<article>
			<?php if ( !empty($errors) ): ?>
			<div class="block-message error">
				<h5><?php _ei('Oops... Petit problème !'); ?></h5>
				<p><?php _ei('Merci de résoudre les erreurs rencontrées pour pouvoir continuer l\'installation.'); ?></p>
			</div>
			<?php endif; ?>

			<h2><?php _ei('Connexion à la base de données'); ?></h2>
			<p>Veuillez indiquer ci-dessous les information de connexion à votre base de données. Si vous n'êtes pas sur, contactez votre hébergeur.</p>
			<div class="block-message">
				<p><?php _ei('Pour vous connecter à votre base de données, évitez l\'utilisation de l\'utilisateur root.'); ?></p>
				<p><?php _ei('Pour plus de sécurité, préférez un mot de passe robuste et différent de vos autres mots de passe FTP, SSH ou email...'); ?></p>
			</div>
			<form method="post" action="?step=gooooo" autocomplete="off">
				<input type="hidden" name="form_sent" value="1">
				<div class="fRow">
					<div class="fColL">
						<label for="db_type"><?php _ei('Type'); ?></label>
					</div>
					<div class="fColR">
						<select id="db_type" name="db_type">
							<option value=""></option>
							<?php foreach ( $db_extensions as $db_type ) echo '<option'.($form_inputs['db_type'] == $db_type[0] ? ' selected="selected" ' : ' ').'value="'.$db_type[0].'">'.mib_html($db_type[1]).'</option>'; ?>
						</select>
						<p><?php _ei('Sélectionnez un type de base de données.'); ?></p>
						<?php if ( !empty($errors['db_type']) ) { echo '<div class="block-message error"><p>'.$errors['db_type'].'</p></div>'; } ?>
						<?php if ( $dual_mysql ) { echo '<div class="block-message info"><p>'.__i('Votre environnement supporte plusieurs sortes de communication avec MySQL. Les deux options sont appelées "standard" et "improved". Si vous hésitez, essayez d\'abord avec "improved" et, s\'il y a une erreur, essayez avec "standard".').'</p></div>'; } ?>
						<?php if ( $mysql_innodb ) { echo '<div class="block-message info"><p>'.__i('Votre server MySQL peut supporter InnoDB. Cela peut être un bon choix pour les très gros sites avec un énorme trafic. Si vous hésitez, il est recommandé de ne pas utiliser InnoDB.').'</p></div>'; } ?>
					</div>
				</div>
			<div class="fRow">
				<div class="fColL">
					<label for="db_host"><?php _ei('Serveur'); ?></label>
				</div>
				<div class="fColR">
					<input name="db_host" id="db_host" class="ico-server" type="text" size="25" value="<?php e_html($form_inputs['db_host']); ?>" placeholder="localhost">
					<p><?php _ei('Exemple : localhost, mysql1.site.com ou 192.168.X.X'); ?></p>
					<?php if ( !empty($errors['db_host']) ) { echo '<div class="block-message error"><p>'.$errors['db_host'].'</p></div>'; } ?>
					<div class="block-message info">
						<p><?php _ei('Vous pouvez spécifier un port personnalisé si votre base de données n\'est pas accessible par le port par défaut. (exemple: "localhost:3580").'); ?></p>
					</div>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="db_name"><?php _ei('Nom'); ?></label>
				</div>
				<div class="fColR">
					<input name="db_name" id="db_name" class="ico-database" type="text" size="25" value="<?php e_html($form_inputs['db_name']); ?>">
					<p><?php echo mib_sprintftpl(__i('Le nom de votre base de données où vous souhaitez installer [[%sys_name%]].'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
					<?php if ( !empty($errors['db_name']) ) { echo '<div class="block-message error"><p>'.$errors['db_name'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="db_username"><?php _ei('Utilisateur'); ?></label>
				</div>
				<div class="fColR">
					<input name="db_username" id="db_username" class="ico-user-silhouette" type="text" size="25" value="<?php e_html($form_inputs['db_username']); ?>">
					<p><?php _ei('Utilisateur pouvant se connecter à votre base de données.'); ?></p>
					<?php if ( !empty($errors['db_username']) ) { echo '<div class="block-message error"><p>'.$errors['db_username'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="db_password"><?php _ei('Mot de passe'); ?></label>
				</div>
				<div class="fColR">
					<input name="db_password" id="db_password" class="ico-key" type="text" size="25" value="<?php e_html($form_inputs['db_password']); ?>">
					<p><?php _ei('Mot de passe de l\'utilisateur se connectant à votre base de données.'); ?></p>
					<?php if ( !empty($errors['db_password']) ) { echo '<div class="block-message error"><p>'.$errors['db_password'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="db_prefix"><?php _ei('Préfixe des tables'); ?></label>
				</div>
				<div class="fColR">
					<input name="db_prefix" id="db_prefix" type="text" size="25" value="<?php e_html($form_inputs['db_prefix']); ?>">
					<p><?php echo mib_sprintftpl(__i('Si vous voulez installer plusieurs [[%sys_name%]] sur la même base de données, changez cette configuration.'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
					<?php if ( !empty($errors['db_prefix']) ) { echo '<div class="block-message error"><p>'.$errors['db_prefix'].'</p></div>'; } ?>
				</div>
			</div>

			<h2><?php _ei('Configurations générales'); ?></h2>
			<p><?php echo mib_sprintftpl(__i('Les informations suivantes sont requises pour installer [[%sys_name%]] sur votre serveur et créer le fichier de configuration.'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
			<div class="block-message">
				<p><?php _ei('N\'ayez pas peur, vous pourrez toujours modifier ces informations plus tard depuis l\'interface administrateur du back office.'); ?></p>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="site_title"><?php _ei('Nom du site'); ?></label>
				</div>
				<div class="fColR">
					<input name="site_title" id="site_title" type="text" size="25" value="<?php e_html($form_inputs['site_title']); ?>">
					<p><?php _ei('Nom de référence du site. Ce champ ne doit pas contenir de HTML.'); ?></p>
					<?php if ( !empty($errors['site_title']) ) { echo '<div class="block-message error"><p>'.$errors['site_title'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="site_email"><?php _ei('E-mail de contact du site'); ?></label>
				</div>
				<div class="fColR">
					<input name="site_email" id="site_email" class="ico-mail" type="text" size="25" value="<?php e_html($form_inputs['site_email']); ?>">
					<p><?php _ei('Adresse e-mail de contact général utilisée lors de l\'envoi d\'e-mails par le site.'); ?></p>
					<?php if ( !empty($errors['site_email']) ) { echo '<div class="block-message error"><p>'.$errors['site_email'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="server_timezone"><?php _ei('Fuseau horaire'); ?></label>
				</div>
				<div class="fColR">
					<p>
						<select id="server_timezone" name="server_timezone">
						<option value=""></option>
<?php
						echo mib_date_timezones_select_options($form_inputs['server_timezone']);
?>
						</select>
					</p>
					<p><?php echo mib_sprintftpl(__i('Le fuseau horaire du serveur où est installé [[%sys_name%]].'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
					<?php if ( !empty($errors['server_timezone']) ) { echo '<div class="block-message error"><p>'.$errors['server_timezone'].'</p></div>'; } ?>
				</div>
			</div>

			<h2><?php _ei('Le super administrateur'); ?></h2>
			<p><?php _ei('Un administrateur va être créé pour pouvoir se connecter au back office et gérer l\'ensemble du site.'); ?></p>
			<div class="block-message">
				<p><?php _ei('Si vous souhaitez gérer le site à plusieurs, vous pourrez créer d\'autres administrateurs après l\'installation.'); ?></p>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="sa_name"><?php _ei('Nom'); ?></label>
				</div>
				<div class="fColR">
					<input name="sa_name" id="sa_name" class="ico-user-worker-boss" type="text" size="25" value="<?php e_html($form_inputs['sa_name']); ?>">
					<?php if ( !empty($errors['sa_name']) ) { echo '<div class="block-message error"><p>'.$errors['sa_name'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="sa_email"><?php _ei('E-mail'); ?></label>
				</div>
				<div class="fColR">
					<input name="sa_email" id="sa_email" class="ico-mail" type="text" size="25" value="<?php e_html($form_inputs['sa_email']); ?>">
					<?php if ( !empty($errors['sa_email']) ) { echo '<div class="block-message error"><p>'.$errors['sa_email'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="sa_password1"><?php _ei('Mot de passe'); ?></label>
				</div>
				<div class="fColR">
					<input name="sa_password1" id="sa_password1" class="ico-key" size="25" type="password" value="<?php e_html($form_inputs['sa_password1']); ?>">
					<?php if ( !empty($errors['sa_password1']) ) { echo '<div class="block-message error"><p>'.$errors['sa_password1'].'</p></div>'; } ?>
				</div>
			</div>
			<div class="fRow">
				<div class="fColL">
					<label for="sa_password2"><?php _ei('Confirmez le mot de passe'); ?></label>
				</div>
				<div class="fColR">
					<input name="sa_password2" id="sa_password2" class="ico-key" size="25" type="password" value="<?php e_html($form_inputs['sa_password2']); ?>">
					<?php if ( !empty($errors['sa_password2']) ) { echo '<div class="block-message error"><p>'.$errors['sa_password2'].'</p></div>'; } ?>
				</div>
			</div>

			<br>
			<h2><?php _ei('Vous êtes prêt ?'); ?></h2>
			<div class="block-message info">
				<h5><?php _ei('Ça y est presque !'); ?></h5>
				<p><?php echo mib_sprintftpl(__i('La dernière chose qu\'il reste à faire, avant de cliquer sur "[[%button_name%]]" est de croiser les doigts pour que tout fonctionne.'), array('button_name'=>mib_sprintftpl(__i('Lancer l\'installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME)))); ?></p>
				<p><?php echo mib_sprintftpl(__i('C\'est bon, vous croisez les doigts ? Vous pouvez maintenant cliquer sur "[[%button_name%]]".'), array('button_name'=>mib_sprintftpl(__i('Lancer l\'installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME)))); ?></p>
			</div>
			<p>
				<button class="button" type="submit"><span class="ico-rocket"><?php echo mib_sprintftpl(__i('Lancer l\'installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME)); ?></span></button>
				<label id="label_anonymous_report" for="anonymous_report"><input type="checkbox" name="anonymous_report" id="anonymous_report" value="1" checked> <?php echo mib_sprintftpl(__i('Aidez-nous à améliorer [[%sys_name%]] en nous envoyant un rapport d\'installation anonyme.'), array('sys_name'=>MIB_SYS_NAME)); ?></label>
			</p>
			</form>
		</article>
	</body>
</html>
<?php
	exit;
}
// ÉTAPE 2
else if( $step == 2 ) {

	// On commence une transaction
	$MIB_DB->start_transaction();

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'		=> false
			),
			'conf_type'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'		=> true
			),
			'conf_ref'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'		=> true
			),
			'conf_name'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'		=> true
			),
			'conf_value'	=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			)
		),
		'PRIMARY KEY'	=> array('id')
	);
	$MIB_DB->create_table('configs', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'g_id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'		=> false
			),
			'g_title'		=> array(
				'datatype'		=> 'VARCHAR(50)',
				'allow_null'		=> false,
				'default'		=> '\'\''
			),
			'g_bo_perms'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			)
		),
		'PRIMARY KEY'	=> array('g_id')
	);
	$MIB_DB->create_table('groups', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'		=> false,
				'default'		=> '1'
			),
			'ident'			=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'		=> false,
				'default'		=> '\'\''
			),
			'logged'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'		=> false,
				'default'		=> '0'
			),
			'idle'			=> array(
				'datatype'		=> 'TINYINT(1)',
				'allow_null'		=> false,
				'default'		=> '0'
			)
		),
		'UNIQUE KEYS'	=> array(
			'user_id_ident_idx'	=> array('user_id', 'ident')
		),
		'INDEXES'		=> array(
			'ident_idx'		=> array('ident'),
			'logged_idx'		=> array('logged')
		),
		'ENGINE'		=> 'HEAP'
	);

	if ( $form_inputs['db_type'] == 'mysql' || $form_inputs['db_type'] == 'mysqli' || $form_inputs['db_type'] == 'mysql_innodb' || $form_inputs['db_type'] == 'mysqli_innodb' ) {
		$schema['UNIQUE KEYS']['user_id_ident_idx'] = array('user_id', 'ident(25)');
		$schema['INDEXES']['ident_idx'] = array('ident(25)');
	}

	if ( $form_inputs['db_type'] == 'mysql_innodb' || $form_inputs['db_type'] == 'mysqli_innodb' )
		$schema['ENGINE'] = 'InnoDB';

	$MIB_DB->create_table('online', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'		=> false
			),
			'url'			=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'url_rewrited'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'title'			=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'meta_robots'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'meta_description'	=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'meta_keywords'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'sitemap_priority'	=> array(
				'datatype'		=> 'FLOAT',
				'allow_null'		=> false,
				'default'		=> '0'
			)
		),
		'PRIMARY KEY'	=> array('id')
	);
	$MIB_DB->create_table('urls', $schema);

	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'		=> false
			),
			'group_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'		=> false,
				'default'		=> '3'
			),
			'username'		=> array(
				'datatype'		=> 'VARCHAR(200)',
				'allow_null'		=> false,
				'default'		=> '\'\''
			),
			'password'		=> array(
				'datatype'		=> 'VARCHAR(40)',
				'allow_null'		=> false,
				'default'		=> '\'\''
			),
			'salt'			=> array(
				'datatype'		=> 'VARCHAR(12)',
				'allow_null'		=> true
			),
			'email'			=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'		=> false,
				'default'		=> '\'\''
			),
			'timezone'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'		=> true
			),
			'registered'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'		=> false,
				'default'		=> '0'
			),
			'registration_ip'	=> array(
				'datatype'		=> 'VARCHAR(39)',
				'allow_null'		=> false,
				'default'		=> '\'0.0.0.0\''
			),
			'last_visit'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'		=> false,
				'default'		=> '0'
			),
			'bo_perms'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'		=> true
			),
			'admin_note'		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'		=> true
			)
		),
		'PRIMARY KEY'	=> array('id'),
		'INDEXES'		=> array(
			'registered_idx'	=> array('registered'),
			'username_idx'		=> array('username')
		)
	);

	if ( $form_inputs['db_type'] == 'mysql' || $form_inputs['db_type'] == 'mysqli' || $form_inputs['db_type'] == 'mysql_innodb' || $form_inputs['db_type'] == 'mysqli_innodb' )
		$schema['INDEXES']['username_idx'] = array('username(8)');

	$MIB_DB->create_table('users', $schema);

	// heure du serveur
	mib_date_timezone_set($form_inputs['server_timezone']);
	$now = time();

	// INSERT LES GROUPES
	$query = array(
		'INSERT'	=> 'g_title, g_bo_perms',
		'INTO'		=> 'groups',
		'VALUES'	=> '\'Administrateurs\', NULL'
	);
	if ($form_inputs['db_type'] != 'pgsql') {
		$query['INSERT'] .= ', g_id';
		$query['VALUES'] .= ', 1';
	}
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$query = array(
		'INSERT'	=> 'g_title, g_bo_perms',
		'INTO'		=> 'groups',
		'VALUES'	=> '\'Invités\', NULL'
	);
	if ($form_inputs['db_type'] != 'pgsql') {
		$query['INSERT'] .= ', g_id';
		$query['VALUES'] .= ', 2';
	}
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$query = array(
		'INSERT'	=> 'g_title, g_bo_perms',
		'INTO'		=> 'groups',
		'VALUES'	=> '\'Membres\', NULL'
	);
	if ($form_inputs['db_type'] != 'pgsql') {
		$query['INSERT'] .= ', g_id';
		$query['VALUES'] .= ', 3';
	}
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	// INSERT URL OPTIMISER
	$query = array(
		'INSERT'	=> 'url, url_rewrited, title',
		'INTO'		=> 'urls',
		'VALUES'	=> '\'/\', \'/\', \'\''
	);
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$query = array(
		'INSERT'	=> 'url, url_rewrited, title',
		'INTO'		=> 'urls',
		'VALUES'	=> '\'fr\', \'fr\', \'Accueil\''
	);
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	// INSERT USER ET ADMIN
	$query = array(
		'INSERT'	=> 'group_id, username, password, email',
		'INTO'		=> 'users',
		'VALUES'	=> '2, \'Guest\', \'Guest\', \'Guest\''
	);
	if ($form_inputs['db_type'] != 'pgsql') {
		$query['INSERT'] .= ', id';
		$query['VALUES'] .= ', 1';
	}
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	$salt = mib_random_key(12);

	$query = array(
		'INSERT'	=> 'group_id, username, password, email, registered, registration_ip, last_visit, salt',
		'INTO'		=> 'users',
		'VALUES'	=> '1, \''.$MIB_DB->escape($form_inputs['sa_name']).'\', \''.mib_hmac($form_inputs['sa_password1'], $salt).'\', \''.$MIB_DB->escape($form_inputs['sa_email']).'\', '.$now.', \'127.0.0.1\', '.$now.', \''.$MIB_DB->escape($salt).'\''
	);
	$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	$new_uid = $MIB_DB->insert_id();

	// INSERT LES CONFIGS SYSTEM
	$config_system = array(
		'site_title'				=> "'".$MIB_DB->escape($form_inputs['site_title'])."'",
		'server_timezone'			=> "'".$MIB_DB->escape($form_inputs['server_timezone'])."'",
		'timeout_visit'				=> "'1800'",
		'timeout_online'			=> "'600'",
		'site_email'				=> "'".$MIB_DB->escape($form_inputs['site_email'])."'",
		'default_user_group'			=> "'3'"
	);
	foreach ($config_system as $conf_name => $conf_value) {
		$query = array(
			'INSERT'	=> 'conf_type, conf_name, conf_value',
			'INTO'		=> 'configs',
			'VALUES'	=> '\'system\', \''.$conf_name.'\', '.$conf_value.''
		);
		$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	}

	$MIB_DB->end_transaction();

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo mib_sprintftpl(__i('Installation de [[%sys_name%]]'), array('sys_name'=>MIB_SYS_NAME.' '.MIB_SYS_VERSION)); ?></title>
		<link rel="stylesheet" href="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/admin/install.css" type="text/css">
	</head>
	<body>
		<header>
			<a href="http://mibbo.net" target="_blank"><img style="max-width:300px;" src="<?php if( defined('MIB_BASE_URL') ) { echo MIB_BASE_URL.'/'; } ?>theme/default/img/logo-mibbo-full.png" alt="<?php echo MIB_SYS_NAME.' '.MIB_SYS_VERSION; ?>"></a>
		</header>
		<article>
<?php
		$anonymous_report = !empty($form_inputs['anonymous_report']) ? true : false;

		if ( $anonymous_report ) {
			$anonymous_report_content = array(
				'i'	=> '0',
				'v'	=> MIB_SYS_VERSION,
				'os'	=> PHP_OS,
				'php'	=> PHP_VERSION,
				'db'	=> $form_inputs['db_type'],
				'url'	=> MIB_BASE_URL,
				'sal'	=> 'none',
				'sab'	=> $_SERVER['HTTP_USER_AGENT'],
			);
		}

		/// génère les données du fichier config.php
		$config = generate_mib_config_file($form_inputs);

		// essaye d'écrire le fichier config.php sur le serveur
		$written = false;
		if ( is_writable(MIB_PATH_VAR) ) {
			$fh = @fopen(MIB_PATH_VAR.'config.php', 'wb');
			if ( $fh ) {
				fwrite($fh, $config);
				fclose($fh);
				$written = true;
			}
		}

		// SUCCES TOTAL :)
		if ( $written ) {
			if ( $anonymous_report ) { $anonymous_report_content['i'] = '2'; }
?>
			<h2><?php _ei('Installation terminée'); ?></h2>
			<div class="block-message success">
				<h5><?php _ei('Quel succès !'); ?></h5>
				<p><?php echo mib_sprintftpl(__i('[[%sys_name%]] a été correctement installé sur votre serveur.'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
			</div>
			<p><?php echo mib_sprintftpl(__i('Vous pouvez dès maintenant [[%go_to_admin%]] ou [[%go_to_website%]].'), array('go_to_admin'=>'<a href="./'.MIB_ADMIN_DIR.'">'.__i('vous connecter à l\'administration').'</a>','go_to_website'=>'<a href="./" target="_blank">'.__i('afficher le front office').'</a>')); ?></p>
<?php
		}
		// SUCCESS, mais il faut rajouter le fichier de config
		else {
			if ( $anonymous_report ) { $anonymous_report_content['i'] = '1'; }
?>
			<h2><?php _ei('Installation presque terminée'); ?></h2>
			<div class="block-message success">
				<h5><?php _ei('Quel succès !'); ?></h5>
				<p><?php echo mib_sprintftpl(__i('[[%sys_name%]] a été correctement installé sur votre serveur.'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
			</div>
			<div class="block-message">
				<h5><?php _ei('Attention !'); ?></h5>
				<p><?php _ei('Pour terminer l\'installation, vous devez cliquer sur le bouton ci-dessous pour télécharger un fichier nommé config.php'); ?></p>
				<p><?php echo mib_sprintftpl(__i('Ensuite vous devrez envoyer ce fichier dans le répertoire [[%folder_path%]] où vous avez installé [[%sys_name%]].'), array('folder_path'=>'<strong>'.MIB_PATH_VAR.'</strong>', 'sys_name'=>MIB_SYS_NAME)); ?></p>
			</div>
			<form method="post" action="?generate_config=true">
				<input type="hidden" name="generate_config" value="1">
<?php
				foreach ( $form_inputs as $k => $v )
					echo '<input type="hidden" name="'.$k.'" value="'.mib_html($v).'">';
?>
				<p><button class="button" type="submit"><span class="ico-disk"><?php _ei('Télécharger le fichier config.php'); ?></span></button></p>
				<br>
			</form>
			<p><?php echo mib_sprintftpl(__i('Dès que vous aurez envoyé le fichier config.php, [[%sys_name%]] sera complètement installé.'), array('sys_name'=>MIB_SYS_NAME)); ?></p>
			<p><?php echo mib_sprintftpl(__i('Vous pourrez alors [[%go_to_admin%]] ou [[%go_to_website%]].'), array('go_to_admin'=>'<a href="./'.MIB_ADMIN_DIR.'">'.__i('vous connecter à l\'administration').'</a>','go_to_website'=>'<a href="./" target="_blank">'.__i('afficher le front office').'</a>')); ?></p>
<?php
		}
?>
		</article>
<?php
	// utilise une iframe pour envoyer le rapport anonyme même si mib_get_remote_file() n'est pas disponible
	if ( $anonymous_report ) {
?>
		<iframe src="<?php echo MIB_SYS_WEBSITE_URL_API; ?>/install.json?<?php e_html(http_build_query($anonymous_report_content)); ?>" height="0" width="0" frameborder="0" scrolling="no" style="display: none;"></iframe>
<?php
	}
?>
	</body>
</html>
<?php
	exit;
}