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

	$MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "remove", "create", "edit", "default"
	$MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id du groupe"
}
$MIB_PLUGIN['days'] = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'); // Gestion des jours de connexion

// Requète JSON
if(defined('MIB_JSON')) {

	// L'utilisateur a t'il les permissions d'écriture ?
	if(!$MIB_USER['can_write_plugin'])
		error(__('Vous n\'avez pas la permission d\'effectuer cette action.'));

	// Créer un groupe
	if($MIB_PLUGIN['action'] == 'create') {
		// On vérifie si on à la bonne URL sans "truc" en plus !
		mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/create');

		$title = isset($_POST['title']) ? mib_trim($_POST['title']) : '';
		$bo_perms = 'NULL'; // défini les permissions du groupe a zéro

		// supprime les espace blancs en doubles
		$title = preg_replace('#\s+#s', ' ', $title);

		if(empty($title))
			error(__('Vous devez indiquer un titre pour le groupe.'));

		// Vérifie si un titre est en double
		$query = array(
			'SELECT'	=> 1,
			'FROM'		=> 'groups',
			'WHERE'		=> '(UPPER(g_title)=UPPER(\''.$MIB_DB->escape($title).'\') OR UPPER(g_title)=UPPER(\''.$MIB_DB->escape(preg_replace('/[^\w]/u', '', $title)).'\'))'
		);
		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		if ($MIB_DB->num_rows($result))
			error(__('Un groupe existe déjà avec se titre, ou le titre du groupe est trop ressemblant.'));

		// Insert le nouveau groupe
		$query = array(
			'INSERT'	=> 'g_title, g_bo_perms',
			'INTO'		=> 'groups',
			'VALUES'	=> '\''.$MIB_DB->escape($title).'\', '.$bo_perms
		);
		$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		$new_group_id = $MIB_DB->insert_id();

		$MIB_PLUGIN['json'] = array(
			'title'		=> __('Création du groupe'),
			'value'		=> __('Le groupe <a href="'.$MIB_PLUGIN['name'].'/edit/'.$new_group_id.'" title="'.mib_html($title).'" ><strong>'.mib_html($title).'</strong></a> a été créé avec succès.'),
			'options'		=> array(
				'type'		=> 'valid',
				'duration'	=> -1
			),
			'page'		=> array(
					'update'		=> $MIB_PLUGIN['name']
			)
		);
	}
	// Définit le groupe par défaut
	else if($MIB_PLUGIN['action'] == 'default' && isset($_POST['default_group'])) {
		// On vérifie si on à la bonne URL sans "truc" en plus !
		mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/default');

		$group_id = intval($_POST['default_group']);

		// Assurons nous que se ne soit pas le groupe Admin ou invité
		if ($group_id == MIB_G_ADMIN || $group_id == MIB_G_GUEST)
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$query = array(
			'UPDATE'	=> 'configs',
			'SET'		=> 'conf_value='.$group_id,
			'WHERE'		=> 'conf_name=\'default_user_group\''
		);
		$query['WHERE'] .= ' AND conf_type=\'system\'';
		$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

		// Regénère le cache des configs
		if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS.'cache.php'; // Si les fonctions de cache n'ont pas été chargées
		mib_generate_configs_cache();

		$MIB_PLUGIN['json'] = array(
			'title'		=> __('Modifications effectuées'),
			'value'		=> __('Le groupe par défaut à été enregistré.'),
			'options'		=> array('type' => 'valid'),
			'page'		=> array(
					'update'		=> $MIB_PLUGIN['name'] // Il faut update pour regénérer les liens de supressions ou pas ;)
			)
		);
	}
	else if($MIB_PLUGIN['action'] == 'remove' || $MIB_PLUGIN['action'] == 'edit') {
		if($MIB_PLUGIN['action'] == 'edit')
			$group_id = intval($MIB_PLUGIN['id']);
		else if($MIB_PLUGIN['action'] == 'remove') {
			if(empty($_POST['remove_group']))
				error(__('Veuillez sélectionner un groupe à supprimer.'));

			$group_id = intval($_POST['remove_group']);
		}

		if($group_id < 1)
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$query = array(
			'SELECT'	=> 'g.*',
			'FROM'		=> 'groups AS g',
			'WHERE'		=> 'g.g_id='.$group_id
		);
		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		if (!$MIB_DB->num_rows($result))
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$cur_group = $MIB_DB->fetch_assoc($result);

		// Edition d'un groupe
		if($MIB_PLUGIN['action'] == 'edit') {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/edit/'.$group_id);

			$title = isset($_POST['title']) ? mib_trim($_POST['title']) : '';
			// supprime les espace blancs en doubles
			$title = preg_replace('#\s+#s', ' ', $title);
			if(empty($title))
				error(__('Vous devez indiquer un titre pour le groupe.'));

			// Vérifie si un titre est en double
			$query = array(
				'SELECT'	=> 1,
				'FROM'		=> 'groups',
				'WHERE'		=> '(UPPER(g_title)=UPPER(\''.$MIB_DB->escape($title).'\') OR UPPER(g_title)=UPPER(\''.$MIB_DB->escape(preg_replace('/[^\w]/u', '', $title)).'\'))'
			);
			$query['WHERE'] .= ' AND g_id!='.$group_id;
			$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			if ($MIB_DB->num_rows($result))
				error(__('Un groupe existe déjà avec se titre, ou le titre du groupe est trop ressemblant.'));

			// Permission du groupe
			if ($group_id == MIB_G_ADMIN || $group_id == MIB_G_GUEST) // Admin et invité n'ont aucune permission
				$bo_perms = 'NULL';
			else { // Permissions du groupe
				$_POST['BO_horaire'] = isset($_POST['BO_horaire']) && is_array($_POST['BO_horaire']) ? $_POST['BO_horaire'] : array();
				$bo_horaire = array();
				foreach($MIB_PLUGIN['days'] as $day) {
					if(array_key_exists($day, $_POST['BO_horaire'])) {
						$day_type = isset($_POST['BO_horaire'][$day]['type']) ? intval($_POST['BO_horaire'][$day]['type']) : 0;
						$day_start = isset($_POST['BO_horaire'][$day]['start']) ? intval($_POST['BO_horaire'][$day]['start']) : 0;
						$day_finish = isset($_POST['BO_horaire'][$day]['finish']) ? intval($_POST['BO_horaire'][$day]['finish']) : 0;

						// On a des horaires
						if($day_type == 2 && $day_start >= $day_finish) 
							error(sprintf(__('Horaires de connexion invalides pour le %s.'), __($day)));

						$bo_horaire[$day] = array(
							'type'	=> $day_type,
							'start'	=> $day_start,
							'finish'	=> $day_finish
						);
					}
					else {
						// Aucune permission pour ce jour
						$bo_horaire[$day] = array(
							'type'	=> 0,
							'start'	=> 0,
							'finish'	=> 0
						);
					}
				}

				$_POST['BO_plugins'] = isset($_POST['BO_plugins']) && is_array($_POST['BO_plugins']) ? $_POST['BO_plugins'] : array();
				$bo_plugins = array();
				foreach($_POST['BO_plugins'] as $plugin_name => $plugin_value) {
					$plugin_name = mib_trim($plugin_name);

					$bo_plugins[$plugin_name] = array(
						'read'	=> (isset($plugin_value['read']) ? intval($plugin_value['read']) : 0),
						'write'	=> (isset($plugin_value['write']) ? intval($plugin_value['write']) : 0),
					);
				}

				// Le groupe à accès au BO
				$_POST['BO_acces'] = isset($_POST['BO_acces']) ? intval($_POST['BO_acces']) : 0;
				if($_POST['BO_acces'] > 0)
					$bo_plugins['bo'] = true;

				if(!empty($bo_horaire) || !empty($bo_plugins))
					$bo_perms = '\''.$MIB_DB->escape(base64_encode(serialize($bo_plugins).'|'.serialize($bo_horaire))).'\'';
				else
					$bo_perms = 'NULL';
			}

			// Save changes
			$query = array(
				'UPDATE'	=> 'groups',
				'SET'		=> 'g_title=\''.$MIB_DB->escape($title).'\', g_bo_perms='.$bo_perms,
				'WHERE'		=> 'g_id='.$group_id
			);
			$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('Modifications effectuées'),
				'value'		=> __('Le groupe a été mis à jour avec succès.'),
				'options'		=> array('type' => 'valid'),
				'page'		=> array(
						'update'		=> $MIB_PLUGIN['name'] // Il faut update pour regénérer le titre des groupes
				)
			);

			if($title != $cur_group['g_title']) // Le titre du groupe a changé
				$MIB_PLUGIN['json']['element'][$MIB_PLUGIN['name'].'_'.$group_id.'_title']['set']['html'] = mib_html('Groupe : '.$title);

		}
		// Suppression d'un groupe
		else if($MIB_PLUGIN['action'] == 'remove' && isset($_POST['move_to_group'])) {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/remove');

			if(empty($_POST['move_to_group']))
				error(__('Veuillez sélectionner un groupe vers lequel seront déplacés les membres lors de la suppression.'));

			$move_to_group = intval($_POST['move_to_group']);

			// Assurons nous que se ne soit pas le groupe Admin ou invité ou par défaut, et Impossible de déplacer dans le groupe Guest
			if ($group_id == MIB_G_ADMIN || $group_id == MIB_G_GUEST || $group_id == $MIB_CONFIG['default_user_group'] || $move_to_group < 1 || $move_to_group == MIB_G_GUEST)
				error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

			// Impossible de déplacer dans le groupe que l'on supprime
			if($move_to_group == $group_id)
				error(__('Vous ne pouvez pas déplacer les membres du groupes que vous supprimez dans ce même groupe !'));

			// Déplace les membres
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'group_id='.$move_to_group,
				'WHERE'		=> 'group_id='.$group_id
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

			// Supprime le groupe
			$query = array(
				'DELETE'	=> 'groups',
				'WHERE'		=> 'g_id='.$group_id
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('Suppression effectuée'),
				'value'		=> __('Le groupe a été supprimé avec succès.'),
				'options'		=> array('type' => 'valid'),
				'page'		=> array(
						'update'		=> $MIB_PLUGIN['name'],
						'remove'		=> $MIB_PLUGIN['name'].'/edit/'.$group_id
				)
			);
		}
	}

	define('MIB_JSONED', 1);
	return;
}
// Requète AJAX
else if(defined('MIB_AJAX'))
	define('MIB_AJAXED', 1);

// Edition d'un groupe
if($MIB_PLUGIN['action'] == 'edit') {
	$group_id = intval($MIB_PLUGIN['id']);

	if($group_id < 1)
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$query = array(
		'SELECT'	=> 'g.*',
		'FROM'		=> 'groups AS g',
		'WHERE'		=> 'g.g_id='.$group_id
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	if (!$MIB_DB->num_rows($result))
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$cur_group = $MIB_DB->fetch_assoc($result);

	if(!empty($cur_group['g_bo_perms'])) { // Le groupe a des permissions
		@list($cur_group['g_bo_perms_plugins'], $cur_group['g_bo_perms_horaire']) = @explode('|', base64_decode($cur_group['g_bo_perms']));
		$cur_group['g_bo_perms_plugins'] = @unserialize($cur_group['g_bo_perms_plugins']);
		$cur_group['g_bo_perms_horaire'] = @unserialize($cur_group['g_bo_perms_horaire']);
	}
	// on valide que les infos de la db sont correctes
	$cur_group['g_bo_perms_plugins'] = (isset($cur_group['g_bo_perms_plugins']) && is_array($cur_group['g_bo_perms_plugins'])) ? $cur_group['g_bo_perms_plugins'] : array();
	$cur_group['g_bo_perms_horaire'] = (isset($cur_group['g_bo_perms_horaire']) && is_array($cur_group['g_bo_perms_horaire'])) ? $cur_group['g_bo_perms_horaire'] : array();
?>
	<style type="text/css">
		#MIB_page .button .edit_group { background-image: url('{{tpl:MIB_PLUGIN}}/img/users__pencil.png'); }
	</style>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_group['g_id']; ?>" target="_json">
	<fieldset><legend id="<?php echo $MIB_PLUGIN['name']; ?>_<?php echo $cur_group['g_id']; ?>_title"><?php echo mib_html('Groupe : '.$cur_group['g_title']); ?></legend>
		<div class="option-row">
			<div class="option-title">Réf :</div>
			<div class="option-item">
				<p><a href="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_group['g_id']; ?>"><?php echo $cur_group['g_id']; ?></a></p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_title">Titre du groupe :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_title" name="title" value="<?php echo mib_html($cur_group['g_title']); ?>" size="25" maxlength="50" />
			</div>
		</div>
		<?php if ($cur_group['g_id'] != MIB_G_ADMIN && $cur_group['g_id'] != MIB_G_GUEST): ?>
		<div class="option-row">
			<div class="option-title">Accès au Back Office :</div>
			<div class="option-item">
				<input <?php if(array_key_exists('bo', $cur_group['g_bo_perms_plugins'])) { echo 'checked="checked"'; } ?> type="radio" name="BO_acces" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_BO_accesOUI" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_BO_accesOUI"><strong>Oui</strong></label>
				<input <?php if(!array_key_exists('bo', $cur_group['g_bo_perms_plugins'])) { echo 'checked="checked"'; } ?> type="radio" name="BO_acces" value="0" id="<?php echo $MIB_PAGE['uniqid']; ?>_BO_accesNON" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_BO_accesNON"><strong>Non</strong></label>
				<p>Autoriser les utilisateurs de ce groupe à accéder au Back Office du site. Ce réglage s'applique à tous les aspects du Back Office et ne peut être outrepassé par des permissions spécifiques aux site. Avec cette option à non les utilisateurs de ce groupe ne pourront pas se connecter au Back Office.</p>
			</div>
		</div>
		<?php endif; ?>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="edit_group">Enregistrer</span></button>
		</div>
	</fieldset>

	<?php if ($cur_group['g_id'] != MIB_G_ADMIN && $cur_group['g_id'] != MIB_G_GUEST): ?>
	<fieldset><legend>Horaires de connexion au Back Office</legend>
		<div class="message">
			<p>Choisissez les horaires disponibles pour la connexion de ce groupe au Back Office. Attention, les horaires sont relatives à l'heure du serveur (actuellement: <?php echo format_time(time(), __('H:i'), true); ?>). Pensez aux décalages horaires si les utilisateurs concernés ne sont pas dans la même tranche horaire que le serveur.</p>
		</div>
<?php
		// Gestion des horaires de connexion
		foreach($MIB_PLUGIN['days'] as $day) {
?>
		<div class="option-row">
			<div class="option-title"><?php echo __($day); ?> :</div>
			<div class="option-item">
				<input <?php if(isset($cur_group['g_bo_perms_horaire'][$day]['type']) && $cur_group['g_bo_perms_horaire'][$day]['type'] == 1) { echo 'checked="checked"'; } ?> type="radio" name="BO_horaire[<?php echo $day; ?>][type]" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_always" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_always">Toute la journée</label>
				<input <?php if(empty($cur_group['g_bo_perms_horaire'][$day]['type'])) { echo 'checked="checked"'; } ?> type="radio" name="BO_horaire[<?php echo $day; ?>][type]" value="0" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_never" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_never">Jamais</label>
				<input <?php if(isset($cur_group['g_bo_perms_horaire'][$day]['type']) && $cur_group['g_bo_perms_horaire'][$day]['type'] == 2) { echo 'checked="checked"'; } ?> type="radio" name="BO_horaire[<?php echo $day; ?>][type]" value="2" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_hours" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $day; ?>_hours">À heures fixes :</label>
				<select name="BO_horaire[<?php echo $day; ?>][start]" class="input" >
<?php
				$m = 0;
				for($h=0;$h<24;$h++) {
					$h_v = intval($h.($m&1 ? '30' : '00'));
					$h_t = (($h < 10) ? '0'.$h : $h).':'.($m&1 ? '30' : '00');
					$h_c = (isset($cur_group['g_bo_perms_horaire'][$day]['start']) && $cur_group['g_bo_perms_horaire'][$day]['start'] == $h_v) ? ' selected="selected" ' : '';
					echo '<option value="'.$h_v.'" '.$h_c.'>'.$h_t.'</option>';
					$m++;

					$h_v = intval($h.($m&1 ? '30' : '00'));
					$h_t = (($h < 10) ? '0'.$h : $h).':'.($m&1 ? '30' : '00');
					$h_c = (isset($cur_group['g_bo_perms_horaire'][$day]['start']) && $cur_group['g_bo_perms_horaire'][$day]['start'] == $h_v) ? ' selected="selected" ' : '';
					echo '<option value="'.$h_v.'" '.$h_c.'>'.$h_t.'</option>';
					$m++;
				}
?>
				</select>
				-
				<select name="BO_horaire[<?php echo $day; ?>][finish]" class="input" >
<?php
				$m = 0;
				for($h=0;$h<24;$h++) {
					$h_v = intval($h.($m&1 ? '30' : '00'));
					$h_t = (($h < 10) ? '0'.$h : $h).':'.($m&1 ? '30' : '00');
					$h_c = (isset($cur_group['g_bo_perms_horaire'][$day]['finish']) && $cur_group['g_bo_perms_horaire'][$day]['finish'] == $h_v) ? ' selected="selected" ' : '';
					echo '<option value="'.$h_v.'" '.$h_c.'>'.$h_t.'</option>';
					$m++;

					$h_v = intval($h.($m&1 ? '30' : '00'));
					$h_t = (($h < 10) ? '0'.$h : $h).':'.($m&1 ? '30' : '00');
					$h_c = (isset($cur_group['g_bo_perms_horaire'][$day]['finish']) && $cur_group['g_bo_perms_horaire'][$day]['finish'] == $h_v) ? ' selected="selected" ' : '';
					echo '<option value="'.$h_v.'" '.$h_c.'>'.$h_t.'</option>';
					$m++;
				}
?>
				</select>
			</div>
		</div>
<?php
		}
?>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="edit_group">Enregistrer</span></button>
		</div>
	</fieldset>

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
			<p>Autorise l'accès des utilisateurs de ce groupe aux extensions du Back Office sélectionnées.</p>
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
					<td class="tc<?php echo $col++; ?>"><input <?php if(isset($cur_group['g_bo_perms_plugins'][$cur_plugin['uid']]['read']) && $cur_group['g_bo_perms_plugins'][$cur_plugin['uid']]['read'] == 1) { echo 'checked="checked"'; } ?> type="checkbox" name="BO_plugins[<?php echo $cur_plugin['uid']; ?>][read]" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_read" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_read">Lecture</label></td>
					<td class="tc<?php echo $col++; ?> tcr"><input <?php if(isset($cur_group['g_bo_perms_plugins'][$cur_plugin['uid']]['write']) && $cur_group['g_bo_perms_plugins'][$cur_plugin['uid']]['write'] == 1) { echo 'checked="checked"'; } ?> type="checkbox" name="BO_plugins[<?php echo $cur_plugin['uid']; ?>][write]" value="1" id="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_write" /> <label for="<?php echo $MIB_PAGE['uniqid']; ?>_<?php echo $cur_plugin['uid']; ?>_write">Écriture</label></td>
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
			<div class="option-item"><button type="submit" class="button" ><span class="edit_group">Enregistrer</span></button></div>
		</div>
	</fieldset>
	<?php endif; ?>
	</form>
<?php

	return;
}
?>
<style type="text/css">
	#MIB_page .button .add_group { background-image: url('{{tpl:MIB_PLUGIN}}/img/users__plus.png'); }
	#MIB_page .button .remove_group { background-image: url('{{tpl:MIB_PLUGIN}}/img/users__minus.png'); }
</style>

<fieldset class="toggle"><legend>Ajouter un nouveau groupe</legend>
	<div class="message">
		<p>Vous pourrez définir les permissions d'accès au Back Office une fois le groupe ajouté.</p>
	</div>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/create" target="_json">
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_title">Titre du groupe :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_title" name="title" value="" size="25" maxlength="50" />
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="add_group">Ajouter un groupe</span></button>
		</div>
	</form>
</fieldset>

<fieldset class="toggle"><legend>Définir le groupe par défaut</legend>
	<div class="message">
		<p>Choisissez le groupe que vous voulez définir par défaut. C'est à dire le groupe où les utilisateurs seront placés quand ils s'inscriront. Pour des raisons de sécurité, par défaut les utilisateurs ne peuvent être mis ni dans le groupe invité ni dans le groupe administrateur.</p>
	</div>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/default" target="_json">
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_default_group">Groupe par défaut :</label></div>
			<div class="option-item">
				<select name="default_group" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_default_group" >
				<?php
					$query = array(
						'SELECT'	=> 'g.g_id, g.g_title',
						'FROM'		=> 'groups AS g',
						'WHERE'		=> 'g_id>'.MIB_G_GUEST,
						'ORDER BY'	=> 'g.g_title'
					);
					$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
					while ($cur_group = $MIB_DB->fetch_assoc($result)) {
						if ($cur_group['g_id'] == $MIB_CONFIG['default_user_group'])
							echo '<option value="'.$cur_group['g_id'].'" selected="selected">'.mib_html($cur_group['g_title']).'</option>'."\n";
						else 
							echo '<option value="'.$cur_group['g_id'].'">'.mib_html($cur_group['g_title']).'</option>'."\n";
					}
				?>
				</select>
				<p>Le groupe par défaut ne peut pas être supprimé.</p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="save">Modifier le groupe par défaut</span></button>
		</div>
	</form>
</fieldset>

<fieldset><legend>Gestion des groupes</legend>
	<div class="message">
		<p>Chaque groupe possède ces propres permissions. Les Administrateurs ont toujours toutes les permissions. Les "Invités" correspondent aux utilisateurs non identifiés.</p>
	</div>
<?php
	// On prépare la recherche
	$search = array();

	// Construit le header
	$search['table_header'] = array();
	$search['table_header']['ref'] = 			'<th class="tc'.count($search['table_header']).' tcl">Réf</th>';
	$search['table_header']['title'] = 			'<th class="tc'.count($search['table_header']).'">Titre</th>';
	$search['table_header']['Bo_acces'] = 		'<th class="tc'.count($search['table_header']).'">Accès au BO</th>';
	$search['table_header']['Bo_horaire'] = 		'<th class="tc'.count($search['table_header']).' tcr">Horaires de connexion</th>';

?>
	<style type="text/css">
	.<?php echo $MIB_PLUGIN['name']; ?>-results .tcl {
		width:50px;
		text-align: center;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-results .tc2 {
		width:100px;
		text-align: center;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-results .tcr {
		width:220px;
		text-align: center;
	}
	</style>
	<table class="table-results mg0 <?php echo $MIB_PLUGIN['name']; ?>-results">
	<thead>
		<tr>
			<?php echo implode("\n\t\t\t", $search['table_header'])."\n" ?>
		</tr>
	</thead>
	<tbody>
<?php

	// Evite de refaire une requete lors de la suppression
	$groups_all = array(); 

	// Grab les résultats
	$query = array(
		'SELECT'	=> 'g.*',
		'FROM'		=> 'groups AS g',
		'ORDER BY'	=> 'g.g_id'
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	while ($cur_group = $MIB_DB->fetch_assoc($result)) {
		$groups_all[$cur_group['g_id']] = $cur_group['g_title'];

		if(!empty($cur_group['g_bo_perms'])) { // Le groupe a des permissions
			@list($cur_group['g_bo_perms_plugins'], $cur_group['g_bo_perms_horaire']) = @explode('|', base64_decode($cur_group['g_bo_perms']));
			$cur_group['g_bo_perms_plugins'] = @unserialize($cur_group['g_bo_perms_plugins']);
			$cur_group['g_bo_perms_horaire'] = @unserialize($cur_group['g_bo_perms_horaire']);
		}
		// on valide que les infos de la db sont correctes
		$cur_group['g_bo_perms_plugins'] = (isset($cur_group['g_bo_perms_plugins']) && is_array($cur_group['g_bo_perms_plugins'])) ? $cur_group['g_bo_perms_plugins'] : array();
		$cur_group['g_bo_perms_horaire'] = (isset($cur_group['g_bo_perms_horaire']) && is_array($cur_group['g_bo_perms_horaire'])) ? $cur_group['g_bo_perms_horaire'] : array();

		$search['table_row'] = array();

		$search['table_row']['ref'] = 			'<td class="tc'.count($search['table_row']).' tcl">'.$cur_group['g_id'].'</td>';
		$search['table_row']['title'] = 		'<td class="tc'.count($search['table_row']).'"><a href="'.$MIB_PLUGIN['name'].'/edit/'.$cur_group['g_id'].'" title="'.mib_html($cur_group['g_title']).'" favicon="{{tpl:MIB_PLUGIN}}/img/users__pencil.png" >'.$cur_group['g_title'].'</a>'.($cur_group['g_id'] == $MIB_CONFIG['default_user_group'] ? ' <span class="iconimg tips fright mg3" title="Groupe par défaut" rel="Groupe par défaut où les utilisateurs seront placés quand ils s\'inscriront." style="background-image: url(\'{{tpl:MIB_PLUGIN}}/img/asterisk.png\');">Groupe par défaut</span>' : '').'</td>';

		// Le groupe à accès au BO
		if($cur_group['g_id'] == MIB_G_ADMIN) {
			$search['table_row']['Bo_acces'] = 		'<td class="tc'.count($search['table_row']).'">Toujours</td>';
			$search['table_row']['Bo_horaire'] = 		'<td class="tc'.count($search['table_row']).' tcr">Toujours</td>';
		}
		else if($cur_group['g_id'] == MIB_G_GUEST) {
			$search['table_row']['Bo_acces'] = 		'<td class="tc'.count($search['table_row']).'">Jamais</td>';
			$search['table_row']['Bo_horaire'] = 		'<td class="tc'.count($search['table_row']).' tcr"></td>';
		}
		else if(array_key_exists('bo', $cur_group['g_bo_perms_plugins'])) {
			$search['table_row']['Bo_acces'] = 		'<td class="tc'.count($search['table_row']).'">Réglementé</td>';
			$search['table_row']['Bo_horaire'] = 	'<td class="tc'.count($search['table_row']).' tcr">';
			// Gestion des horaires de connexion
			foreach($MIB_PLUGIN['days'] as $day) {
				if(isset($cur_group['g_bo_perms_horaire'][$day]['type']) && $cur_group['g_bo_perms_horaire'][$day]['type'] == 1)
					$search['table_row']['Bo_horaire'] .= ' <span class="tips tags valid" title="'.mib_html(__($day)).'" rel="Toute la journée" >'.substr(__($day), 0, 3).'</span>';
				else if(empty($cur_group['g_bo_perms_horaire'][$day]['type']))
					$search['table_row']['Bo_horaire'] .= ' <span class="tips tags error" title="'.mib_html(__($day)).'" rel="Jamais" >'.substr(__($day), 0, 3).'</span>';
				else if(isset($cur_group['g_bo_perms_horaire'][$day]['type']) && $cur_group['g_bo_perms_horaire'][$day]['type'] == 2)
					$search['table_row']['Bo_horaire'] .= ' <span class="tips tags orange" title="'.mib_html(__($day)).'" rel="À heures fixes de '.substr_replace($cur_group['g_bo_perms_horaire'][$day]['start'], 'h', -2, 0).' à '.substr_replace($cur_group['g_bo_perms_horaire'][$day]['finish'], 'h', -2, 0).'" >'.substr(__($day), 0, 3).'</span>';
			}
			$search['table_row']['Bo_horaire'] .= 	'</td>';
		}
		// Pas d'accès au BO
		else {
			$search['table_row']['Bo_acces'] = 		'<td class="tc'.count($search['table_row']).'">Aucun</td>';
			$search['table_row']['Bo_horaire'] = 	'<td class="tc'.count($search['table_row']).' tcr"></td>';
		}

?>
	<tr>
		<?php echo implode("\n\t\t\t", $search['table_row'])."\n" ?>
	</tr>
<?php
	}
?>
	</tbody>
	</table>
</fieldset>

<fieldset class="toggle"><legend>Supprimer un groupe</legend>
	<div class="message">
		<p>Choisissez le groupe que vous voulez supprimer et indiquez le nouveau groupe dans lequel seront déplacés les membres du groupe supprimé.</p>
	</div>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/remove" confirm="Attention, la suppression d'un groupe est définitive.::Supprimer un groupe ?" target="_json">
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_remove_group">Groupe à supprimer :</label></div>
			<div class="option-item">
				<select name="remove_group" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_remove_group" >
					<option value="">Sélectionnez un groupe</option>
				<?php
					foreach($groups_all as $gid => $gtitle) {
						if($gid != MIB_G_ADMIN && $gid != MIB_G_GUEST && $gid != $MIB_CONFIG['default_user_group'])
							echo '<option value="'.$gid.'">'.mib_html($gtitle).'</option>'."\n";
					}
				?>
				</select>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_move_to_group">Déplacer vers :</label></div>
			<div class="option-item">
				<select name="move_to_group" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_move_to_group" >
					<option value="">Sélectionnez un groupe</option>
				<?php
					foreach($groups_all as $gid => $gtitle) {
						if($gid != MIB_G_GUEST)
							echo '<option value="'.$gid.'">'.mib_html($gtitle).'</option>'."\n";
					}
				?>
				</select>
				<p>Avant la suppression, les membres du groupe à supprimer seront déplacés dans ce groupe.</p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="remove_group">Supprimer le groupe</span></button>
		</div>
	</form>
</fieldset>