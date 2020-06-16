<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
defined('MIB_MANAGE') or exit;

$errors = array();

// Vérifie le navigateur de l'utilisateur (Obligatoire pour le BO)
$MIB_PLUGIN['browser'] = mib_core_function('MIB_Browser');

// Navigateurs autorisés (avec version minimum)
$MIB_PLUGIN['browser_allowed'] = array(
	'firefox'	=> '15.0',
	'chrome'	=> '15.0',
	'opera'		=> '9.0',
	'msie'		=> '10.0',
	'safari'	=> '5.0',
);

// Le navigateur est t'il autorisé ?
if(array_key_exists($MIB_PLUGIN['browser']['name'], $MIB_PLUGIN['browser_allowed']) && version_compare($MIB_PLUGIN['browser']['version'], $MIB_PLUGIN['browser_allowed'][$MIB_PLUGIN['browser']['name']], '>='))
	$MIB_PLUGIN['user_agent_allowed'] = true; 
else
	$MIB_PLUGIN['user_agent_allowed'] = false; 

$MIB_PAGE['title'] = __('Connexion au Back Office');

// Login si bon user_agent et form_sent
if ($MIB_PLUGIN['user_agent_allowed'] && isset($_POST['form_sent'])) {

	$form_email = utf8_strtolower(mib_trim($_POST['login_email']));
	$form_password = mib_trim($_POST['login_password']);
	$save_pass = isset($_POST['login_remind']); 
	$authorized = false;

	// Si l'adresse email est valide
	if(mib_valid_email($form_email)) {

		// Sélectionne les infos correspondant à l'email
		$query = array(
			'SELECT'	=> 'u.id, u.group_id, u.password, u.salt',
			'FROM'		=> 'users AS u'
		);

		if (DB_TYPE == 'mysql' || DB_TYPE == 'mysqli' || DB_TYPE == 'mysql_innodb' || DB_TYPE == 'mysqli_innodb')
			$query['WHERE'] = 'email=\''.$MIB_DB->escape($form_email).'\'';
		else
			$query['WHERE'] = 'LOWER(email)=LOWER(\''.$MIB_DB->escape($form_email).'\')';

		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		list($user_id, $group_id, $db_password_hash, $salt) = $MIB_DB->fetch_row($result);

		if (!empty($db_password_hash)) {
			$sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
			$form_password_hash = mib_hmac($form_password, $salt);

			if ($sha1_in_db && $db_password_hash == $form_password_hash)
				$authorized = true;
			else if ((!$sha1_in_db && $db_password_hash == md5($form_password)) || ($sha1_in_db && $db_password_hash == sha1($form_password))) {
				$authorized = true;

				$salt = mib_random_key(12);
				$form_password_hash = mib_hmac($form_password, $salt);

				// There's an old MD5 hash or an unsalted SHA1 hash in the database, so we replace it
				// with a randomly generated salt and a new, salted SHA1 hash
				$query = array(
					'UPDATE'	=> 'users',
					'SET'		=> 'password=\''.$form_password_hash.'\', salt=\''.$MIB_DB->escape($salt).'\'',
					'WHERE'		=> 'id='.$user_id
				);

				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			}
		}

		// L'authentification est bonne
		if ($authorized) {
			// on test si l'utilisateur à accès au BO
			$errors = array_merge($errors, validate_bo_perms($user_id));

			if(empty($errors)) {
				// Supprime cet utilisateur "invité" de la "online list"
				$query = array(
					'DELETE'	=> 'online',
					'WHERE'		=> 'ident=\''.$MIB_DB->escape(mib_remote_address()).'\''
				);

				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

				$expire = ($save_pass) ? time() + 1209600 : time() + $MIB_CONFIG['timeout_visit'];
				mib_setcookie(COOKIE_NAME, base64_encode($user_id.'|'.$form_password_hash.'|'.$expire.'|'.sha1($salt.$form_password_hash.mib_hmac($expire, $salt))), $expire);

				// On redirige vers le BO
				mib_header(MIB_ADMIN_DIR.'/');
			}
			else // erreur de permissions du BO
				$authorized = false;
		}
	}

	if (!$authorized && empty($errors))
		$errors[] = __('Adresse e-mail et/ou mot de passe incorrect !');
}

// a t'on un logo personalisé pour le template par defaut ?
if ( file_exists(MIB_THEME_DIR.'img/logo-admin-full.png') )
	$logo = 'logo-admin-full-300px-max.png';
else if ( file_exists(MIB_THEME_DEFAULT_DIR.'img/logo-mibbo-full.png') )
	$logo = 'logo-mibbo-full-300px-max.png';
else
	$logo = false;

if( $logo ) {
?>
	<div id="logo">
		<a href="<?php echo $MIB_CONFIG['base_url']; ?>" /><img src="../../img/theme/<?php echo $logo; ?>" alt="<?php echo mib_html($MIB_CONFIG['site_title']); ?>"></a>
	</div>
<?php
}
?>
<div id="login" class="bgbox">
	<h1 class="grad"><span>Connexion au Back Office &rsaquo; <?php echo mib_html($MIB_CONFIG['site_title']); ?></span></h1>
	<div id="inlogin" class="bginbox bdinbox">
		<div class="message">
			<p><strong>Vous devez vous connecter pour accéder au back office.</strong></p>
			<p>Pour des raisons de sécurité, veuillez vous déconnecter et fermer votre navigateur lorsque vous avez fini d'accéder aux services protégés.</p>
		</div>
<?php
		if($MIB_PLUGIN['user_agent_allowed']) {
			// Si il y a des erreurs on les affiches
			if (!empty($errors)) {
				// Un petit sleep pour éviter les attaques de type "brutforce"
				if (!defined('MIB_DEBUG'))
					sleep(rand(1, 5));
?>
		<div class="message error">
			<p><?php echo implode('</p><p>',$errors); ?></p>
		</div>
<?php
			}
?>
		<form action="login" method="post" name="login">
			<input type="hidden" name="form_sent" value="1" />
			<div class="label">IP / Hôte :</div>
			<div class="input_label"><p>
<?php
			$ip = mib_remote_address();
            $host = '';
			if (@preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ip)) {
				$host = @gethostbyaddr($ip);
			}
			echo $ip;
			if($host != $ip)
				echo '<br><small>'.$host.'</small>';
			else
				echo '<br><small>Nom d\'hôte inconnu.</small>';
?>
			</p></div>
			<hr class="clear">
			<label for="login_email" class="label">Adresse e-mail :</label>
			<div class="input_label">
				<input name="login_email" id="login_email" class="input" type="text" value="<?php if (isset($_POST['login_email'])) { echo mib_html($_POST['login_email']); } ?>" size="25" maxlength="80"  />
			</div>
			<hr class="clear">
			<label for="login_password" class="label">Mot de passe :</label>
			<div class="input_label">
				<input name="login_password" id="login_password" class="input" type="password" value="" size="25" maxlength="25"  />
			</div>
			<hr class="clear">
			<label class="label">
				<input type="checkbox" name="login_remind" id="login_remind" value="1" <?php if (isset($_POST['login_remind']) && intval($_POST['login_remind']) == '1') { echo 'checked="checked"'; } ?>  />
			</label>
			<div class="input_label">
				<label for="login_remind">Mémoriser mes informations sur cet ordinateur.</label>
			</div>
			<hr class="clear">
			<div class="label"></div>
			<div class="input_label">
				<button type="submit" class="button" ><span>Connexion</span></button>
			</div>
			<hr class="clear">
		</form>
		<script type="text/javascript">
			window.onload = function(){
				document.login.login_email.focus();
			}
		</script>
<?php
		}
		else {
?>
		<div class="message error">
			<p><strong>Votre navigateur n'est pas à jour !</strong></p>
			<p>Pour des raisons de sécurité, veuillez utiliser un navigateur plus récent pour vous connecter au Back Office.</p>
		</div>
		<p style="text-align: center;">
			<a href="http://www.mozilla.com/firefox/" target="_blank"><img src="../../<?php echo MIB_THEME_DEFAULT_DIR; ?>/admin/img/firefox_btn.gif" alt="Mozilla Firefox" /></a>
			<a href="http://www.google.com/chrome/" target="_blank"><img src="../../<?php echo MIB_THEME_DEFAULT_DIR; ?>/admin/img/chrome_btn.gif" alt="Google Chrome"/></a>
			<a href="http://www.apple.com/safari/" target="_blank"><img src="../../<?php echo MIB_THEME_DEFAULT_DIR; ?>/admin/img/safari_btn.gif" alt="Apple Safari"/></a>
			<a href="http://www.opera.com/browser/" target="_blank"><img src="../../<?php echo MIB_THEME_DEFAULT_DIR; ?>/admin/img/opera_btn.gif" alt="Opera Browser"/></a>
			<!--<a href="http://www.microsoft.com/windows/internet-explorer/default.aspx" target="_blank"><img src="../../<?php echo MIB_THEME_DEFAULT_DIR; ?>/admin/img/ie8_btn.gif" alt="Microsoft Internet Explorer"/></a>!-->
		</p>

<?php
		}
?>
		<p class="user_agent"><?php echo $MIB_PLUGIN['browser']['userAgent']; ?></p>
	</div>
</div>
<?php

echo '<p class="2boandco">'.sprintf(__('Une réalisation %1$s - Propulsé par %2$s'), '<a target="_blank" class="ico-2boandco" href="//2boandco.com">2BO&amp;CO</a>', '<a class="ico-mibbo" target="_blank" href="//mibbo.net">Mibbo</a>').'</p>';