<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit;

/**
 * Préparation de la recherche et test si toutes les infos necessaire sont dispo
 */
function mib_search_build($cur_search) {

	// Pas de $cur_search !!!
	if(!is_array($cur_search) || empty($cur_search))
		error(__('Impossible de construire la recherche. La configuration de la recherche est manquante.'));

	// Pas de target, on met celui par défaut
	if( empty($cur_search['target']) )
		$cur_search['target'] = '_self';
	if ( empty($cur_search['href']) )
		$cur_search['href'] = $cur_search['target'];

	// Pas d'indication sur le tri est autorizé, on créer un tableau vide au cas ou
	if(empty($cur_search['sort_by_autorized']) || !is_array($cur_search['sort_by_autorized']) )
		$cur_search['sort_by_autorized'] = array();

	// Pa de champ de la table utilisé pour le tri par défaut indiqué,
	// on essaye de mettre le 1er champs de sort_by_autorized mais il n'existe pas non plus
	// on affiche une erreur
	if(empty($cur_search['sort_by_default'])) {
		if(empty($cur_search['sort_by_autorized']))
			error(sprintf(__('La variable %s concernant le champ utilisé pour le tri par défaut de la recherche est manquante.'), '<code>[\'sort_by_default\']</code>'));
		else if ( isset($cur_search['cols']['position']) )
			$cur_search['sort_by_default'] = 'position';
		else
			$cur_search['sort_by_default'] = current($cur_search['sort_by_autorized']);
	}

	// Ordre de tri par défaut manquant, on met ASC
	if(empty($cur_search['sort_dir_default']) || ($cur_search['sort_dir_default'] != 'ASC' && $cur_search['sort_dir_default'] != 'DESC'))
		$cur_search['sort_dir_default'] = 'ASC';

	// Nombre de résultat à afficher par page
	if(empty($cur_search['num_results_by_page']) || intval($cur_search['num_results_by_page']) < 2)
		$cur_search['num_results_by_page'] = 20;

	// Pas de filtre de recherche, on créer un tableau vide au cas ou
	if( empty($cur_search['filters']) || !is_array($cur_search['filters']) )
		$cur_search['filters'] = array();

	// Les résultats des filtres de recherches seront stokés dans ce tableau
	$cur_search['filters_result'] = array();

	// Détection de l'ordre de recherche
	$cur_search['sort_by'] = (!isset($_GET['sort_by']) || !in_array($_GET['sort_by'], $cur_search['sort_by_autorized'])) ? $cur_search['sort_by_default'] : $_GET['sort_by'];
	$cur_search['sort_dir'] = (!isset($_GET['sort_dir']) || $_GET['sort_dir'] != 'ASC' && $_GET['sort_dir'] != 'DESC') ? strtoupper($cur_search['sort_dir_default']) : strtoupper($_GET['sort_dir']);
	$cur_search['sort_dir_selected'] = ($cur_search['sort_dir'] == 'ASC') ? 'DESC' : 'ASC';

	// Prépare le lien d'une URL comprenant les filtres
	$cur_search['filters_url'] = array();

	// Prépare le lien d'une URL comprenant les tri
	$cur_search['sort_url'] = array();
	$cur_search['sort_url'][] = 'sort_by='.$cur_search['sort_by'];
	$cur_search['sort_url'][] = 'sort_dir='.$cur_search['sort_dir'];

	// Préparation de la clause WHERE pour la requette SQL
	$cur_search['where_sql'] = array();
	// Préparation du tableau des requettes "count" et "result"
	$cur_search['query'] = array();

	return $cur_search;
}

/**
 * Compte le nombre de résultat d'une recherche et prépare les variables pour
 * afficher la navigation dans les résultats (page suivante, précédente, etc...)
 */
function mib_search_count($cur_search) {
	global $MIB_DB;

	// Pas de $cur_search !!!
	if(!is_array($cur_search) || empty($cur_search))
		error(__('Impossible de compter le nombre de résultats de la recherche. La configuration de la recherche est manquante.'));

	if(empty($cur_search['query']['count']))
		error(sprintf(__('La requette %s est manquante.'), '<code>[\'query\'][\'count\']</code>'));

	// Il y a des conditions a prendre en compte
	if (!empty($cur_search['where_sql']))
		$cur_search['query']['count']['WHERE'] = implode(' AND ', $cur_search['where_sql']);

	$result = $MIB_DB->query_build($cur_search['query']['count']) or error(__FILE__, __LINE__);
	$cur_search['num_results'] = $MIB_DB->result($result);

	/*
		Détermine le nombre de résultats et de pages.
		Une petite astuce est utilisée pour toujours réafficher le dernier résultat
		de la page précédente.
	*/
	$cur_search['num_pages'] = ceil($cur_search['num_results'] / ($cur_search['num_results_by_page']-1));
	$cur_search['page'] = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $cur_search['num_pages']) ? 1 : intval($_GET['p']); // Numéro de la page
	$cur_search['start_from'] = ($cur_search['num_results_by_page']-1) * ($cur_search['page'] - 1);
	$cur_search['num_pages'] = ceil(($cur_search['num_results'] - $cur_search['num_results_by_page']) / ($cur_search['num_results_by_page'] - 1) + 1);

	// Préparation des données necessaires à l'affichage des résultats
	$cur_search['num_cols'] = count($cur_search['cols']); // Nombre de colonnes dans le tableau de résultats

	// Ajoute les filtres à $cur_search['filters_url']
	foreach($cur_search['filters_result'] as $k => $v) {
		if(!empty($v)) { // Le filtre à une valeur
			$cur_search['filters_url'][] = $k.'='.urlencode($v);
		}
	}

	return $cur_search;
}

/**
 * Construction du header de la table des résultats d'une recherche
 */
function mib_search_header($cur_search) {

	// Pas de $cur_search !!!
	if(!is_array($cur_search) || empty($cur_search))
		error(__('Impossible de construire le header des résultats de la recherche. La configuration de la recherche est manquante.'));

	if(!isset($cur_search['sort_by_autorized']) || !is_array($cur_search['sort_by_autorized']))
		$cur_search['sort_by_autorized'] = array();

	if(!isset($cur_search['filters_url']) || !is_array($cur_search['filters_url']))
		$cur_search['filters_url'] = array();

	// Préparation des données necessaires à l'affichage des résultats
	if(empty($cur_search['num_cols']))
		$cur_search['num_cols'] = count($cur_search['cols']); // Nombre de colonnes dans le tableau de résultats

	echo '<thead><tr>';

	$i = 1;
	foreach($cur_search['cols'] as $k => $v) {
		echo '<th class="tc tc-'.$k.'">';
		if(in_array($k, $cur_search['sort_by_autorized'])) { // Il y a un tri possible sur cette colonne
			echo '<a href="'.$cur_search['href'].'?'.implode('&amp;', $cur_search['filters_url']).'&amp;sort_by='.urlencode($k).($cur_search['sort_by'] == $k ? '&amp;sort_dir='.$cur_search['sort_dir_selected'] : '').'" class="'.($cur_search['sort_by'] == $k ? 'sort_dir_'.strtoupper($cur_search['sort_dir']).' to_sort_dir_'.strtoupper($cur_search['sort_dir_selected']) : 'to_sort_dir_'.strtoupper($cur_search['sort_dir_default'])).'" target="'.$cur_search['target'].'">'.$v.'</a>';
		}
		else
			echo $v;
		echo '</th>';
		$i++;
	}

	echo '</tr></thead>';

	return $cur_search;
}

/**
 * Construction de la navigation de la table des résultats d'une recherche
 */
function mib_search_navigation($cur_search) {

	// Pas de $cur_search !!!
	if(!is_array($cur_search) || empty($cur_search))
		error(__('Impossible de construire la navigation des résultats de la recherche. La configuration de la recherche est manquante.'));

	echo '<div class="nav-result">';

	// si on a des résultats
	if($cur_search['num_results'] > 0) {
		if($cur_search['num_pages'] > 1) {
			echo '<form method="get" action="'.$cur_search['href'].'?'.(count($cur_search['sort_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['sort_url']) : '').(count($cur_search['filters_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['filters_url']) : '').'" target="'.$cur_search['target'].'">';
			echo '<strong>'.$cur_search['num_results'].' '.($cur_search['num_results'] > 1 ? 'résultats' : 'résultat').'</strong>';
			// Première page
			echo ' <a href="'.$cur_search['href'].'?p=1'.(count($cur_search['sort_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['sort_url']) : '').(count($cur_search['filters_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['filters_url']) : '').'" class="button minbutton tips" target="'.$cur_search['target'].'" title="Première page" ><span class="nav_first">&laquo;</span></a>';
			// Page précédente
			echo ' <a href="'.$cur_search['href'].'?p='.($cur_search['page'] > 1 ? ($cur_search['page'] - 1) : 1).(count($cur_search['sort_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['sort_url']) : '').(count($cur_search['filters_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['filters_url']) : '').'" class="button minbutton tips" target="'.$cur_search['target'].'" title="Page précédente" ><span class="nav_prev">&laquo;</span></a>';

			echo ' '.__('Page').' <input type="text" class="input mininput" size="2" maxlength="5" name="p" value="'.$cur_search['page'].'"> '.__('sur').' '.$cur_search['num_pages'];

			// Page suivante
			echo ' <a href="'.$cur_search['href'].'?p='.(($cur_search['page'] + 1) > $cur_search['num_pages'] ? $cur_search['num_pages'] : ($cur_search['page'] + 1)).(count($cur_search['sort_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['sort_url']) : '').(count($cur_search['filters_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['filters_url']) : '').'" class="button minbutton tips" target="'.$cur_search['target'].'" title="Page suivante" ><span class="nav_next">&laquo;</span></a>';
			// Dernière page
			echo ' <a href="'.$cur_search['href'].'?p='.$cur_search['num_pages'].(count($cur_search['sort_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['sort_url']) : '').(count($cur_search['filters_url']) > 0 ? '&amp;'.implode('&amp;', $cur_search['filters_url']) : '').'" class="button minbutton tips" target="'.$cur_search['target'].'" title="Dernière page" ><span class="nav_end">&laquo;</span></a>';
			echo '</form>';
		}
		else
			echo '<strong>'.$cur_search['num_results'].' '.($cur_search['num_results'] > 1 ? 'résultats' : 'résultat').'</strong>';
	}
	else
		echo '<strong>'.__('Aucun résultat').'</strong>';

	echo '</div>';

	return $cur_search;
}

/**
 * Grab les résultats demandés
 */
function mib_search_result($cur_search) {
	global $MIB_DB;

	// Pas de $cur_search !!!
	if(!is_array($cur_search) || empty($cur_search))
		error(__('Impossible d\'afficher les résultats de la recherche. La configuration de la recherche est manquante.'));

	if(empty($cur_search['query']['result']))
		error(sprintf(__('La requette %s est manquante.'), '<code>[\'query\'][\'result\']</code>'));

	// Il y a des conditions a prendre en compte
	if (!empty($cur_search['where_sql']))
		$cur_search['query']['result']['WHERE'] = implode(' AND ', $cur_search['where_sql']);

	// ORDER BY
	$cur_search['query']['result']['ORDER BY'] = $cur_search['sort_by'].' '.$cur_search['sort_dir'];
	// LIMIT
	$cur_search['query']['result']['LIMIT'] = $cur_search['start_from'].', '.$cur_search['num_results_by_page'];

	$cur_search['result'] = $MIB_DB->query_build($cur_search['query']['result']) or error(__FILE__, __LINE__);
	$cur_search['num_rows'] = $MIB_DB->num_rows($cur_search['result']);

	// Aucun résultat
	if(!$cur_search['num_rows'])
		echo '<tr><td class="tc-no-result" colspan="'.$cur_search['num_cols'].'">'.__('Modifier les filtres de recherche pour obtenir plus de résultats.').'</td></tr>';

	return $cur_search;
}


/**
 * Initialisation du tableau de résultats
 */
function mib_search_table_start($search) {

	echo '<table';

	if ( !empty($search['uid']) )
		echo ' data-sort_dir="'.$search['sort_dir'].'" id="'.$search['uid'].'-results"';


	$class = array('table-results', $search['sort_by'].'-order');

	if ( $search['sort_by'] == 'position' && !empty($search['order_positions_url']) && (empty($search['filters_result']) || !empty($search['order_positions_forced'])) ) $class[] = 'table-sortable'; 

	echo ' class="'.implode(' ', $class).'"';

	echo '>';

	$search = mib_search_header($search); // construction du header

	echo '<tbody>';

	return $search;
}

/**
 * Fin du tableau de résultats
 */
function mib_search_table_end($search) {
	echo '</tbody></table>';

	// javascript de positionnement
	if ( $search['sort_by'] == 'position' && !empty($search['order_positions_url']) && (empty($search['filters_result']) || !empty($search['order_positions_forced'])) ) {
?>
<script>
var sort<?php echo $search['uid']; ?> = new MIB_Table_Sortables(
	$$('#<?php echo $search['uid']; ?>-results tbody'),
	{
		onSort: function(el) {
			var order = sort<?php echo $search['uid']; ?>.serialize(
				function(el, index) {
					return el.get('data-id');
				}
			)<?php if ( $search['sort_dir'] == 'DESC' ) { echo '.reverse()'; } ?>.join('-');

			MIB_Bo.load('<?php echo $search['order_positions_url']; ?>/'+order, {'target': '_json'});
		}
	}
);
</script>
<?php
	}

	return $search;
}

/**
 * Charge les informations des catégories disponibles pour le BO
 *
 * @param string $uid UID de la catégorie
 * @param string $return
 *
 * @uses $MIB_CATS_CONFIGS
 * @uses $MIB_PAGE
 *
 * @return array $MIB_CATS_CONFIGS
 *
 * @example
 *  get_BO_cat(); => array()
 *  get_BO_cat('uid'); => array()
 *  get_BO_cat('uid', 'title'); = > 'Titre de la catégorie'
 *  get_BO_cat('', 'title'); = > false
 */
function get_BO_cat($uid = false, $return = false) {
	global $MIB_CATS_CONFIGS, $MIB_PAGE;

	// Si la liste des catégorie n'a pas encore été chargée
	if(!isset($MIB_CATS_CONFIGS)) {
		$MIB_CATS_CONFIGS = array();




        $MIB_CATS_CONFIGS  =  [
            'mibbo' => ['position' => 0, 'title' => 'Mibbo', 'description' => 'Configurations générales et administration de Mibbo.'],
            'mibbo_website' => ['position' => 1, 'title' => 'Site Internet', 'description' => 'Outils disponibles pour administrer le site Internet.'],
            'website' => ['position' => 2, 'title' => '{{tpl:MIBconfig site_title}}', 'description' => 'Plugins spécifiques à {{tpl:MIBconfig site_title}}.'],
            'hidden' => ['position' => 999, 'title' => 'Plugins Invisibles', 'description' => 'Plugins invisibles qui n\'apparaissent pas dans la barre de navigation de l\'administration.']
        ];

		// Effectue un trie en fonction de la position trouvée
		foreach ($MIB_CATS_CONFIGS as $key => $row) { $position[$key] = $row['position']; } // Obtient une liste de la colonne position
		array_multisort($position, SORT_ASC, $MIB_CATS_CONFIGS); // Ordonne le résultat

	}

	// Renvois les infos d'une seule catégorie
	if($uid) {
		$uid = utf8_strtolower(mib_trim($uid));

		if($return) {
			$return = utf8_strtolower(mib_trim($return));
			if($return == 'uid')
				return $uid;
			else if($MIB_CATS_CONFIGS[$uid][$return])
				return $MIB_CATS_CONFIGS[$uid][$return];
			else
				return false;
		}
		else {
			if($MIB_CATS_CONFIGS[$uid])
				return $MIB_CATS_CONFIGS[$uid];
			else
				return false;
		}
	}
	else if($return)
		return false;

	return $MIB_CATS_CONFIGS;
}

/**
 * Charge les informations des plugins disponibles
 *
 * @param string $uid UID du plugin
 * @param string $return
 *
 * @uses $MIB_PLUGINS_CONFIGS
 * @uses $MIB_PAGE
 *
 * @return array $MIB_PLUGINS_CONFIGS
 *
 * @example
 *  get_plugin(); => array()
 *  get_plugin('uid'); => array()
 *  get_plugin('uid', 'title'); = > 'Titre du plugin'
 *  get_plugin('', 'title'); = > false
 */
function get_plugin($uid = false, $return = false) {
	global $MIB_PLUGINS_CONFIGS, $MIB_PLUGINS, $MIB_PAGE;

	// Si la liste des plugins n'a pas encore été chargée
	if(!isset($MIB_PLUGINS_CONFIGS)) {
		$MIB_PLUGINS_CONFIGS = array();

		// Chargement des plugins system
		$d = dir(MIB_PATH_SYS.'plugins');
		while (($entry = $d->read()) !== false) {
			if ($entry{0} != '.' && is_dir(MIB_PATH_SYS.'plugins/'.$entry)) {
				$MIB_PLUGINS_CONFIGS[$entry] = array();

				if (preg_match('/[^0-9a-z_]/', $entry))
					$MIB_PLUGINS_CONFIGS[$entry]['error'][] = 'UID Illegal.';
				else {
					if (!file_exists(MIB_PATH_SYS.'plugins/'.$entry.'/'.$entry.'.xml'))
						$MIB_PLUGINS_CONFIGS[$entry]['error'][] = 'Fichier du plugin configuration manquant.';
					// Charge le XML
					else {
						$cur_plugin = @simplexml_load_file(MIB_PATH_SYS.'plugins/'.$entry.'/'.$entry.'.xml');
						if(isset($cur_plugin[0])) { // la config du plugin à été chargé
							$cur_plugin = $cur_plugin[0];
							$cur_plugin_uid = $entry;

							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['position'] = isset($MIB_PLUGINS[$cur_plugin_uid]['position']) ? intval($MIB_PLUGINS[$cur_plugin_uid]['position']) : 0;
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['uid'] = $cur_plugin_uid;
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['version'] = mib_trim($cur_plugin->version);
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['categorie'] = !empty($cur_plugin->categorie) ? utf8_strtolower(mib_trim($cur_plugin->categorie)) : 'hidden';
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['title'] = $cur_plugin->title->{$MIB_PAGE['lang']} ? mib_trim($cur_plugin->title->{$MIB_PAGE['lang']}) : mib_trim($cur_plugin->title->{MIB_LANG});
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['description'] = $cur_plugin->description->{$MIB_PAGE['lang']} ? mib_trim($cur_plugin->description->{$MIB_PAGE['lang']}) : mib_trim($cur_plugin->description->{MIB_LANG});

							if(file_exists(MIB_PATH_SYS.'plugins/'.$entry.'/'.$entry.'.png'))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['favicon'] = MIB_URL_SYS.'/plugins/'.$entry.'/'.$entry.'.png';
							if(file_exists(MIB_PATH_SYS.'plugins/'.$entry.'/'.$entry.'_manage.php'))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['admin_manage'] = true;

							// Ajoute les configs de la DB
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['configs'] = array();
							if(!empty($MIB_PLUGINS[$cur_plugin_uid]))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['configs'] = $MIB_PLUGINS[$cur_plugin_uid];
						}
					}
				}
			}
		}
		$d->close();

		// Chargement des plugins website
		$d = dir(MIB_PATH_VAR.'plugins');
		while (($entry = $d->read()) !== false) {
			if ($entry{0} != '.' && is_dir(MIB_PATH_VAR.'plugins/'.$entry)) {
				$MIB_PLUGINS_CONFIGS[$entry] = array();

				if (preg_match('/[^0-9a-z_]/', $entry))
					$MIB_PLUGINS_CONFIGS[$entry]['error'][] = 'UID Illegal.';
				else {
					if (!file_exists(MIB_PATH_VAR.'plugins/'.$entry.'/'.$entry.'.xml'))
						$MIB_PLUGINS_CONFIGS[$entry]['error'][] = 'Fichier du plugin configuration manquant.';
					// Charge le XML
					else {
						$cur_plugin = @simplexml_load_file(MIB_PATH_VAR.'plugins/'.$entry.'/'.$entry.'.xml');
						if(isset($cur_plugin[0])) { // la config du plugin à été chargé
							$cur_plugin = $cur_plugin[0];
							$cur_plugin_uid = $entry;

							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['position'] = isset($MIB_PLUGINS[$cur_plugin_uid]['position']) ? intval($MIB_PLUGINS[$cur_plugin_uid]['position']) : 0;
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['uid'] = $cur_plugin_uid;
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['version'] = mib_trim($cur_plugin->version);
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['categorie'] = !empty($cur_plugin->categorie) ? utf8_strtolower(mib_trim($cur_plugin->categorie)) : 'hidden';
							$title = $cur_plugin->title->{$MIB_PAGE['lang']} ? mib_trim($cur_plugin->title->{$MIB_PAGE['lang']}) : mib_trim($cur_plugin->title->{MIB_LANG});
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['title'] = (string) $title;
                            $description =$cur_plugin->description->{$MIB_PAGE['lang']} ? mib_trim($cur_plugin->description->{$MIB_PAGE['lang']}) : mib_trim($cur_plugin->description->{MIB_LANG});
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['description'] = (string) $description;

							if(file_exists(MIB_PATH_VAR.'plugins/'.$entry.'/'.$entry.'.png'))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['favicon'] = MIB_URL_VAR.'/plugins/'.$entry.'/'.$entry.'.png';
							if(file_exists(MIB_PATH_VAR.'plugins/'.$entry.'/'.$entry.'_manage.php'))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['admin_manage'] = true;

							// Ajoute les configs de la DB
							$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['configs'] = array();
							if(!empty($MIB_PLUGINS[$cur_plugin_uid]))
								$MIB_PLUGINS_CONFIGS[$cur_plugin_uid]['configs'] = $MIB_PLUGINS[$cur_plugin_uid];
						}
					}
				}
			}
		}
		$d->close();

		// Effectue un trie en fonction de la position trouvée
		foreach ($MIB_PLUGINS_CONFIGS as $key => $row) {  // Obtient une liste de la colonne position
			$position[$key] = isset($row['position']) ? $row['position'] : 0;
			$categorie[$key] = isset($row['categorie']) ? $row['categorie'] : 'hidden';
		}
		array_multisort($categorie, SORT_ASC, $position, SORT_ASC, $MIB_PLUGINS_CONFIGS); // Ordonne le résultat

	}

	// Renvois les infos d'un seul plugin
	if($uid) {
		$uid = utf8_strtolower(mib_trim($uid));

		if($return) {
			$return = utf8_strtolower(mib_trim($return));
			if($return == 'uid')
				return $uid;
			else if($MIB_PLUGINS_CONFIGS[$uid][$return])
				return $MIB_PLUGINS_CONFIGS[$uid][$return];
			else
				return false;
		}
		else {
			if($MIB_PLUGINS_CONFIGS[$uid])
				return $MIB_PLUGINS_CONFIGS[$uid];
			else
				return false;
		}
	}
	else if($return)
		return false;

	return $MIB_PLUGINS_CONFIGS;
}

/**
 * Vérifie si un plugin est ok pour le BO
 * 
 * @param string $uid UID du plugin
 *
 * @return bool
 */
function is_valid_bo_plugin($uid) {
	$plugin_info = get_plugin($uid);

	if(empty($plugin_info['error']) && !empty($plugin_info['admin_manage'])) // Aucune erreur dans le plugin
		return true;

	return false;
}


/**
 * Retourne la liste des plugins disponible pour le BO
 *
 * @return {array}
 */
function get_plugin_bo_by_cat() {
	$return = array();

	$BO_cat = get_BO_cat();
	$plugins = get_plugin();

	foreach ( $BO_cat as $cat_uid => $cur_cat ) {
		foreach ( $plugins as $cur_plugin ) {
			if ( isset($cur_plugin['categorie']) && $cur_plugin['categorie'] == $cat_uid && is_valid_bo_plugin($cur_plugin['uid']) ) { // Aucune erreur dans le plugin
				$return[$cat_uid][$cur_plugin['uid']] = $cur_plugin;
			}
		}
	}

	return $return;
}

/**
 * Retourne les permissions du plugins
 * 
 * @param string $uid UID du plugin
 * @param string $type (read|write|config)
 *
 * @uses $MIB_USER
 *
 * @return bool
 *
 * @example
 *  get_plugin_bo_perms(); => false
 *  get_plugin_bo_perms('uid');
 *  get_plugin_bo_perms('uid', 'write');
 *  get_plugin('', 'read'); = > false
 */
function get_plugin_bo_perms($uid = false, $type = false) {
	global $MIB_USER;

	if(!$type) $type == 'read';

	if($uid && !$MIB_USER['is_guest']) { // uid + utilisateur logué
		if($MIB_USER['group_id'] == MIB_G_ADMIN) // Les admins on tout les droits
			return true;

		// Check les permissions du groupe
		$group_perms = $MIB_USER['g_bo_perms'];

		// récupère les permission de groupe
		if(!empty($group_perms)) { // Le groupe a des permissions
			@list($plugins_perms, $horaire_perms) = @explode('|', base64_decode($group_perms));
			$plugins_perms = @unserialize($plugins_perms);
		}
		// on valide que les infos de la db sont correctes
		$plugins_perms = (isset($plugins_perms) && is_array($plugins_perms)) ? $plugins_perms : array();

		if(array_key_exists($uid, $plugins_perms)) { // le groupe à bien des permissions définie pour ce plugin
			if(isset($plugins_perms[$uid][$type]) && $plugins_perms[$uid][$type] == 1)
				return true;
		}

		// Check les permissions de l'utilisateur
		$user_perms = $MIB_USER['bo_perms'];

		// récupère les permission de groupe
		if(!empty($user_perms)) { // Le groupe a des permissions
			@list($plugins_perms, $ip_perms) = @explode('|', base64_decode($user_perms));
			$plugins_perms = @unserialize($plugins_perms);
		}
		// on valide que les infos de la db sont correctes
		$plugins_perms = (isset($plugins_perms) && is_array($plugins_perms)) ? $plugins_perms : array();

		if(array_key_exists($uid, $plugins_perms)) { // l'utilisateur à bien des permissions définie pour ce plugin
			if(isset($plugins_perms[$uid][$type]) && $plugins_perms[$uid][$type] == 1)
				return true;
		}
	}

	return false;
}

/**
 * Valide les permissions du BO pour un utilisateur
 * 
 * @param string $user_id id de l'utilisateur
 *
 * @uses $MIB_DB
 * @uses $MIB_USER
 *
 * @return {array}
 */
function validate_bo_perms($user_id = null) {
	global $MIB_DB, $MIB_USER;

	if($user_id)
		$user_id = intval($user_id);

	$errors = array();

	if($user_id && $user_id != $MIB_USER['id']) { // id différent de l'utilisateur actuel
		// Récupère les premissions de connection pour cet utilisateur
		$query = array(
			'SELECT'	=> 'u.group_id, g.g_bo_perms',
			'FROM'		=> 'users AS u',
			'JOINS'		=> array(
				array(
					'INNER JOIN'	=> 'groups AS g',
					'ON'			=> 'g.g_id=u.group_id'
				)
			),
			'WHERE'		=> 'u.id='.$user_id
		);
		$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
		if($MIB_DB->num_rows($result))
			list($group_id, $group_perms) = $MIB_DB->fetch_row($result);
	}
	else {
		$user_id = $MIB_USER['id'];
		$group_id = $MIB_USER['group_id'];
		$group_perms = $MIB_USER['g_bo_perms'];
	}

	if(intval($group_id) != MIB_G_ADMIN) { // Ne prend pas en compte les admins qui ont tous les droits

		// récupère les permission de groupe
		if(!empty($group_perms)) { // Le groupe a des permissions
			@list($plugins_perms, $horaire_perms) = @explode('|', base64_decode($group_perms));
			$plugins_perms = @unserialize($plugins_perms);
			$horaire_perms = @unserialize($horaire_perms);
		}
		// on valide que les infos de la db sont correctes
		$plugins_perms = (isset($plugins_perms) && is_array($plugins_perms)) ? $plugins_perms : array();
		$horaire_perms = (isset($horaire_perms) && is_array($horaire_perms)) ? $horaire_perms : array();

		if(array_key_exists('bo', $plugins_perms)) { // le groupe à bien accès au BO
			$day = @date('l'); // jour actuel au format BO
			$time = intval(@date('Hi')); // heure actuel + minute au format du BO
			if(isset($horaire_perms[$day]['type']) && intval($horaire_perms[$day]['type']) > 0) {
				if(intval($horaire_perms[$day]['type']) == 2) { // un horaire est défini
					if($time < $horaire_perms[$day]['start'] || $time > $horaire_perms[$day]['finish'])
						$errors[] = sprintf(__('Vous n\'avez pas la permission d\'accéder au Back Office le %1$s à %2$s.'), __($day), format_time(time(), __('H:i'), true));
				}
			}
			else
				$errors[] = sprintf(__('Vous n\'avez pas la permission d\'accéder au Back Office le %s.'), __($day));
		}
		else
			$errors[] = __('Vous n\'avez pas la permission d\'accéder au Back Office.');
	}

	return $errors;
}

/**
 * Construction d'un formulaire
 * 
 * @param string $id id unique du formulaire
 * @param array $inputs_available champs du formulaire
 * @param string $inputs_to_use Champs du formulaire à utiliser (séparés par des virgules)
 * @param array $inputs_value valeur des champs du formulaire
 */
function mib_form_build($id, $inputs_available, $inputs_to_use = false, $inputs_value = array()) {
	global $MIB_CONFIG;

	$inputs_to_use = !empty($inputs_to_use) && $inputs_to_use != '*' ? explode(',', $inputs_to_use) : array_keys($inputs_available);

	foreach( $inputs_to_use as $name ) {
		$name = mib_trim($name);
		$uid = mib_uid($id.$name);

		if ( array_key_exists($name, $inputs_available) ) {
			$input = array_merge(array(
				'label'				=> '',
				'req'				=> false,
				'alert'				=> '',
				'description'		=> '',
				'type'				=> '',
				'html'				=> '',
				'options'			=> array(),
			), $inputs_available[$name]);

			echo '<div class="option-row" id="'.$uid.'-row">';

				echo '<div class="option-title">';
				if ( !empty($input['label']) ) echo '<label for="'.$uid.'">'.($input['req'] ? '<span class="req">*</span>' : '').$input['label'].'</label>';
				echo '</div>';

				echo '<div class="option-item">';
				if ( !empty($input['html']) )
					echo $input['html'];
				else {
					if ( $input['type'] == 'text' )
						echo mib_form_input_text($uid, $name, $inputs_value[$name], empty($input['default']) ? '':$input['default'] );
					else if ( $input['type'] == 'text-multilang' ) {
						foreach( $MIB_CONFIG['languages'] as $iso => $language ) {
							if ( $iso != MIB_LANG ) $uid = $uid.$iso;
							echo '<p><input type="text" class="input tips ico" style="width:50%;background-image:url(\'../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png\');box-sizing:border-box;" title="'.$language.'" id="'.$uid.'" name="'.$name.'_'.$iso.'" value="'.(!empty($inputs_value[$name.'_'.$iso]) ? mib_html($inputs_value[$name.'_'.$iso]) : '').'" size="50" maxlength="255"></p>';
						}
					}
					else if ( $input['type'] == 'number' )
						echo '<input type="text" class="input" id="'.$uid.'" name="'.$name.'" value="'.(!empty($inputs_value[$name]) ? mib_html($inputs_value[$name]) : (!empty($input['default']) ? intval($input['default']) : '')).'" size="10" maxlength="10">';
					else if ( $input['type'] == 'color' ) {
						if ( !empty($inputs_value[$name]) ) $inputs_value[$name] = preg_replace("/[^0-9A-Fa-f]/", '', $inputs_value[$name]);
						if ( !empty($input['default']) ) $input['default'] = preg_replace("/[^0-9A-Fa-f]/", '', $input['default']);
						echo '<label for="'.$uid.'" class="color"'.(!empty($inputs_value[$name]) ? ' style="background:#'.mib_html($inputs_value[$name]).'"' : (!empty($input['default']) ? ' style="background:#'.mib_html($input['default']).'"' : '')).'></label><input type="text" class="input colorpickers" id="'.$uid.'" name="'.$name.'" value="'.(!empty($inputs_value[$name]) ? '#'.mib_html($inputs_value[$name]) : (!empty($input['default']) ? '#'.mib_html($input['default']) : '')).'" size="8" maxlength="7">';
					}
					else if ( $input['type'] == 'textarea' ) {
						echo mib_form_input_textarea($uid, $name, $inputs_value[$name], $input['default']);
					}
					else if ( $input['type'] == 'textarea-multilang' ) {
						echo '<table><tr>';
						$i = 0;
						foreach( $MIB_CONFIG['languages'] as $iso => $language ) {
							$i++;
							if ( $iso != MIB_LANG ) $uid = $uid.$iso;
							echo '<td style="width:'.(100 / count($MIB_CONFIG['languages'])).'%;'.($i == 1 ? 'padding:0;' : 'padding:0 0 0 10px;').'">';
							echo '<label for="'.$uid.'"><img class="tips" title="'.mib_html($language).'" src="../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png" alt="'.mib_html($iso).'"></label><br>';
							echo mib_form_input_textarea($uid, $name.'_'.$iso, $inputs_value[$name.'_'.$iso], $input['default_iso'], $configs = array('width'=>'100%'));
							echo '</td>';
						}
						echo '</tr></table>';
					}
					else if ( $input['type'] == 'date' )
						echo '<input type="text" class="input datepickers" id="'.$uid.'" name="'.$name.'" value="'.(!empty($inputs_value[$name]) ? format_time($inputs_value[$name],__('Y-m-d'), true) : (!empty($input['default']) ? format_time($input['default'],__('Y-m-d'), true) : '')).'"  accept="'.__('Y-m-d').'" size="10" maxlength="10">';
					else if ( $input['type'] == 'lang' )
						echo mib_form_input_lang($uid, $name, $inputs_value[$name], $input['default']);
					else if ( $input['type'] == 'select' )
						echo mib_form_input_select($uid, $name, $input['options'], $inputs_value[$name], empty($input['default']) ? '':$input['default'], empty($input['custom']) ? '':$input['custom']);
					else if ( $input['type'] == 'time' ) {
						echo '<input type="text" class="input" id="'.$uid.'" name="'.$name.'" value="'.(!empty($inputs_value[$name]) ? format_hour($inputs_value[$name]) : (!empty($input['default']) ? format_hour($input['default']) : '')).'" size="5" maxlength="5">';
					}
					else if ( $input['type'] == 'number-minmax' ) {
						$min_name = !empty($input['options']['min_name']) ? $input['options']['min_name'] : $uid.'-min';
						$min_value = (isset($inputs_value[$min_name]) ? $inputs_value[$min_name] : (isset($input['default'][0]) ? $input['default'][0] : null));

						$max_name = !empty($input['options']['max_name']) ? $input['options']['max_name'] : $uid.'-max';
						$max_value = (isset($inputs_value[$max_name]) ? $inputs_value[$max_name] : (isset($input['default'][1]) ? $input['default'][1] : null));

						echo mib_form_input_number_minmax($uid, $min_name, $max_name, $min_value, $max_value, $input['options']);
					}
					else if ( $input['type'] == 'radio' )
						echo mib_form_input_radio($uid, $name, $input['options'], $inputs_value[$name], $input['default']);

					if ( !empty($input['alert']) )
						echo '<div class="alert">'.$input['alert'].'</div>';
					if ( !empty($input['description']) )
						echo '<p>'.$input['description'].'</p>';
				}
				echo '</div>';
			echo '</div>';
		}
	}
}

/**
 * Nombres Min et Max
 * 
 * <input type="text"> <input type="text">
 */
function mib_form_input_number_minmax($id, $min_name, $max_name, $min_value = null, $max_value = null, $options = array()) {
	$number_minmax = '';

	$min_input = '<input type="text" class="input" id="'.$id.$min_name.'" name="'.$min_name.'" value="'.(!is_null($min_value) ? intval($min_value) : '').'" size="10" maxlength="10">';
	$number_minmax .= !empty($options['min_label']) ? '<label for="'.$id.$min_name.'">'.str_replace('[[%input%]]', $min_input, $options['min_label']).'</label>' : $min_input;

	$number_minmax .= ' ';

	$max_input = '<input type="text" class="input" id="'.$id.$max_name.'" name="'.$max_name.'" value="'.(!is_null($max_value) ? intval($max_value) : '').'" size="10" maxlength="10">';
	$number_minmax .= !empty($options['max_label']) ? '<label for="'.$id.$max_name.'">'.str_replace('[[%input%]]', $max_input, $options['max_label']).'</label>' : $max_input;

	return $number_minmax;
}
/**
 * <input type="text"> 
 */
function mib_form_input_text($id, $name, $value = false, $default = false, $configs = array()) {

	$configs = array_merge(array(
		'class'					=> 'input',
		'width'					=> '50%',
	), $configs);

	return '<input type="text" class="'.$configs['class'].'" style="width:'.$configs['width'].';box-sizing:border-box;" id="'.$id.'" name="'.$name.'" value="'.(!empty($value) ? mib_html($value) : (!empty($default) ? mib_html($default) : '')).'">';
}
/**
 * <textarea>
 */
function mib_form_input_textarea($id, $name, $value = false, $default = false, $configs = array()) {

	$configs = array_merge(array(
		'class'					=> 'input',
		'width'					=> '50%',
		'rows'					=> '5',
	), $configs);

	return '<textarea class="'.$configs['class'].'" style="width:'.$configs['width'].';box-sizing:border-box;" rows="'.$configs['rows'].'" id="'.$id.'" name="'.$name.'">'.(!empty($value) ? mib_html($value) : (!empty($default) ? mib_html($default) : '')).'</textarea>';
}
/**
 * <select> 
 */
function mib_form_input_select($id, $name, $options, $value = false, $default = false, $custom = false, $configs = array()) {
	$configs = array_merge(array(
		'class'					=> 'input',
		'width'					=> '25%',
		'width_custom'			=> '25%',
	), $configs);

	$select = '<select class="'.$configs['class'].'" style="width:'.$configs['width'].';box-sizing:border-box;" id="'.$id.'" name="'.$name.'"'.(!empty($custom) ? ' onchange="$(\''.$id.'_custom\').set(\'value\', \'\');"' : '').'>';
	$select .= '<option value=""></option>';
	foreach ( $options as $l => $o ) {
		if ( is_string($l) ) {
			$select .= '<optgroup label="'.mib_html($l).' :">';
			if ( is_array($o) ) {
				foreach ( $o as $o_id => $o_name )
					$select .= '<option value="'.$o_id.'"'.(!empty($value) && $value == $o_id ? ' selected="selected"' : (!empty($default) && empty($value) && $default == $o_id ? ' selected="selected"' : '')).'>'.mib_html($o_name).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>';
			}
			$select .= '</optgroup>';
		}
	}
	$select .= '</select>';

	if ( !empty($custom) ) {
		$select .= ' '.$custom.' <input type="text" class="input" style="width:'.$configs['width_custom'].';" id="'.$id.'_custom" name="'.$name.'_custom" value="'.(!empty($value) && !mib_multi_array_key_exists($value, $options) ? mib_html($value) : '').'">';
	}

	return $select;
}
/**
 * <input type="radio"> 
 */
function mib_form_input_radio($id, $name, $options, $value = false, $default = false) {
	$radio = '<p>';

	foreach ( $options as $k => $l ) {
		if ( $k != $options[0] )
			$uid = $id.$k;
		else
			$uid = $id;

		if ( !empty($value) && $value == $k || !empty($default) && $default == $k || empty($value) && $k == '*' )
			$checked = ' checked="checked"';
		else
			$checked = '';

		$radio .= '<input type="radio" class="input" id="'.$uid.'" name="'.$name.'" value="'.$k.'"'.$checked.'><label for="'.$uid.'">'.$l.'</label> ';
	}

	$radio .= '</p>';

	return $radio;
}
/**
 * <input type="radio"> 
 */
function mib_form_input_lang($id, $name, $value = false, $default = false) {
	global $MIB_CONFIG;

	$input_lang = '';
	if ( count($MIB_CONFIG['languages']) > 1 ) {
		foreach( $MIB_CONFIG['languages'] as $iso => $language ) {
			$id_lang = $id;
			if ( $iso != MIB_LANG ) $id_lang = $id.$iso;
			$input_lang .= '<input type="radio" class="input" id="'.$id_lang.'" name="'.$name.'" value="'.$iso.'"'.(!empty($value) && $value == $iso ? ' checked="checked"' : (!empty($default) && $default == $iso ? ' checked="checked"' : '')).'><label for="'.$id_lang.'"><span class="ico" style="background-image:url(\'../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png\');">'.mib_html($language).'</span></label> ';
		}
	}
	else
		$input_lang .= '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.MIB_LANG.'"><span class="ico" style="background-image:url(\'../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.MIB_LANG.'.png\');">'.mib_html($MIB_CONFIG['languages'][MIB_LANG]).'</span>';

	return $input_lang;
}

/**
 * Construction d'un formulaire de filtre de recherche
 * 
 * @param string $id id unique du formulaire
 * @param array $inputs_to_use Champs à utilise
 *
 * @return array filters_result
 */
function mib_form_build_search_filters($id, $inputs_to_use = array()) {
	global $MIB_DB, $MIB_CONFIG;

	$filters_result = $where_sql = array();

	foreach( $inputs_to_use as $name => $input ) {
		if ( is_array($input) ) {
			$name = mib_trim($name);
			$id = mib_uid($id.$name);

			// START FILTER
			echo '<div class="option-row"><div class="option-title"><label for="'.$id.'">'.$input['label'].'</label></div><div class="option-item">';

			// affiche le champ de recherche
			if ( !empty($input['html']) ) {
				echo $input['html'];
			}
			else if ( $input['type'] == 'text' ) {
				$filters_result[$name] = (isset($_GET[$name])) ? mib_trim($_GET[$name]) : '';
				echo mib_form_input_text($id, $name, $filters_result[$name]);
			}
			else if ( $input['type'] == 'select' ) {
				$filters_result[$name] = (isset($_GET[$name]) && mib_multi_array_key_exists($_GET[$name], $input['options'])) ? mib_trim($_GET[$name]) : '';
				echo mib_form_input_select($id, $name, $input['options'], $filters_result[$name]);
			}
			else if ( $input['type'] == 'lang' && count($MIB_CONFIG['languages']) > 1 ) {
				$filters_result[$name] = (isset($_GET[$name]) && array_key_exists($_GET[$name], $MIB_CONFIG['languages'])) ? mib_trim($_GET[$name]) : '';

				echo '<input type="radio" name="'.$name.'" id="'.$id.'_all" value="" '.(empty($filters_result[$name]) ? 'checked="checked"' : '').' class="input"> <label for="'.$id.'_all">'.__bo('Toutes').'</label> ';
				echo mib_form_input_lang($id, $name, $filters_result[$name]);
			}
			else if ( $input['type'] == 'radio' ) {
				$filters_result[$name] = (isset($_GET[$name]) && array_key_exists($_GET[$name], $input['options'])) ? mib_trim($_GET[$name]) : '';
				echo mib_form_input_radio($id, $name, $input['options'], $filters_result[$name]);
			}

			// construction de la requette SQL
			if ( !empty($input['where_custom']) ) { // requette personalisée
				if ( is_array($input['where_custom']) && array_key_exists($filters_result[$name], $input['where_custom']) ) {
					$where_sql[] = $input['where_custom'][$filters_result[$name]];
				}
				else
					unset($filters_result[$name]);
			}
			else if ( $input['type'] == 'text' && !empty($filters_result[$name]) ) {
				$q = '%'.mib_trim($filters_result[$name], '*').'%';
				$where_cols = !empty($input['in']) ? explode(',', $input['in']) : array($name);
				if ( count($where_cols) > 1 ) {
					$where_req = array();

					foreach( $where_cols as $c ) {
						$where_req[] .= $c.' '.$input['where'].' \''.$MIB_DB->escape($q).'\'';
					}

					$where_sql[] = '('.implode(' OR ', $where_req).')';
				}
				else
					$where_sql[] = $where_cols[0].' '.$input['where'].' \''.$MIB_DB->escape($q).'\'';
			}
			else if ( !empty($input['where']) && $input['type'] == 'select' && !empty($filters_result[$name]) ) {
				$where_sql[] = $input['where'].'\''.$MIB_DB->escape($filters_result[$name]).'\'';
			}
			else if ( !empty($input['where']) && $input['type'] == 'lang' && !empty($filters_result[$name]) ) // Langue
				$where_sql[] = $input['where'].'\''.$MIB_DB->escape($filters_result[$name]).'\'';
			else if ( !empty($input['where']) && $input['type'] == 'radio' && !empty($filters_result[$name]) && $filters_result[$name] != '*' )
				$where_sql[] = $input['where'].'\''.$MIB_DB->escape($filters_result[$name]).'\'';
			else
				unset($filters_result[$name]);

			// END FILTER
			echo '</div></div>';
		}
	}

	return array('filters_result'=>$filters_result,'where_sql'=>$where_sql);
}

/**
 * Affiche le status
 * 
 * @param int $status
 * @param string $url
 */
function mib_form_status($status, $url = false) {
	if ( intval($status) > 0 ) {
		echo '<span class="tags tag-valid tips" title="'.__bo('Actif').'" rel="'.__bo('Depuis').' : '.time_ago($status).', '.__bo('le').' '.format_time($status, __('Y-m-d'), true).'">';
		if ( $url ) echo '<a href="'.$url.'" target="_json">';
		echo __bo('Actif');
		if ( $url ) echo '</a>';
		echo '</span>';
	}
	else {
		echo '<span class="tags tag-error">';
		if ( $url ) echo '<a href="'.$url.'" target="_json">';
		echo __bo('Inactif');
		if ( $url ) echo '</a>';
		echo '</span>';
	}
}

/**
 * Affiche le status avec la prise en compte de dates
 * 
 * @param int $status
 * @param int $date_start
 * @param int $date_end
 * @param string $url
 */
function mib_form_status_date($status, $date_start, $date_end = false, $url = false) {
	global $MIB_PAGE;

	if ( intval($status) > 0 ) {

		if ( intval($date_start) > $MIB_PAGE['time'] )
			echo '<span class="tags">'.($url ? '<a href="'.$url.'" target="_json">' : '').__bo('En attente');
		else if ( intval($date_end) != 0 && intval($date_end) < $MIB_PAGE['time'] )
			echo '<span class="tags tag-error tips" title="'.__bo('Terminé').'" rel="'.__bo('Depuis').' : '.time_ago($date_end).', '.__bo('le').' '.format_time($date_end, __('Y-m-d'), true).'">'.($url ? '<a href="'.$url.'" target="_json">' : '').__bo('Terminé');
		else
			echo '<span class="tags tag-valid tips" title="'.__bo('Actif').'" rel="'.__bo('Depuis').' : '.time_ago($date_start).', '.__bo('le').' '.format_time($date_start, __('Y-m-d'), true).'">'.($url ? '<a href="'.$url.'" target="_json">' : '').__bo('Actif');

		if ( $url ) echo '</a>';

		echo '</span>';
	}
	else {
		echo '<span class="tags tag-error">';
		if ( $url ) echo '<a href="'.$url.'" target="_json">';
		echo __bo('Inactif');
		if ( $url ) echo '</a>';
		echo '</span>';
	}
}

function mib_form_delete_button($type, $url, $title, $message = false) {
	if ( empty($message) ) $message = $title;

	$acp = __bo('Attention, toute suppression est définitive !');
	$acp .= '<br><br><strong>'.$message.'</strong>';

	if ( $type == 'button-secure' || $type == 'minbutton-secure')
		$acp = 'prompt="'.$acp.'<br><br>'.__bo('Par mesure de sécurité, veuillez saisir votre mot de passe actuel pour effectuer cette action.').'::'.$title.'::secure"';
	else
		$acp = 'confirm="'.$acp.'::'.$title.'"';

	// petit bouton dans tableau
	if ( $type == 'minbutton' || $type == 'minbutton-secure' ) {
		echo '<a href="'.$url.'" target="_json" class="tips iconimg delete" title="'.__bo('Supprimer').'" rel="'.$title.'" '.$acp.'></a>';
	}
	// bouton normal
	else {
		echo '<a href="'.$url.'" target="_json" class="button" '.$acp.'><span class="delete">'.__bo('Supprimer').'</span></a>';
	}
}

/**
 * Construction du système d'envois de photo
 * 
 * @param string $where path de destination de la photo
 * @param string $url url du formulaire d'upload
 * @param array $options
 */
function mib_photo_form_upload($where, $url, $options = array()) {
	$options = array_merge(array(
		'label'					=> false, // si le label est présent, alors on affiche la photo dans un formulaire
		'user_can_upload'		=> true, // peut envoyer/supprimer une photo
		'size'					=> '100x100', // dimmension d'affichage de la photo dans le BO
		'tips_title'			=> __bo('Modifier la photo'), // titre de l'info bulle
		'tips_description'		=> __bo('Envoyer une nouvelle photo'), // description de l'info bulle
		'popup_title'			=> __bo('Envoyer une nouvelle photo'), // titre de la popup d'upload
		'popup_description'		=> __bo('Veuillez sélectionner la nouvelle photo à envoyer.'), // description de la popup d'upload
		'placeholder_default'	=> false, // path du placeholder si ce n'est pas un placeholder automatique
		'placeholder_text'		=> false, // text du placeholder (uniquement si c'est un placeholder automatique)
		'placeholder_crop'		=> true, // si il y a un redimensionnement, est-ce qu'on découpe la photo ?
		'alert'					=> '',
		'description'			=> '',
		'delete_link'			=> false, // url du formulaire pour supprimer l'image
	), $options);

	$file_exists = file_exists(MIB_PUBLIC_DIR.$where);

	// on affiche uniquement si on peut up, ou si l'image existe
	if ( $options['can_upload'] || $file_exists ) {
		$options['sizes'] = explode('x', $options['size']);
		if ( count($options['sizes']) == 1) {
			$options['width'] = intval($options['sizes'][0]);
			$options['height'] = $options['width'];
		}
		else if ( count($options['sizes']) == 2) {
			$options['width'] = intval($options['sizes'][0]);
			$options['height'] = intval($options['sizes'][1]);
		}

		$file_url = '../../img/';
		$file_ext = false;

		if ( !$file_exists ) { // utilise le placeholder
			if ( $options['placeholder_default'] ) {
				$file_ext = substr(strrchr(strtolower($options['placeholder_default']),'.'), 1);
				$file_url .= substr($options['placeholder_default'], 0, -(strlen($file_ext)+1));
			}
		}
		else {
			$file_ext = substr(strrchr(strtolower($where),'.'), 1);
			$file_url .= substr($where, 0, -(strlen($file_ext)+1));
		}

		if ( $file_exists || $options['placeholder_default'] ) $file_url .= '-';

		$file_url .= $options['width'].'x'.$options['height'].'px'; 

		if ( !$options['placeholder_crop'] ) $file_url .= '-max';

		if ( $file_ext )
			$file_url .= '.'.$file_ext;
		else
			$file_url .= '.jpg';

		if ( !$file_exists && $options['placeholder_text'] ) $file_url .= '?t='.$options['placeholder_text'];

		if ( !empty($options['label']) ) {
?>
<div class="option-row">
	<div class="option-title"><?php echo $options['label']; ?></div>
	<div class="option-item">
<?php
		}

		if ( $options['can_upload'] ) {
			echo '<a class="tips" href="'.$url.'"'.(!empty($options['tips_title']) ? ' title="'.mib_html($options['tips_title']).'"' : '').(!empty($options['tips_description']) ? ' rel="'.mib_html($options['tips_description']).'"' : '').' upload="'.$options['popup_description'].(!empty($options['popup_title']) ? '::'.mib_html($options['popup_title']) : '').' ('.str_replace('M','Mo',ini_get('upload_max_filesize')).' max)">';
			echo '<img '.(!empty($options['label']) ? 'class="input"' : 'class="button-image"').' style="width:'.$options['width'].'px;height:'.$options['height'].'px;'.($file_exists && !empty($options['style']) ? $options['style'] : '').'" src="'.$file_url.'"'.(!empty($options['label']) ? ' alt="'.$options['label'].'"' : '').'>';
			echo '</a>';

			if ( $file_exists && !empty($options['label']) && $options['delete_link'] ) {
				echo '<br><a class="tips button minbutton" target="_json" style="margin:-55px 0 0 5px;" href="'.$options['delete_link'].'" title="'.__bo('Supprimer l\'image').'" confirm="'.__bo('Attention, toute suppression est définitive ! Souhaitez-vous supprimer l\'image ?').'::'.__bo('Supprimer l\'image ?').'">'.__bo('Supprimer').'</a>';
			}

			if ( !empty($options['alert']) )
				echo '<div class="alert">'.$options['alert'].'</div>';
			if ( !empty($options['description']) )
				echo '<p>'.$options['description'].'</p>';
		}
		else
			echo '<img '.(!empty($options['label']) ? 'class="input"' : 'class="button-image"').' style="width:'.$options['width'].'px;height:'.$options['height'].'px;'.($file_exists && !empty($options['style']) ? $options['style'] : '').'" src="'.$file_url.'"'.(!empty($options['label']) ? ' alt="'.$options['label'].'"' : '').'>';

		if ( !empty($options['label']) ) {
?>
	</div>
</div>
<?php
		}
	}
}