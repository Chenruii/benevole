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

if (isset($MIB_PAGE['info'])) {
	$MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info']);

	$MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "remove", "create", "edit"
	$MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id de l'utilisateur"
}

// Requète JSON
if(defined('MIB_JSON')) {
	// L'utilisateur a t'il les permissions d'écriture ?
	if(!$MIB_USER['can_write_plugin'])
		error(__('Vous n\'avez pas la permission d\'effectuer cette action.'));

	// Créer un utilisateur
	if($MIB_PLUGIN['action'] == 'create') {
		// On vérifie si on à la bonne URL sans "truc" en plus !
		mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/create');

		$errors = array();

		$username = isset($_POST['username']) ? mib_trim($_POST['username']) : '';
		$email = isset($_POST['email']) ? strtolower(mib_trim($_POST['email'])) : '';

		// Valide le nom d'utilisateur
		$errors = array_merge($errors, validate_username($username));

		// Valide l'adresse e-mail
		if (empty($errors))
			$errors = array_merge($errors, validate_email($email));

		// Aucune erreur
		if (empty($errors)) {
			$password = mib_random_key(8, true);
			$salt = mib_random_key(12);
			$password_hash = mib_hmac($password, $salt);
			$intial_group_id = $MIB_CONFIG['default_user_group'];

			// Ajoute l'utilisateur
			$query = array(
				'INSERT'	=> 'username, group_id, password, salt, email, registered',
				'INTO'		=> 'users',
				'VALUES'	=> '\''.$MIB_DB->escape($username).'\', '.$intial_group_id.', \''.$MIB_DB->escape($password_hash).'\', \''.$MIB_DB->escape($salt).'\', \''.$MIB_DB->escape($email).'\', '.$MIB_PAGE['time'].''
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			$new_uid = $MIB_DB->insert_id();

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('Utilisateur ajouté'),
				'value'		=> __('L\'utilisateur <a href="'.$MIB_PLUGIN['name'].'/edit/'.$new_uid.'" title="'.mib_html($username).'" ><strong>'.mib_html($username).'</strong></a> a été ajouté avec succès.'),
				'options'		=> array(
					'type'		=> 'valid',
					'duration'	=> -1
				),
				'page'		=> array(
						'update'		=> $MIB_PLUGIN['name']
				)
			);
		}
		else
			error('<p>'.implode('</p><p>', $errors).'</p>');

	}
	else if($MIB_PLUGIN['action'] == 'remove' || $MIB_PLUGIN['action'] == 'edit') {
		$user_id = intval($MIB_PLUGIN['id']);

		if($user_id < 2)
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$query = array(
			'SELECT'	=> 'u.*',
			'FROM'		=> 'users AS u',
			'WHERE'		=> 'u.id='.$user_id
		);
		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		if (!$MIB_DB->num_rows($result))
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$cur_user = $MIB_DB->fetch_assoc($result);

		// Edition d'un utilisateur
		if($MIB_PLUGIN['action'] == 'edit') {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/edit/'.$user_id);

			$errors = array();

			$form['username'] = isset($_POST['username']) ? mib_trim($_POST['username']) : '';
			// Convertit les caractères blancs multiples en un seul (pour prévenir des personnes qui s'inscrive avec des nom d'utilisateur indistingable)
			$form['username'] = preg_replace('#\s+#s', ' ', $form['username']);

			$form['email'] = isset($_POST['email']) ? utf8_strtolower(mib_trim($_POST['email'])) : '';
			$form['timezone'] = mib_trim($_POST['timezone']);

			// Valide le groupe
			$form['group_id'] = intval($_POST['group_id']);
			if($form['group_id'] < 1 || $form['group_id'] == MIB_G_GUEST)
				error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

			// Autorise les IP
			$bo_ip = array();

			// Autorise les plugins
			$bo_plugins = array();
			if($form['group_id'] == $cur_user['group_id']) { // le groupe ne change pas, on attribut les permissions
				$_POST['BO_plugins'] = isset($_POST['BO_plugins']) && is_array($_POST['BO_plugins']) ? $_POST['BO_plugins'] : array();
				foreach($_POST['BO_plugins'] as $plugin_name => $plugin_value) {
					$plugin_name = mib_trim($plugin_name);

					$bo_plugins[$plugin_name] = array(
						'read'	=> (isset($plugin_value['read']) ? intval($plugin_value['read']) : 0),
						'write'	=> (isset($plugin_value['write']) ? intval($plugin_value['write']) : 0),
					);
				}
			}

			if(!empty($bo_ip) || !empty($bo_plugins))
				$form['bo_perms'] = base64_encode(serialize($bo_plugins).'|'.serialize($bo_ip));
			else
				$form['bo_perms'] = ''; // Rezet les permissions

			$form['admin_note'] = mib_trim($_POST['admin_note']);

			// Valide le nom d'utilisateur
			$errors = array_merge($errors, validate_username($form['username'], $cur_user['id']));

			// Valide l'adresse e-mail
			if (empty($errors))
				$errors = array_merge($errors, validate_email($form['email'], $cur_user['id']));

			// Vérification des mots de passes
			$new_password = mib_trim($_POST['password']);
			$new_password_verif = mib_trim($_POST['password_verif']);

			if(!empty($new_password)) {
				if (utf8_strlen($new_password) < 6)
					$errors[] = __('Le mot de passe doit être constitué d\'au moins 6 caractères.');
				else if ($new_password != $new_password_verif)
					$errors[] = __('Les mots de passe ne correspondent pas.');
				else
					$form['password'] =mib_hmac($new_password, $cur_user['salt']);
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
					'WHERE'		=> 'id='.$cur_user['id']
				);
				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Modifications effectuées'),
					'value'		=> __('L\'utilisateur a été mis à jour avec succès.'),
					'options'		=> array('type' => 'valid'),
					'page'		=> array(
							'update'		=> $MIB_PLUGIN['name'] // Il faut update pour regénérer la liste des utilisateurs
					)
				);

				if($form['username'] != $cur_user['username']) // Le nom d'utilisateur a changé
					$MIB_PLUGIN['json']['element'][$MIB_PLUGIN['name'].'_'.$cur_user['id'].'_title']['set']['html'] = mib_html('Utilisateur : '.$form['username']);

				if($form['group_id'] != $cur_user['group_id']) // Le groupe a changé
					$MIB_PLUGIN['json']['page']['update'] = $MIB_PLUGIN['name'].'/edit/'.$user_id.','.$MIB_PLUGIN['name'];
			}
			else
				error('<p>'.implode('</p><p>', $errors).'</p>');
		}
		// Suppression d'un utilisateur
		else if($MIB_PLUGIN['action'] == 'remove' && isset($_POST['secure'])) {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/remove/'.$user_id);

			// Vérification du MDP
			if(mib_hmac(mib_trim($_POST['secure']), $MIB_USER['salt']) == $MIB_USER['password']) {
				// Supprime l'utilisateur
				$query = array(
					'DELETE'	=> 'users',
					'WHERE'		=> 'id='.$user_id
				);
				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Suppression effectuée'),
					'value'		=> __('L\'utilisateur a été supprimé avec succès.'),
					'options'		=> array('type' => 'valid'),
					'page'		=> array(
							'update'		=> $MIB_PLUGIN['name'],
							'remove'		=> $MIB_PLUGIN['name'].'/edit/'.$user_id
					)
				);
			}
			else
				error(__('Mot de passe incorrect !'));
		}
	}

	define('MIB_JSONED', 1);
	return;
}
// Requète AJAX
else if(defined('MIB_AJAX'))
	define('MIB_AJAXED', 1);

// Edition d'un utilisateur
if($MIB_PLUGIN['action'] == 'edit') {
	$user_id = intval($MIB_PLUGIN['id']);

	if($user_id < 2)
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$query = array(
		'SELECT'	=> 'u.*, g.*, o.user_id AS is_online',
		'FROM'		=> 'users AS u',
		'JOINS'		=> array(
			array(
				'LEFT JOIN'		=> 'groups AS g',
				'ON'			=> 'g.g_id=u.group_id'
			),
			array(
				'LEFT JOIN'		=> 'online AS o',
				'ON'			=> 'o.user_id=u.id AND o.user_id!=1 AND o.idle=0'
			)
		),
		'WHERE'		=> 'u.id='.$user_id
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	if (!$MIB_DB->num_rows($result))
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$cur_user = $MIB_DB->fetch_assoc($result);

	if(!empty($cur_user['g_bo_perms'])) { // Le groupe de l'utilisateur a des permissions
		@list($cur_user['g_bo_perms_plugins'], $cur_user['g_bo_perms_horaire']) = @explode('|', base64_decode($cur_user['g_bo_perms']));
		$cur_user['g_bo_perms_plugins'] = @unserialize($cur_user['g_bo_perms_plugins']);
		$cur_user['g_bo_perms_horaire'] = @unserialize($cur_user['g_bo_perms_horaire']);
	}
	// on valide que les infos de la db sont correctes
	$cur_user['g_bo_perms_plugins'] = (isset($cur_user['g_bo_perms_plugins']) && is_array($cur_user['g_bo_perms_plugins'])) ? $cur_user['g_bo_perms_plugins'] : array();
	$cur_user['g_bo_perms_horaire'] = (isset($cur_user['g_bo_perms_horaire']) && is_array($cur_user['g_bo_perms_horaire'])) ? $cur_user['g_bo_perms_horaire'] : array();

	if(!empty($cur_user['bo_perms'])) { // L'utilisateur a des permissions
		@list($cur_user['bo_perms_plugins'], $cur_user['bo_perms_ip']) = @explode('|', base64_decode($cur_user['bo_perms']));
		$cur_user['bo_perms_plugins'] = @unserialize($cur_user['bo_perms_plugins']);
		$cur_user['bo_perms_ip'] = @unserialize($cur_user['bo_perms_ip']);
	}
	// on valide que les infos de la db sont correctes
	$cur_user['bo_perms_plugins'] = (isset($cur_user['bo_perms_plugins']) && is_array($cur_user['bo_perms_plugins'])) ? $cur_user['bo_perms_plugins'] : array();
	$cur_user['bo_perms_ip'] = (isset($cur_user['bo_perms_ip']) && is_array($cur_user['bo_perms_ip'])) ? $cur_user['bo_perms_ip'] : array();

?>
	<style type="text/css">
		#MIB_page .button .edit_user { background-image: url('{{tpl:MIB_PLUGIN}}/img/user__pencil.png'); }
		#MIB_page .button .remove_user { background-image: url('{{tpl:MIB_PLUGIN}}/img/user__minus.png'); }
	</style>
	<form id="<?php echo $MIB_PAGE['uniqid']; ?>_form" method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_user['id']; ?>" target="_json">
	<fieldset><legend id="<?php echo $MIB_PLUGIN['name']; ?>_<?php echo $cur_user['id']; ?>_title"><?php echo mib_html('Utilisateur : '.$cur_user['username']); ?></legend>
		<div class="option-row">
			<div class="option-title">Réf :</div>
			<div class="option-item">
				<p><a href="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_user['id']; ?>"><?php echo $cur_user['id']; ?></a></p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_username">Nom d'utilisateur :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_username" name="username" value="<?php echo mib_html($cur_user['username']); ?>" size="35" maxlength="50" />
				<p>Le nom d'utilisateur doit être unique.</p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_email">Adresse e-mail :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_email" name="email" value="<?php echo mib_html($cur_user['email']); ?>" size="35" maxlength="80" />
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
				<option value=""></option>
				<?php echo mib_date_timezones_select_options($cur_user['timezone']); ?>
				</select>
				<p>Afin que le Back Office affiche l'heure correctement pour cet utilisateur, sélectionnez un fuseau horaire. Laissez vide pour utiliser le fuseau horaire du serveur.</p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_group">Groupe :</label></div>
			<div class="option-item">
				<select name="group_id" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_group" >
					<?php
						$query = array(
							'SELECT'	=> 'g.g_id, g.g_title',
							'FROM'		=> 'groups AS g',
							'WHERE'		=> 'g_id!='.MIB_G_GUEST,
							'ORDER BY'	=> 'g.g_title'
						);
						$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
						while ($cur_group = $MIB_DB->fetch_assoc($result)) {
							if ($cur_group['g_id'] == $cur_user['g_id'])
								echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.mib_html($cur_group['g_title']).'</option>'."\n";
							else
								echo '<option value="'.$cur_group['g_id'].'">'.mib_html($cur_group['g_title']).'</option>'."\n";
						}
					?>
				</select>
				<script type="text/javascript">
					$('<?php echo $MIB_PAGE['uniqid']; ?>_group').addEvent('change', function() {
						MIB_Bo.execute($('<?php echo $MIB_PAGE['uniqid']; ?>_form'));
					});
				</script>
				<p>Choisissez un groupe pour cet utilisateur. Chaque groupe a des permissions spécifiques.</p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="edit_user">Enregistrer</span></button>
		</div>
	</fieldset>

	<?php if ($cur_user['g_id'] != MIB_G_ADMIN && array_key_exists('bo', $cur_user['g_bo_perms_plugins'])): ?>
	<style type="text/css">
		.<?php echo $MIB_PLUGIN['name']; ?>-edit .tc0 {
			width: 60px;
			text-align: center;
		}
		.<?php echo $MIB_PLUGIN['name']; ?>-edit .tc2, .<?php echo $MIB_PLUGIN['name']; ?>-edit .tc3, .<?php echo $MIB_PLUGIN['name']; ?>-edit .tc4 {
			width: 120px;
		}
	</style>
	<fieldset><legend>Accès aux extensions</legend>
		<div class="message">
			<p>Autorise l'accès de l'utilisateur aux extensions du Back Office sélectionnées. Certains accès sont prédéfinis par le groupe de l'utilisateur.</p>
			<p> - <strong>Lecture :</strong> Accès en lecture seule.</p>
			<p> - <strong>Ecriture :</strong> Accès en écriture, peut modifier des informations.</p>
			<p> - <strong>Configuration :</strong> Accès aux configurations de l'extension, peut configurer l'extension.</p>
		</div>
<?php
		$admin_cats = get_BO_cat(); // Chargement des catégories
		$admin_plugins = get_plugin(); // Chargement des Plugins

		foreach ($admin_cats as $cat_uid => $cur_cat) {
			$num_plugins_in_cat = 0;
			foreach ($admin_plugins as $cur_plugin) {
				if(isset($cur_plugin['categorie']) && $cur_plugin['categorie'] == $cat_uid && empty($cur_plugin['error']) && !empty($cur_plugin['admin_manage'])) { // Aucune erreur dans le plugin
					$num_plugins_in_cat++;
				}
			}
			if ($num_plugins_in_cat > 0) { //Si la catégorie contient des plugins valides
?>
				<div class="option-row">
				<div class="option-title"><?php echo mib_html($cur_cat['title']); ?> :</div>
				<div class="option-item">

				<table class="table-results mg0 <?php echo $MIB_PLUGIN['name']; ?>-edit">
				<thead>
					<tr>
						<th colspan="4" class="tcl tcr"><?php echo mib_html($cur_cat['description']); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
				foreach ($admin_plugins as $cur_plugin) {
					if(isset($cur_plugin['categorie']) && $cur_plugin['categorie'] == $cat_uid && empty($cur_plugin['error']) && !empty($cur_plugin['admin_manage'])) { // Aucune erreur dans le plugin
						$col = 0;
?>
				<tr>
					<td class="tc<?php echo $col++; ?> tcl">
					<?php
						if(is_valid_bo_plugin($cur_plugin['uid']))
							echo '<span class="tags valid">active</span>';
						else
							echo '<span class="tags error">inactive</span>';
					?>
					</td>
					<td class="tc<?php echo $col++; ?> tips" title="<?php echo mib_html($cur_plugin['title']); ?>" rel="<?php echo mib_html($cur_cat['description']); ?>">
						<strong <?php if(!empty($cur_plugin['favicon'])) echo 'class="icontxt" style="background-image:url(\'../../'.$cur_plugin['favicon'].'\');"'; ?> ><?php echo mib_html($cur_plugin['title']); ?> :</strong>
					</th>
					<td class="tc<?php echo $col++; ?>">
						<input <?php if(isset($cur_user['g_bo_perms_plugins'][$cur_plugin['uid']]['read']) && $cur_user['g_bo_perms_plugins'][$cur_plugin['uid']]['read'] == 1) { echo 'checked="checked" disabled="disabled"'; } else if(isset($cur_user['bo_perms_plugins'][$cur_plugin['uid']]['read']) && $cur_user['bo_perms_plugins'][$cur_plugin['uid']]['read'] == 1) { echo 'checked="checked"'; } ?> type="checkbox" name="BO_plugins[<?php echo $cur_plugin['uid']; ?>][read]" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_read" />
						<label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_read">Lecture</label>
					</td>
					<td class="tc<?php echo $col++; ?> tcr">
						<input <?php if(isset($cur_user['g_bo_perms_plugins'][$cur_plugin['uid']]['write']) && $cur_user['g_bo_perms_plugins'][$cur_plugin['uid']]['write'] == 1) { echo 'checked="checked" disabled="disabled"'; } else if(isset($cur_user['bo_perms_plugins'][$cur_plugin['uid']]['write']) && $cur_user['bo_perms_plugins'][$cur_plugin['uid']]['write'] == 1) { echo 'checked="checked"'; } ?> type="checkbox" name="BO_plugins[<?php echo $cur_plugin['uid']; ?>][write]" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_write" />
						<label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_write">Écriture</label>
					</td>
				</tr>
<?php
					}
				}
?>
				</tbody>
				</table>
				</div>
				</div>
<?php
			}
		}
?>
		<div class="option-row">
			<div class="option-item"><button type="submit" class="button" ><span class="edit_user">Enregistrer</span></button></div>
		</div>
	</fieldset>
	<?php endif; ?>

	<fieldset><legend>Activité</legend>
		<div class="option-row">
			<div class="option-title">Date d'inscription :</div>
			<div class="option-item">
				<p><?php echo format_time($cur_user['registered']); ?></p>
				<?php
					if ($cur_user['registration_ip'] != '0.0.0.0')
						echo '<p>Adresse IP d\'inscription : <a href="bo/host/?ip='.mib_html($cur_user['registration_ip']).'" target="_json">'.mib_html($cur_user['registration_ip']).'</a></p>';
				?>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title">Dernière visite :</div>
			<div class="option-item">
				<p><?php echo format_time($cur_user['last_visit']); ?></p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_note">Note administrateur :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_note" name="admin_note" value="<?php echo mib_html($cur_user['admin_note']); ?>" size="35" maxlength="30" />
				<p>En tant qu'administrateur vous pouvez préciser une note concernant l'utilisateur uniquement visible depuis le Back Office.</p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="edit_user">Enregistrer</span></button>
		</div>
	</fieldset>
	</form>

	<form prompt="Par mesure de sécuritée, saisissez votre mot de passe actuel pour effectuer cette action.::Supprimer <?php echo mib_html($cur_user['username']); ?> ?::secure" method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/remove/<?php echo $cur_user['id']; ?>" target="_json" >
	<fieldset><legend>Supprimer l'utilisateur</legend>
		<div class="message">
			<p><strong>Attention</strong> : la supression d'un utilisateur est définitive.</p>
		</div>
		<div class="option-row">
			<div class="option-title">Utilisateur à supprimer :</div>
			<div class="option-item">
				<p><?php echo mib_html($cur_user['username']); ?></p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="remove_user">Supprimer l'utilisateur</span></button>
		</div>
	</fieldset>
	</form>
<?php

	return;
}

?>
<style type="text/css">
	#MIB_page .button .add_user { background-image: url('{{tpl:MIB_PLUGIN}}/img/user__plus.png'); }
</style>

<fieldset class="toggle"><legend>Ajouter un utilisateur</legend>
	<div class="message">
		<p>L'adresse e-mail doit être unique. Un mot de passe sera genéré aléatoirement pour cet utilisateur.</p>
	</div>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/create" target="_json">
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_username">Nom d'utilisateur :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_username" name="username" value="" size="25" maxlength="50"  />
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_email">Adresse e-mail :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_email" name="email" value="" size="25" maxlength="80"  />
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="add_user">Ajouter l'utilisateur</span></button>
		</div>
	</form>
</fieldset>

<fieldset class="toggle"><legend>Rechercher un utilisateur</legend>
<?php
	// Configuration et construction de la recherche
	$search = mib_search_build(array(
		'target'					=> $MIB_PLUGIN['name'], // URL de la page dans laquelle s'affiche le tableau des résulats (généralement $MIB_PLUGIN['name'])
		'sort_by_autorized'			=> array('id','username','email','last_visit'), // Champs de la table sur lequels le tri est autorizé
		'sort_by_default'			=> 'username', // Champ de la table utilisé pour le tri par défaut
		'sort_dir_default'			=> 'ASC', // Ordre de tri par défaut
		'num_results_by_page'		=> 25, // Nombre de résultat à afficher par page
		'cols'					=> array( // Colonnes à affiché pour le tableau des résultats
				'id'			=> 'Réf',
				'username'	=> 'Nom d\'utilisateur',
				'g_title'		=> 'Groupe',
				'email'		=> 'E-mail',
				'admin_note'	=> 'Note admin',
				'last_visit'	=> 'Dernière visite',
		),
		'filters'					=> array( // Filtres de recherche
				'username'	=> 'Nom d\'utilisateur',
				'email'		=> 'Adresse e-mail',
				'group_id'	=> 'Groupe',
		)
	));
?>
	<div class="message">
		<p>Rechercher un utilisateur dans la base de données. Vous pouvez saisir un ou plusieurs termes à rechercher pour filtrer les résultats. Utilisez le caractère astérisque (*) comme joker.</p>
	</div>
	<form method="get" action="<?php echo $search['target']; ?>" target="<?php echo $search['target']; ?>">
<?php
	foreach($search['filters'] as $k => $v) {
		$id = $MIB_PAGE['uniqid'].'_search_'.$k;
?>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $id; ?>"><?php echo $v; ?> :</label></div>
			<div class="option-item">
<?php
			if($k == 'username' || $k == 'email') { // Nom d'utilisateur et email
				$search['filters_result'][$k] = (isset($_GET[$k])) ? mib_trim($_GET[$k]) : '';
				echo '<input type="text" class="input" id="'.$id.'" name="'.$k.'" size="25" maxlength="50" value="'.mib_html($search['filters_result'][$k]).'" />';
			}
			else if($k == 'group_id') { // Groupe
				$search['filters_result'][$k] = (!isset($_GET[$k]) || intval($_GET[$k]) < -1 && intval($_GET[$k]) > 2) ? -1 : intval($_GET[$k]);
				echo '<select class="input" id="'.$id.'" name="'.$k.'" >';
				echo '<option value="-1">Tous les utilisateurs</option>';
				echo '<optgroup label="&nbsp;Par groupe :">';
				$query = array(
					'SELECT'	=> 'g.g_id, g.g_title',
					'FROM'		=> 'groups AS g',
					'WHERE'		=> 'g_id!='.MIB_G_GUEST,
					'ORDER BY'	=> 'g.g_id'
				);
				$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
				while ($cur_group = $MIB_DB->fetch_assoc($result)) {
					if ($cur_group['g_id'] == $search['filters_result'][$k])
						echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.mib_html($cur_group['g_title']).'</option>'."\n";
					else 
						echo '<option value="'.$cur_group['g_id'].'">'.mib_html($cur_group['g_title']).'</option>'."\n";
				}
				echo '</optgroup></select>';
			}
?>
			</div>
		</div>
<?php
	}
?>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="filter">Filtrer les résultats</span></button>
		</div>
	</form>
</fieldset>
<?php

	// Ajoute les conditions des filtres à $search['where_sql']
	foreach($search['filters_result'] as $k => $v) {
		if(!empty($v)) { // Le filtre à une valeur
			if($k == 'username' || $k == 'email') // Nom d'utilisateur et email
				$search['where_sql'][] = 'u.'.$k.' '.$MIB_CONFIG['like_command'].' \''.$MIB_DB->escape(str_replace('*', '%', $v)).'\'';
			else if($k == 'group_id' && $v > -1) // Groupe
				$search['where_sql'][] = 'u.'.$k.'='.$v;
		}
	}
	$search['where_sql'][] = 'u.id > 1'; // Ne pas sélectionner l'utilisateur "invité" ;)

	// Requette pour compter le nombre de résultat
	$search['query']['count'] = array(
		'SELECT'	=> 'COUNT(u.id)',
		'FROM'		=> 'users AS u'
	);
	// Lance le comptage des résultats
	$search = mib_search_count($search);

	// Construit la navigation des résultats de la recherche
	$search = mib_search_navigation($search);

?>
	<style type="text/css">
	.<?php echo $search['target']; ?>-results .tc1 {
		width:50px;
		text-align: center;
	}
	.<?php echo $search['target']; ?>-results .tc3 {
		width:150px;
		text-align: center;
	}
	.<?php echo $search['target']; ?>-results .tc6 {
		width:120px;
		text-align: center;
	}
	</style>
	<table class="table-results <?php echo $search['target']; ?>-results">
<?php
	// Construit le header des résultats de la recherche
	$search = mib_search_header($search);
?>
	<tbody>
<?php
	// Requette pour obtenir les informations des résutats
	$search['query']['result'] = array(
		'SELECT'	=> 'u.*, g.*, o.user_id AS is_online',
		'FROM'		=> 'users AS u',
		'JOINS'		=> array(
			array(
				'LEFT JOIN'		=> 'groups AS g',
				'ON'			=> 'g.g_id=u.group_id'
			),
			array(
				'LEFT JOIN'		=> 'online AS o',
				'ON'			=> 'o.user_id=u.id AND o.user_id!=1 AND o.idle=0'
			)
		)
	);

	// Lance la recherche
	$search = mib_search_result($search);
	// Si il y a des résultats
	if($search['num_rows']) {
		while ($cur_result = $MIB_DB->fetch_assoc($search['result'])) {
			echo '<tr>';

			// Affiche les colonnes
			$i = 1;
			foreach($search['cols'] as $k => $v) {
				echo '<td class="tc'.$i.($i == 1 ? ' tcl' : '').($i == $search['num_cols'] ? ' tcr' : '').'">';

				if($k == 'id')
					echo $cur_result['id'];
				else if($k == 'username') {
					if($cur_result['is_online'] == $cur_result['id']) // L'utilisateur est en ligne ?
						echo ' <span class="iconimg tips fright mg3" title="En ligne" rel="Cet utilisateur est actuellement en ligne sur le Back Office." style="background-image: url(\'{{tpl:MIB_PLUGIN}}/img/user_green.png\');">En ligne</span>';

					echo '<a href="'.$MIB_PLUGIN['name'].'/edit/'.$cur_result['id'].'" title="'.mib_html($cur_result['username']).'" favicon="{{tpl:MIB_PLUGIN}}/img/user__pencil.png" >'.mib_html($cur_result['username']).'</a>';
				}
				else if($k == 'email')
					echo '<a href="mailto:'.mib_html($cur_result['email']).'" target="_blank">'.mib_html($cur_result['email']).'</a>';
				else if($k == 'g_title')
					echo mib_html($cur_result['g_title']);
				else if($k == 'admin_note')
					echo mib_html($cur_result['admin_note']);
				else if($k == 'last_visit')
					echo format_time($cur_result['last_visit']);

				echo '</td>';
				$i++;
			}

			echo '</tr>';
		}
	}
?>
	</tbody>
	</table>