<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

$positions = mib_get_request_infos(2);

if ( !$MIB_USER['can_write_plugin'] ) error(__bo('Vous n\'avez pas la permission d\'effectuer cette action.'));

if ( defined('MIB_JSON') ) { // requète JSON
	if ( !defined('MIB_JSONED') ) define('MIB_JSONED', 1); // confirme qu'on renvoit du json

	// vérifie si on à la bonne URL sans "truc" en plus !
	mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/'.basename(__FILE__, '.php').'/'.$positions);

	$positions = explode('-', $positions);
	$positions_new = array();
	$i = 1;
	foreach( $positions as $id ) {
		if ( !is_valid_bo_plugin($id) ) // erreur, un des plugins n'est pas valide
			error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));

		$positions_new[$id] = $i;

		$i++;
	}

	// supprime les anciennes positions
	$query = array(
		'DELETE'	=> $MIB_PLUGIN['dbtable'],
		'WHERE'		=> 'conf_type=\'plugin\' AND conf_name=\'position\' AND conf_ref IN (\''.implode('\',\'',$positions).'\')'
	);
	$result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

	// ajoute les nouvelles positions
	foreach( $positions_new as $plugin => $position ) {
		$query = array(
			'INSERT'	=> 'conf_type, conf_ref, conf_name, conf_value',
			'INTO'		=> $MIB_PLUGIN['dbtable'],
			'VALUES'	=> '\'plugin\', \''.$MIB_DB->escape($plugin).'\', \'position\', '.$position
		);
		$MIB_DB->query_build($query) or error(__FILE__, __LINE__);
	}

	// re-génération du cache
	if ( !defined('MIB_LOADED_CACHE_FUNCTIONS') ) require MIB_PATH_SYS.'cache.php'; // si les fonctions de cache n'ont pas été chargées
	mib_generate_plugins_cache();

	$MIB_PLUGIN['json'] = array(
		'title'		=> __bo('Succès'),
		'value'		=> __bo('Le nouvel ordre de positionnement a été enregistré.'),
		'options'	=> array('type' => 'valid'),
		'page'		=> array(
			'position'	=> $MIB_PLUGIN['name'] // met à jour automatiquement la position des tableaux
		)
	);

	return;
}