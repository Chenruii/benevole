<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

$action = basename(__FILE__, '.php');

if ( defined('MIB_JSON') ) { // requète JSON
	if ( !defined('MIB_JSONED') ) define('MIB_JSONED', 1); // confirme qu'on renvoit du json

	// un formulaire a été envoyé
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		// vérifie si on à la bonne URL sans "truc" en plus !
		mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/'.$action);

		if ( !$MIB_USER['can_write_plugin'] ) error(__bo('Vous n\'avez pas la permission d\'effectuer cette action.'));

		// modification des infos en base de données
		if ( $updated = bo_configs_update($_POST, $MIB_PLUGIN['dbtable']) ) {
			$MIB_PLUGIN['json'] = array(
				'title'		=> __bo('Succès'),
				'value'		=> __bo('Les configurations générales ont été mise à jour avec succès.'),
				'options'	=> array('type' => 'valid'),
			);

			if ( $updated['site_title'] != $MIB_CONFIG['site_title'] ) // le titre a changé
				$MIB_PLUGIN['json']['element']['MIB_site_title']['set']['html'] = mib_html($updated['site_title']);

			if ( $updated['server_timezone'] != $MIB_CONFIG['server_timezone'] ) // le timezone a changé
				$MIB_PLUGIN['json']['page']['update'] = $MIB_PLUGIN['name'];

			// regénère le cache des configs
			if ( !defined('MIB_LOADED_CACHE_FUNCTIONS') ) require MIB_PATH_SYS.'cache.php'; // si les fonctions de cache n'ont pas été chargées
			mib_generate_configs_cache();
		}
		else
			mib_error_notify(); // affiche les erreurs rencontrées
	}

	return;
}
else if ( defined('MIB_AJAX') && !defined('MIB_AJAXED') ) // requète AJAX
	define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax

?>
<fieldset><legend><?php _ebo('Configurations générales'); ?></legend>
	<form method="post" action="<?php echo $MIB_PLUGIN['name'].'/'.$action; ?>" target="_json">
		<?php mib_form_build($action, $MIB_PLUGIN['inputs'], '*', $MIB_CONFIG); ?>
		<div class="option-actions">
			<button type="submit" class="button"><span class="save"><?php _ebo('Enregistrer'); ?></span></button>
		</div>
	</form>
</fieldset>

<fieldset><legend><?php _ebo('Plugins du Back Office'); ?></legend>
	<div class="alert"><?php _ebo('Les plugins étendent les fonctionnalités de Mibbo. Vous pouvez organiser leur position par défaut dans le menu du Back Office.'); ?></div>
<?php

	// colonnes à affiché pour le tableau des résultats
	$cols = array(
		'position'		=> '<span class="tips" title="'.__bo('Ordre de positionnement d\'affichage dans le menu.').'">'.__bo('Pos.').'</span>',
		'name'			=> '',
		'version'		=> __bo('Version'),
		'description'	=> __bo('Description'),
	);

	foreach( get_plugin_bo_by_cat() as $cat => $plugins ) {
		$cols['name'] = '<span class="tips" title="'.mib_html(get_BO_cat($cat, 'title')).'" rel="'.mib_html(get_BO_cat($cat, 'description')).'">'.mib_html(get_BO_cat($cat, 'title')).'</span>';

		// Tableau de l'affichage des résultats
		$search = mib_search_build(array(
			'uid'					=> mib_uid($MIB_PLUGIN['name'].basename(__FILE__, '.php').$cat),
			'sort_by_default'		=> 'position',
			'order_positions_url'	=> $MIB_PLUGIN['name'].'/plugins_positions', // url de la requette pour changer l'ordre de positionnement
			'cols'					=> $cols
		));
?>
	<style>
	#<?php echo $search['uid']; ?>-results { margin-bottom: -1px; }
	#<?php echo $search['uid']; ?>-results .tc-name {
		width: 250px;
	}
	#<?php echo $search['uid']; ?>-results .tc-version {
		white-space: nowrap;
		text-align: center;
		width: 70px;
	}
	</style>
<?php
		$search = mib_search_table_start($search); // initialisation du tableau de résultats

		foreach( $plugins as $p_k => $p_v ) {
			echo '<tr data-id="'.$p_k.'">'; // START LIGNE

				if ( count($plugins) == 1 ) $p_v['position'] = 1;
				foreach( $search['cols'] as $k => $v ) { echo '<td class="tc tc-'.$k.'">'; // START COLONNE

					if ( $k == 'name' ) {
						if ( !empty($p_v['favicon']) ) echo '<span class="icontxt" style="background-image:url(\'../../'.$p_v['favicon'].'\');">';
						echo mib_html($p_v['title']);
						if ( !empty($p_v['favicon']) ) echo '</span>';
					}
					else
						echo mib_html($p_v[$k]);

				echo '</td>'; } // END COLONNE

			echo '</tr>';
		}

		$search = mib_search_table_end($search); // fin du tableau de résultats
	}
?>
</fieldset>
<?php

$configs = array(
	'MIB_ADMIN_DIR'		=> __bo('Répertoire d\'administration par défaut.'),
	'MIB_LANG'			=> __bo('Langue par défaut utilisé si aucune langue n\'a été sélectionnée par le visiteur.'),
	'MIB_DEBUG'			=> __bo('Active le mode débug : affiche les erreurs, log les erreurs dans le fichier website/debug.log, affiche la barre de débug.'),
	'MIB_DEBUG_DISPLAY'	=> __bo('Ne pas afficher les erreurs quand le mode débug est actif.'),
	'MIB_DEBUG_LOG'		=> __bo('Ne pas log les erreurs quand le mode débug est actif.'),
	'MIB_PUBLIC_DIR'	=> __bo('Répertoire des fichiers public.'),
	'MIB_CACHE_DIR'		=> __bo('Répertoire des fichiers de cache.'),
	'MIB_ENCODE_EMAIL'	=> __bo('Active l\'encodage automatique des adresses emails pour limiter le spam.'),
	'MIB_GZIP_OUTPUT'	=> __bo('Active la compression gzip.'),
	'MIB_COMPRESS_HTML'	=> __bo('Active la compression du code HTML.'),
	'MIB_COMPRESS_JS'	=> __bo('Active la compression du code JAVASCRIPT.'),
	'MIB_COMPRESS_CSS'	=> __bo('Active la compression du code CSS.'),
	'MIB_BASE_URL'		=> __bo('Url de base de Mibbo.'),
	'COOKIE_NAME'		=> __bo('Cookie utilisé pour gérer la connexion automatique des membres.'),
	'MIB_IS_BEHIND_REVERSE_PROXY'	=> __bo('Sommes nous derière un "reverse proxy" (proxy inverse).'),
);

?>
<fieldset><legend><?php _ebo('Configurations avancées'); ?></legend>
	<div class="alert"><?php echo sprintf(__('Les configurations avancées sont modifiables dans le fichier %s'), '<code>config.php</code>'); ?></div>
	<div class="option-row">
		<div class="option-title"><?php echo 'MIB_PROD_SERVER'; ?></div>
		<div class="option-item">
			<p><code><?php if ( defined('MIB_PROD_SERVER') ) echo mib_html(MIB_PROD_SERVER); else echo 'NULL'; ?></code></p>
			<p>
<?php
			if ( defined('MIB_PROD_SERVER') )
				echo mib_sprintftpl(__bo('[[%site_title%]] est actuellement en production.'), array('site_title' => $MIB_CONFIG['site_title']));
			else
				_ebo('Veuillez activer cette configuration pour signaler qu\'on est en production.');
?>
			</p>
		</div>
	</div>
<?php
	foreach ( $configs as $config => $description ) {
		if ( defined($config) ) {
?>
	<div class="option-row">
		<div class="option-title"><?php e_html($config); ?></div>
		<div class="option-item">
			<p><code><?php if ( defined($config) ) echo mib_html(constant($config)); else echo 'NULL'; ?></code></p>
			<?php if ( !empty($description) ) echo '<p>'.$description.'</p>'; ?>
		</div>
	</div>
<?php
		}
	}
?>
</fieldset>