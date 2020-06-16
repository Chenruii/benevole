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
	$MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id de l'url"
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

		$lang = (isset($_POST['lang'])) ? mib_trim($_POST['lang']) : '';
		$url = (isset($_POST['url'])) ? mib_trim(mib_trim($_POST['url']), '/') : '';

		if (!array_key_exists($lang, $MIB_CONFIG['languages']))
			error(__('La langue de l\'URL à optimiser est invalide.'));

		if (strpos($url, $MIB_CONFIG['base_url']) === 0) // "If" elle commence par $MIB_CONFIG['base_url'] on enlève $MIB_CONFIG['base_url']
			$url = substr($url, strlen($MIB_CONFIG['base_url']));

		// en lève les / en début et fin
		$url = mib_trim($url, '/'); 

		if (strpos($url, 'http://') === 0) // Il y a encore http:// (essaye d'ajouter une url qui n'est pas celle du site)
			error(__('Vous ne pouvez pas ajouter d\'URL n\'appartenant pas au site.'));

		// La langue est dans le début de l'url
		if(substr($url, 0, 3) == $lang.'/')
			$url = substr($url, 3);

		if(strpos($url, MIB_ADMIN_DIR) === 0)
			error(__('Vous ne pouvez pas optimiser l\'URL d\'accès au Back Office.'));

		if(strlen($url) < 3)
			$errors[] = __('L\'URL à optimiser doit être constituées d\'au moins 3 caractères.');
		if(preg_match('/[^0-9a-zA-Z\/\-_]/', $url))
			$errors[] = __('Il y a des caractères invalides dans l\'URL à optimiser.');

		// Vérifie si cette URL n'est pas déjà optimisée
		if(array_key_exists($lang.'/'.$url, $MIB_URL))
			$errors[] = __('Cette URL est déjà optimisée.');

		// Vérifie si cette URL n'est pas elle même déjà une URL optimisée
		if(array_key_exists($lang.'/'.$url, $MIB_URL_REWRITED))
			$errors[] = __('Vous ne pouvez pas utiliser cette URL car elle est déjà utilisée pour optimiser une autre URL.');

		$new_url = $lang.'/'.$url;
		$new_url_rewrited = $url;

		// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
		if(count(explode('/', $new_url)) > 2) {
			foreach ($MIB_URL as $r_url => $r_info) {
				if(count(explode('/', $r_info['url'])) == 2 && strpos($new_url.'/', $r_info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
					$new_url_rewrited = next(explode('/', $new_url_rewrited, 2));
					break;
				}
			}
		}

		// Aucune erreur
		if (empty($errors)) {
			// Ajoute l'url
			$query = array(
				'INSERT'	=> 'url, url_rewrited',
				'INTO'		=> 'urls',
				'VALUES'	=> '\''.$MIB_DB->escape($lang.'/'.$url).'\', \''.$MIB_DB->escape($new_url_rewrited).'\''
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			$new_id = $MIB_DB->insert_id();

			// Regénère le cache
			if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS.'cache.php'; // Si les fonctions de cache n'ont pas été chargées
			mib_generate_urls_cache();

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('URL ajoutée'),
				'value'		=> __('L\'URL <a href="'.$MIB_PLUGIN['name'].'/edit/'.$new_id.'" title="'.mib_html($lang.'/'.$url).'" ><strong>'.mib_html($lang.'/'.$url).'</strong></a> a été ajoutée avec succès.'),
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
		$url_id = intval($MIB_PLUGIN['id']);

		if($url_id < 1)
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$query = array(
			'SELECT'	=> '*',
			'FROM'		=> 'urls',
			'WHERE'		=> 'id='.$url_id
		);
		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		if (!$MIB_DB->num_rows($result))
			error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

		$cur_url = $MIB_DB->fetch_assoc($result);

		// Edition
		if($MIB_PLUGIN['action'] == 'edit') {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/edit/'.$url_id);

			$errors = array();

			$form['title'] = isset($_POST['title']) ? mib_trim($_POST['title']) : '';
			$form['meta_robots'] = isset($_POST['meta_robots']) ? mib_trim($_POST['meta_robots']) : '';
			$form['meta_description'] = isset($_POST['meta_description']) ? mib_trim($_POST['meta_description']) : '';
			$form['meta_keywords'] = isset($_POST['meta_keywords']) ? mib_trim($_POST['meta_keywords']) : '';
			$form['sitemap_priority'] = (isset($_POST['sitemap_priority']) && floatval($_POST['sitemap_priority']) > 0) ? floatval($_POST['sitemap_priority']) : 0;

			// Ce n'est pas une URL de base
			if($cur_url['url'] != '/' && !array_key_exists($cur_url['url'], $MIB_CONFIG['languages'])) {
				$form['url_rewrited'] = isset($_POST['url_rewrited']) ? mib_trim($_POST['url_rewrited']) : '';

				// en lève les / en début et fin
				$form['url_rewrited'] = mib_trim($form['url_rewrited'], '/'); 

				if(strlen($form['url_rewrited']) < 3)
					$errors[] = __('L\'URL optimisé doit être constituées d\'au moins 3 caractères.');
				if(preg_match('/[^0-9a-zA-Z\/\-_]/', $form['url_rewrited']))
					$errors[] = __('Il y a des caractères invalides dans l\'URL optimisé.');

				// Construit l'URL full
				$form['url_rewrited_full'] = current(explode('/', $cur_url['url'])).'/'; // Ajoute la langue

				// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
				if(count(explode('/', $cur_url['url'])) > 2) {
					foreach ($MIB_URL as $r_url => $r_info) {
						if(count(explode('/', $r_info['url'])) == 2 && strpos($cur_url['url'].'/', $r_info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
							$form['url_rewrited_full'] .= $r_info['url_rewrited'].'/';
							break;
						}
					}
				}

				$form['url_rewrited_full'] .= $form['url_rewrited'];
				$form['url_rewrited_full'] = mib_trim($form['url_rewrited_full'], '/');

				// Si l'URL originale est une rubrique, l'URL rewrité doit aussi être une rubrique
				if(count(explode('/', $cur_url['url'])) == 2) // == 2 car il ya la langue comprise dedant 
					if(count(explode('/', $form['url_rewrited_full'])) > 2)
						$errors[] = __('L\'URL originale est une rubrique. L\'URL optimisée ne peut pas contenir de sous-rubrique. Veuillez enlever un "/".');

				// Vérifie si cette URL n'est pas une URL originale déjà optimisée
				if($form['url_rewrited_full'] != $cur_url['url'] && array_key_exists($form['url_rewrited_full'], $MIB_URL))
					$errors[] = __('Cette URL est une URL originale déjà optimisée.');

				// Vérifie si cette URL n'est pas elle même déjà une URL optimisée
				if(array_key_exists($form['url_rewrited_full'], $MIB_URL_REWRITED) && $MIB_URL_REWRITED[$form['url_rewrited_full']] != $cur_url['url'])
					$errors[] = __('Vous ne pouvez pas utiliser cette URL car elle est déjà utilisée pour optimiser une autre URL.');

				unset($form['url_rewrited_full']); // on clean url_rewrited_full car cette colonne n'héxiste pas en DB
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
					'UPDATE'	=> 'urls',
					'SET'		=> implode(',', $temp),
					'WHERE'		=> 'id='.$cur_url['id']
				);
				$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

				// Regénère le cache
				if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS.'cache.php'; // Si les fonctions de cache n'ont pas été chargées
				mib_generate_urls_cache();

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Modifications effectuées'),
					'value'		=> __('L\'URL a été mise à jour avec succès.'),
					'options'		=> array('type' => 'valid'),
					'page'		=> array(
							'update'		=> $MIB_PLUGIN['name'] // Il faut update 
					)
				);
			}
			else
				error('<p>'.implode('</p><p>', $errors).'</p>');
		}
		// Suppression
		else if($MIB_PLUGIN['action'] == 'remove') {
			// On vérifie si on à la bonne URL sans "truc" en plus !
			mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/remove/'.$url_id);

			// On supprime l'URL
			$query = array(
				'DELETE'	=> 'urls',
				'WHERE'		=> 'id='.$url_id
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);

			// Regénère le cache
			if (!defined('MIB_LOADED_CACHE_FUNCTIONS')) require MIB_PATH_SYS.'cache.php'; // Si les fonctions de cache n'ont pas été chargées
			mib_generate_urls_cache();

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('Suppression effectuée'),
				'value'		=> __('L\'URL a été supprimée avec succès.'),
				'options'		=> array('type' => 'valid'),
				'page'		=> array(
						'update'		=> $MIB_PLUGIN['name'],
						'remove'		=> $MIB_PLUGIN['name'].'/edit/'.$url_id
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

// Edition
if($MIB_PLUGIN['action'] == 'edit') {
	$url_id = intval($MIB_PLUGIN['id']);

	if($url_id < 1)
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$query = array(
		'SELECT'	=> '*',
		'FROM'		=> 'urls',
		'WHERE'		=> 'id='.$url_id
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	if (!$MIB_DB->num_rows($result))
		error(__('Le lien que vous avez suivi est incorrect ou périmé.'));

	$cur_url = $MIB_DB->fetch_assoc($result);

	// Stock les urls existante dans ce tableau
	$urls_all = array(); 
	$urls_all_db = array(); 
	$urls_all['/'] = '<strong>Toutes les URLs</strong>';
	foreach($MIB_CONFIG['languages'] as $iso => $language)
		$urls_all[$iso] = 'URLs en langue : <strong>'.mib_html($language).'</strong>';

?>
	<style type="text/css">
		#MIB_page .button .edit_url { background-image: url('{{tpl:MIB_PLUGIN}}/img/target--pencil.png'); }
		#MIB_page .button .remove_url { background-image: url('{{tpl:MIB_PLUGIN}}/img/target--minus.png'); }
	</style>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_url['id']; ?>" target="_json">
	<fieldset><legend id="<?php echo $MIB_PLUGIN['name']; ?>_<?php echo $cur_url['id']; ?>_url"><?php echo mib_html('URL : '.$cur_url['url']); ?></legend>
		<div class="option-row">
			<div class="option-title">Réf :</div>
			<div class="option-item">
				<p><a href="<?php echo $MIB_PLUGIN['name']; ?>/edit/<?php echo $cur_url['id']; ?>"><?php echo $cur_url['id']; ?></a></p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title">URL originale :</div>
			<div class="option-item">
				<p>
<?php
					echo $cur_url['url'];

					if($cur_url['url'] == '/') {
						$cur_url['base_url'] = '/';
						echo ' - <strong>Toutes les URLs</strong>';
					}
					foreach($MIB_CONFIG['languages'] as $iso => $language) {
						if($iso == $cur_url['url']) {
							$cur_url['base_url'] = $iso;
							echo ' - URLs en langue : <strong>'.mib_html($language).'</strong>';
						}
					}
?>
				</p>
			</div>
		</div>
<?php
		if(empty($cur_url['base_url'])) {
			$cur_url['base_url'] = '';
?>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_url_rewrited">URL optimisée :</label></div>
			<div class="option-item">
<?php
				echo current(explode('/', $cur_url['url'])).'/'; // Affiche la langue
				// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
				if(count(explode('/', $cur_url['url'])) > 2) {
					foreach ($MIB_URL as $url => $info) {
						if($info['url'] != $cur_url['url'] && count(explode('/', $info['url'])) == 2 && strpos($cur_url['url'].'/', $info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
							echo '<a href="'.$MIB_PLUGIN['name'].'/edit/'.$info['id'].'" title="'.mib_html($info['url']).'" favicon="{{tpl:MIB_PLUGIN}}/img/target--pencil.png" >'.mib_html($info['url_rewrited']).'</a>/';
							break;
						}
					}
				}
?>
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_url_rewrited" name="url_rewrited" value="<?php echo mib_html($cur_url['url_rewrited']); ?>" size="50" maxlength="500" />
				<p>L'URL optimisée ne doit pas contenir de caractères spéciaux.</p>
			</div>
		</div>
<?php
		}
?>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_title">Titre de la page :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_title" name="title" value="<?php echo mib_html($cur_url['title']); ?>" size="50" maxlength="250" />
				<p>
					<?php
						if($cur_url['base_url'] == '/')
							echo 'Titre du site par défaut.';
						else if(!empty($cur_url['base_url']))
							echo 'Titre de la page d\'accueil de cette langue.';
					?>
					Le titre de la page (affiché en haut de chaque page). Ce champs <strong>ne doit pas</strong> contenir du HTML.
				</p>
			</div>
		</div>

		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_meta_description">META description :</label></div>
			<div class="option-item">
				<textarea class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_meta_description" name="meta_description" rows="5" cols="55" ><?php echo mib_html($cur_url['meta_description']); ?></textarea>
				<p>
					<?php
						if($cur_url['base_url'] == '/')
							echo 'META description par défaut du site. Cette description est utilisée si aucune autre description n\'a été trouvée.';
						else if(!empty($cur_url['base_url']))
							echo 'META description par défaut pour cette langue. Cette description est utilisée si aucune autre description concernant cette langue n\'a été trouvée.';
					?>
					Ce champs <strong>ne doit pas</strong> contenir du HTML.
				</p>
			</div>
		</div>

		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_meta_keywords">META keywords :</label></div>
			<div class="option-item">
				<textarea class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_meta_keywords" name="meta_keywords" rows="5" cols="55" ><?php echo mib_html($cur_url['meta_keywords']); ?></textarea>
				<p>
					<?php
						if($cur_url['base_url'] == '/')
							echo 'META keywords utilisés sur tous le site (en plus des META keywords spéciques aux URL(s)).';
						else if(!empty($cur_url['base_url']))
							echo 'META keywords utilisés pour toutes les pages de cette langue (en plus des META keywords spéciques aux URL(s)).';
					?>
					Ce champs <strong>ne doit pas</strong> contenir du HTML. Les META keywords sont a séparer par des virgules. 
				</p>
			</div>
		</div>

		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_meta_robots">META robots :</label></div>
			<div class="option-item">
				<select id="<?php echo $MIB_PAGE['uniqid']; ?>_meta_robots" name="meta_robots" >
					<option value="index,follow"<?php if ($cur_url['meta_robots'] == 'index,follow') echo ' selected="selected"' ?> >index,follow</option>
					<option value="index,follow,noarchive"<?php if ($cur_url['meta_robots'] == 'index,follow,noarchive') echo ' selected="selected"' ?> >index,follow,noarchive</option>
					<option value="index,nofollow"<?php if ($cur_url['meta_robots'] == 'index,nofollow') echo ' selected="selected"' ?> >index,nofollow</option>
					<option value="noindex,follow"<?php if ($cur_url['meta_robots'] == 'noindex,follow') echo ' selected="selected"' ?> >noindex,follow</option>
					<option value="noindex,nofollow"<?php if ($cur_url['meta_robots'] == 'noindex,nofollow') echo ' selected="selected"' ?> >noindex,nofollow</option>
					<option value="noindex,nofollow,noarchive"<?php if ($cur_url['meta_robots'] == 'noindex,nofollow,noarchive') echo ' selected="selected"' ?> >noindex,nofollow,noarchive</option>
				</select>
			</div>
		</div>

		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_sitemap_priority">Sitemap "priority" :</label></div>
			<div class="option-item">
				<select id="<?php echo $MIB_PAGE['uniqid']; ?>_sitemap_priority" name="sitemap_priority" >
					<option value="0" >0</option>
					<option value="0.1"<?php if ($cur_url['sitemap_priority'] == 0.1) echo ' selected="selected"' ?> >0.1</option>
					<option value="0.2"<?php if ($cur_url['sitemap_priority'] == 0.2) echo ' selected="selected"' ?> >0.2</option>
					<option value="0.3"<?php if ($cur_url['sitemap_priority'] == 0.3) echo ' selected="selected"' ?> >0.3</option>
					<option value="0.4"<?php if ($cur_url['sitemap_priority'] == 0.4) echo ' selected="selected"' ?> >0.4</option>
					<option value="0.5"<?php if ($cur_url['sitemap_priority'] == 0.5) echo ' selected="selected"' ?> >0.5</option>
					<option value="0.6"<?php if ($cur_url['sitemap_priority'] == 0.6) echo ' selected="selected"' ?> >0.6</option>
					<option value="0.7"<?php if ($cur_url['sitemap_priority'] == 0.7) echo ' selected="selected"' ?> >0.7</option>
					<option value="0.8"<?php if ($cur_url['sitemap_priority'] == 0.8) echo ' selected="selected"' ?> >0.8</option>
					<option value="0.9"<?php if ($cur_url['sitemap_priority'] == 0.9) echo ' selected="selected"' ?> >0.9</option>
					<option value="1"<?php if ($cur_url['sitemap_priority'] == 1) echo ' selected="selected"' ?> >1</option>
				</select>
				<p>Une URL avec un sitemap "priority" à 0 n'est pas inclue dans le sitemap du site.</p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="edit_url">Enregistrer</span></button>
		</div>
	</fieldset>
	</form>

<?php if (empty($cur_url['base_url'])): ?>
	<form confirm="Voulez vous supprimer l'URL <strong><?php echo mib_html($cur_url['url']); ?></strong> ?::Supprimer ?::question" method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/remove/<?php echo $cur_url['id']; ?>" target="_json" >
	<fieldset><legend>Supprimer l'URL optimisée</legend>
		<div class="alert">
			<strong>Attention</strong> : la supression d'une url optimisée est définitive.
		</div>
		<div class="option-row">
			<div class="option-title">URL à supprimer :</div>
			<div class="option-item">
				<p><?php echo mib_html($cur_url['url']); ?></p>
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="remove_url">Supprimer l'URL</span></button>
		</div>
	</fieldset>
	</form>
<?php endif;

	return;
}
?>
<style type="text/css">
	#MIB_page .button .add_url { background-image: url('{{tpl:MIB_PLUGIN}}/img/target--plus.png'); }
</style>

<fieldset class="toggle"><legend>Ajouter une URL à optimiser</legend>
	<div class="alert">
		Pour améliorer le référencement du site, vous pouvez ajouter des URLS à optimiser.
	</div>
	<form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/create" target="_json">
		<div class="option-row">
			<div class="option-title">Langue :</div>
			<div class="option-item">
<?php
				foreach($MIB_CONFIG['languages'] as $iso => $language)
					echo '<input type="radio" name="lang" id="'.$MIB_PAGE['uniqid'].'_lang_'.$iso.'" value="'.$iso.'" class="input"  /> <label for="'.$MIB_PAGE['uniqid'].'_lang_'.$iso.'"><img src="../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png" alt="'.$iso.'" /> '.mib_html($language).'</label> ';
?>
				<p>Indiquez la langue de l'URL à optimiser.</p>
			</div>
		</div>
		<div class="option-row">
			<div class="option-title"><label for="<?php echo $MIB_PAGE['uniqid']; ?>_url">URL à optimiser :</label></div>
			<div class="option-item">
				<input type="text" class="input" id="<?php echo $MIB_PAGE['uniqid']; ?>_url" name="url" value="" size="50" maxlength="500"  />
			</div>
		</div>
		<div class="option-actions">
			<button type="submit" class="button" ><span class="add_url">Ajouter l'url</span></button>
		</div>
	</form>
</fieldset>

<fieldset><legend>URLs de base</legend>
	<div class="alert">
		Les URLs de base sont les URLs par défaut du site.
	</div>
<?php
	// Stock les urls existante dans ce tableau
	$urls_all = array(); 
	$urls_all_db = array(); 
	$urls_all['/'] = '<strong>Toutes les URLs</strong>';
	foreach($MIB_CONFIG['languages'] as $iso => $language)
		$urls_all[$iso] = 'URLs en langue : <strong>'.mib_html($language).'</strong>';

	$search_base = array(
		'cols'					=> array( // Colonnes à affiché pour le tableau des résultats
			'url'			=> 'URL',
			'title'			=> 'Titre',
			'meta_description'	=> 'META description',
			'meta_keywords'	=> 'META keywords',
			'meta_robots'		=> 'META robots',
			'sitemap_priority'	=> 'Sitemap',
		)
	);

	// Grab les résultats
	$query = array(
		'SELECT'	=> '*',
		'FROM'		=> 'urls',
		'WHERE'		=> 'url = \''.$MIB_DB->escape('/').'\'',
		'ORDER BY'	=> 'url'
	);
	// Ajoute les urls de base pour les langues
	foreach($MIB_CONFIG['languages'] as $iso => $language)
		$query['WHERE'] .= ' OR url = \''.$MIB_DB->escape($iso).'\'';

	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	while ($cur_url = $MIB_DB->fetch_assoc($result))
		$urls_all_db[$cur_url['url']] = $cur_url;
?>
	<style type="text/css">
	.<?php echo $MIB_PLUGIN['name']; ?>-baseresults .tc2, .<?php echo $MIB_PLUGIN['name']; ?>-baseresults .tc3, .<?php echo $MIB_PLUGIN['name']; ?>-baseresults .tc4 {
		width:200px;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-baseresults .tc5 {
		width:100px;
		text-align: center;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-baseresults .tc6 {
		width:60px;
		text-align: center;
	}
	</style>
	<table class="table-results mg0 <?php echo $MIB_PLUGIN['name']; ?>-baseresults">
<?php

	// Construit le header des résultats de la recherche
	$search_base = mib_search_header($search_base);
?>
	<tbody>
<?php
	foreach($urls_all as $base_url => $base_title) {
		// L'url de base n'existe pas encore en DB, on l'ajoute
		if(!isset($urls_all_db[$base_url])) {
			$query = array(
				'INSERT'	=> 'url, url_rewrited',
				'INTO'		=> 'urls',
				'VALUES'	=> '\''.$MIB_DB->escape($base_url).'\', \''.$MIB_DB->escape($base_url).'\''
			);
			$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
			$new_id = $MIB_DB->insert_id();
			$urls_all_db[$base_url]['id'] = $new_id;
			$urls_all_db[$base_url]['sitemap_priority'] = 0;
		}

		echo '<tr>';

		// Affiche les colonnes
		$i = 1;
		foreach($search_base['cols'] as $k => $v) {
			echo '<td class="tc'.$i.($i == 1 ? ' tcl' : '').($i == $search_base['num_cols'] ? ' tcr' : '').'">';

			if($k == 'url') {
				echo '<a href="'.$MIB_CONFIG['base_url'].'/'.($base_url != '/' ? $base_url : '').'" target="_blank"><span class="iconimg tips fleft mg3" style="background-image: url(\'{{tpl:MIB_PLUGIN}}/img/target--arrow.png\');">URL</span></a>';
				echo '<a href="'.$MIB_PLUGIN['name'].'/edit/'.$urls_all_db[$base_url]['id'].'" class="tips" title="'.mib_html($base_url).'" favicon="{{tpl:MIB_PLUGIN}}/img/target--pencil.png" >'.$base_title.'</a>';
			}
			else if($k == 'title' || $k == 'meta_description' || $k == 'meta_keywords') {
				if(!empty($urls_all_db[$base_url][$k]))
					echo '<span class="tips" title="'.mib_html($search_base['cols'][$k]).'" rel="'.mib_html($urls_all_db[$base_url][$k]).'">'.mib_html(mib_split($urls_all_db[$base_url][$k], 27)).'</span>';
			}
			else if($k == 'meta_robots' && !empty($urls_all_db[$base_url]['meta_robots']))
				echo mib_html($urls_all_db[$base_url]['meta_robots']);
			else if($k == 'sitemap_priority' && isset($urls_all_db[$base_url]['sitemap_priority'])) {
				if(floatval($urls_all_db[$base_url]['sitemap_priority']) > 0 )
					echo floatval($urls_all_db[$base_url]['sitemap_priority']);
				else
					echo '<span class="tags error">0</span>';
			}

			echo '</td>';
			$i++;
		}

		echo '</tr>';
	}
?>
	</tbody>
	</table>
</fieldset>

<fieldset class="toggle"><legend>Rechercher une URL</legend>
<?php
	// Configuration et construction de la recherche
	$search = mib_search_build(array(
		'target'					=> $MIB_PLUGIN['name'], // URL de la page dans laquelle s'affiche le tableau des résulats (généralement $MIB_PLUGIN['name'])
		'sort_by_autorized'			=> array('id','url','title', 'meta_robots', 'sitemap_priority'), // Champs de la table sur lequels le tri est autorizé
		'sort_by_default'			=> 'url', // Champ de la table utilisé pour le tri par défaut
		'sort_dir_default'			=> 'ASC', // Ordre de tri par défaut
		'num_results_by_page'		=> 25, // Nombre de résultat à afficher par page
		'cols'					=> array( // Colonnes à affiché pour le tableau des résultats
			'id'				=> 'Réf',
			'url'			=> 'URL originale',
			'url_rewrited'		=> 'URL optimisée',
			'title'			=> 'Titre',
			'meta_description'	=> 'META description',
			'meta_keywords'	=> 'META keywords',
			'meta_robots'		=> 'META robots',
			'sitemap_priority'	=> 'Sitemap',
		),
		'filters'					=> array( // Filtres de recherche
				'lang'		=> 'Langue',
				'url'		=> 'URL originale',
				'title'		=> 'Titre',
				'meta'		=> 'META',
		)
	));
?>
	<div class="alert">
		Rechercher une URL dans la base de données. Vous pouvez saisir un ou plusieurs termes à rechercher pour filtrer les résultats. Utilisez le caractère astérisque (*) comme joker.
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
			if($k == 'url' || $k == 'url_rewrited' || $k == 'title' || $k == 'meta') {
				$search['filters_result'][$k] = (isset($_GET[$k])) ? mib_trim($_GET[$k]) : '';
				echo '<input type="text" class="input" id="'.$id.'" name="'.$k.'" size="50" maxlength="50" value="'.mib_html($search['filters_result'][$k]).'" />';
			}
			else if($k == 'lang') { // Langue
				$search['filters_result'][$k] = (isset($_GET[$k]) && array_key_exists($_GET[$k], $MIB_CONFIG['languages'])) ? mib_trim($_GET[$k]) : '';

				echo '<input type="radio" name="'.$k.'" id="'.$id.'" value="" '.(empty($search['filters_result'][$k]) ? 'checked="checked"' : '').' class="input"  /> <label for="'.$id.'">Toutes</label> ';

				foreach($MIB_CONFIG['languages'] as $iso => $language)
					echo '<input type="radio" name="'.$k.'" id="'.$id.'_'.$iso.'" value="'.$iso.'" '.($iso == $search['filters_result'][$k] ? 'checked="checked"' : '').' class="input"  /> <label for="'.$id.'_'.$iso.'"><img src="../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png" alt="'.$iso.'" /> '.mib_html($language).'</label> ';
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
			if($k == 'url' || $k == 'url_rewrited' || $k == 'title')
				$search['where_sql'][] = $k.' '.$MIB_CONFIG['like_command'].' \''.$MIB_DB->escape(str_replace('*', '%', $v)).'\'';
			else if($k == 'lang') // Langue
				$search['where_sql'][] = 'url '.$MIB_CONFIG['like_command'].' \''.$MIB_DB->escape($v.'/%').'\'';
			else if($k == 'meta') // META
				$search['where_sql'][] = '(meta_description '.$MIB_CONFIG['like_command'].' \''.$MIB_DB->escape(str_replace('*', '%', $v)).'\' || meta_keywords '.$MIB_CONFIG['like_command'].' \''.$MIB_DB->escape(str_replace('*', '%', $v)).'\')';
		}
	}
	// Ne pas rechercher dans les URL de base
	$search['where_sql'][] = 'url != \''.$MIB_DB->escape('/').'\'';
	foreach($MIB_CONFIG['languages'] as $iso => $language)
		$search['where_sql'][] = 'url != \''.$MIB_DB->escape($iso).'\'';

	// Requette pour compter le nombre de résultat
	$search['query']['count'] = array(
		'SELECT'	=> 'COUNT(id)',
		'FROM'		=> 'urls'
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
	.<?php echo $search['target']; ?>-results .tc4, .<?php echo $search['target']; ?>-results .tc5, .<?php echo $search['target']; ?>-results .tc6 {
		width:200px;
	}
	.<?php echo $search['target']; ?>-results .tc7 {
		width:100px;
		text-align: center;
	}
	.<?php echo $search['target']; ?>-results .tc8 {
		width:60px;
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
		'SELECT'	=> '*',
		'FROM'		=> 'urls'
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
				else if($k == 'url') {
					echo '<a href="'.$MIB_CONFIG['base_url'].'/'.$cur_result['url'].'" target="_blank"><span class="iconimg tips fleft mg3" style="background-image: url(\'{{tpl:MIB_PLUGIN}}/img/target-disable.png\');">URL</span></a>';
					echo '<a href="'.$MIB_PLUGIN['name'].'/edit/'.$cur_result['id'].'" title="'.mib_html($cur_result['url']).'" favicon="{{tpl:MIB_PLUGIN}}/img/target--pencil.png" >'.mib_html(mib_split($cur_result['url'], 30)).'</a>';
				}
				else if($k == 'url_rewrited') {
					$cur_result['url_rewrited_full_href'] = $MIB_CONFIG['base_url'];
					$cur_result['url_rewrited_full'] = current(explode('/', $cur_result['url'])).'/'; // Langue
					$cur_result['url_rewrited_full_href'] .= '/'.$cur_result['url_rewrited_full'];

					// Si l'URL original (avec des infos) contient une rubrique qui est optimisée
					if(count(explode('/', $cur_result['url'])) > 2) {
						foreach ($MIB_URL as $url => $info) {
							if($info['url'] != $cur_result['url'] && count(explode('/', $info['url'])) == 2 && strpos($cur_result['url'].'/', $info['url'].'/') === 0) { // Rubrique uniquement + Présente dans l'URL
								$cur_result['url_rewrited_full'] .= '<a href="'.$MIB_PLUGIN['name'].'/edit/'.$info['id'].'" title="'.mib_html($info['url']).'" favicon="{{tpl:MIB_PLUGIN}}/img/target--pencil.png" >'.mib_html($info['url_rewrited']).'</a>/';
								$cur_result['url_rewrited_full_href'] .= $info['url_rewrited'].'/';
								break;
							}
						}
					}
					$cur_result['url_rewrited_full'] .= $cur_result['url_rewrited'];
					$cur_result['url_rewrited_full_href'] .= $cur_result['url_rewrited'];

					echo '<a href="'.$cur_result['url_rewrited_full_href'].'" target="_blank"><span class="iconimg tips fleft mg3" style="background-image: url(\'{{tpl:MIB_PLUGIN}}/img/target--arrow.png\');">URL</span></a>';
					echo $cur_result['url_rewrited_full'];
				}
				else if($k == 'title' || $k == 'meta_description' || $k == 'meta_keywords') {
					if(!empty($cur_result[$k]))
						echo '<span class="tips" title="'.mib_html($search['cols'][$k]).'" rel="'.mib_html($cur_result[$k]).'">'.mib_html(mib_split($cur_result[$k], 27)).'</span>';
				}
				else if($k == 'meta_robots')
					echo mib_html($cur_result['meta_robots']);
				else if($k == 'sitemap_priority') {
					if(floatval($cur_result['sitemap_priority']) > 0 )
						echo floatval($cur_result['sitemap_priority']);
					else
						echo '<span class="tags error">0</span>';
				}

				echo '</td>';
				$i++;
			}

			echo '</tr>';
		}
	}
?>
	</tbody>
	</table>