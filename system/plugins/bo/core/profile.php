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

// Requète JSON
if(defined('MIB_JSON')) {

	// Vérification du mot de passe envoyé par secure prompt
	if(isset($_POST['secure']) && mib_hmac(mib_trim($_POST['secure']), $MIB_USER['salt']) == $MIB_USER['password']) {

		// On vérifie si on à la bonne URL sans "truc" en plus !
		mib_confirm_current_url($MIB_PAGE['base_url'].'/json/bo/profile');

		$errors = array();

		$form['username'] = isset($_POST['username']) ? mib_trim($_POST['username']) : '';
		// Convertit les caractères blancs multiples en un seul (pour prévenir des personnes qui s'inscrive avec des nom d'utilisateur indistingable)
		$form['username'] = preg_replace('#\s+#s', ' ', $form['username']);

		$form['email'] = isset($_POST['email']) ? utf8_strtolower(mib_trim($_POST['email'])) : '';
		$form['timezone'] = mib_trim($_POST['timezone']);

		// Valide le nom d'utilisateur
		$errors = array_merge($errors, validate_username($form['username'], $MIB_USER['id']));

		// Valide l'adresse e-mail
		if (empty($errors))
			$errors = array_merge($errors, validate_email($form['email'], $MIB_USER['id']));

		// Vérification des mots de passes
		$new_password = mib_trim($_POST['password']);
		$new_password_verif = mib_trim($_POST['password_verif']);

		if(!empty($new_password)) {
			if (utf8_strlen($new_password) < 6)
				$errors[] = __('Le mot de passe doit être constitué d\'au moins 6 caractères.');
			else if ($new_password != $new_password_verif)
				$errors[] = __('Les mots de passe ne correspondent pas.');
			else
				$form['password'] =mib_hmac($new_password, $MIB_USER['salt']);
		}

		// Singlequotes around non-empty values and NULL for empty values
		$temp = array();
		foreach ($form as $key => $input) {
			$value = ($input !== '') ? '\''.$MIB_DB->escape($input).'\'' : 'NULL';
			$temp[] = $key.'='.$value;
		}
		if (empty($temp))
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		// Aucune erreur
		if (empty($errors)) {
			// Effectue la mise à jour
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> implode(',', $temp),
				'WHERE'		=> 'id='.$MIB_USER['id']
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

			// Changement de mot de passe, on regénere le cookie, en fesant attention à l'expiration existante du cookie actuel
			if(isset($form['password'])) {
				$cookie_data = @explode('|', base64_decode($_COOKIE[COOKIE_NAME]));

				$expire = ($cookie_data[2] > time() + $MIB_CONFIG['timeout_visit']) ? time() + 1209600 : time() + $MIB_CONFIG['timeout_visit'];
				mib_setcookie(COOKIE_NAME, base64_encode($MIB_USER['id'].'|'.$form['password'].'|'.$expire.'|'.sha1($MIB_USER['salt'].$form['password'].mib_hmac($expire, $MIB_USER['salt']))), $expire);
			}

			$MIB_PLUGIN['json']['title'] = __('Modifications effectuées');
			$MIB_PLUGIN['json']['value'] =  __('Vos informations personnelles on été enregistrées.');
			$MIB_PLUGIN['json']['options']['type'] = 'valid';

			if($form['username'] != $MIB_USER['username']) // Le nom d'utilisateur a changé
				$MIB_PLUGIN['json']['element']['MIB_user_username']['set']['html'] = mib_html($form['username']);
			if($form['email'] != $MIB_USER['email']) // L'adresse email a changée
				$MIB_PLUGIN['json']['element']['MIB_user_email']['set']['html'] = mib_html($form['email']);
			if($form['timezone'] != $MIB_USER['timezone']) // Le timezone a changé
				$MIB_PLUGIN['json']['page']['update'] = 'bo/profile';
		}
		else
			error('<p>'.implode('</p><p>', $errors).'</p>');
	}
	else
		error(__('Mot de passe incorrect !'));

	define('MIB_JSONED', 1);
	return;
}
// Requète AJAX
else if(defined('MIB_AJAX'))
	define('MIB_AJAXED', 1);

?>
<fieldset><legend>Editez vos informations personnelles</legend>
	<div class="message">
		<p><strong>Ces informations sont strictement personnelles et ne seront en aucun cas communiquées.</strong></p>
		<p>Votre mot de passe est automatiquement crypté. Il est personnel, veuillez ne le communiquez à personne. Si vous l'avez oublié, contactez l'administrateur du site pour qu'il vous génère un nouveau mot de passe.</p>
	</div>
	<form prompt="Par mesure de sécuritée, saisissez votre mot de passe actuel pour effectuer cette action.::Saisissez votre mot de passe::secure" method="post" action="bo/profile" target="_json">
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_username">Nom d'utilisateur :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_username" name="username" value="<?php echo mib_html($MIB_USER['username']); ?>" size="35" maxlength="50" />
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_email">Adresse e-mail :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_email" name="email" value="<?php echo mib_html($MIB_USER['email']); ?>" size="35" maxlength="80" />
				<p>Saisissez une adresse e-mail valide (utilisée lors de la connexion au Back Office).</p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_password">Nouveau mot de passe :</label></div>
			<div class="option-item">
				<input type="password" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_password" name="password" value="" size="35" maxlength="40" />
				<input type="password" class="input" name="password_verif" value="" size="35" maxlength="40" />
				<p>Laissez vide pour ne pas modifier le mot de passe actuel. Répétez le mot de passe.</p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_timezone">Fuseau horaire :</label></div>
			<div class="option-item">
				<select class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_timezone" name="timezone">
				<?php echo mib_date_timezones_select_options($MIB_USER['timezone']); ?>
				</select>
				<p>Afin que le Back Office affiche l'heure correctement pour cet utilisateur, sélectionnez un fuseau horaire.</p>
				<p><?php echo format_time(time(), __('Y-m-d'), true); ?> - <?php echo format_time(time(), __('H:i'), true); ?></p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="save">Enregistrer</span></button>
		</div>
	</form>
</fieldset>
