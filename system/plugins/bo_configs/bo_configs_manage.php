<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

// charge les fonctions dédiés
if ( file_exists($MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php') ) require_once $MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php';

// configure pour afficher l'heure du serveur, sinon c'est l'heure de l'utilisateur qui s'affiche !
mib_date_timezone_set($MIB_CONFIG['server_timezone']);

// table utilisée dans la base de données
$MIB_PLUGIN['dbtable'] = 'configs';

// champs de formulaire du plugin
$MIB_PLUGIN['inputs'] = array(
	'site_title'		=> array(
		'label'				=> __bo('Titre'),
		'type'				=> 'text',
		'req'				=> true,
		'description'		=> __bo('Titre de référence du site. Ce champs <strong>ne doit pas</strong> contenir de HTML.'),
	),
	'site_email'		=> array(
		'label'				=> __bo('E-mail'),
		'type'				=> 'text',
		'req'				=> true,
		'description'		=> __bo('Adresse e-mail de contact générale utilisée lors de l\'envois d\'e-mails.'),
	),
	'bo_color'		=> array(
		'label'				=> __bo('Couleur du BO'),
		'type'				=> 'color',
		'description'		=> __bo('Couleur primaire utilisée pour le Back Office (liens, bordures, etc...).'),
	),
	'server_timezone'		=> array(
		'label'				=> __bo('Fuseau horaire'),
		'type'				=> 'select',
		'req'				=> true,
		'alert'				=> __bo('Date et heure actuelle du serveur :').' '.format_time($MIB_PAGE['time'], __('Y-m-d'), true).' - '.format_time($MIB_PAGE['time'], __('H:i'), true),
		'description'		=> __bo('Le fuseau horaire du serveur où est installé Mibbo.'),
		'options'			=> mib_date_timezones(),
	),
	'timeout_visit'		=> array(
		'label'				=> __bo('Temps mort de visite'),
		'type'				=> 'number',
		'req'				=> true,
		'default'			=> 1800,
		'description'		=> __bo('Nombre de secondes d\'inactivité d\'un utilisateur avant que les données de sa dernière visite soient mises à jours (affecte principalement les indicateurs de nouvelle visite).'),
	),
	'timeout_online'		=> array(
		'label'				=> __bo('Temps mort en ligne'),
		'type'				=> 'number',
		'req'				=> true,
		'default'			=> 600,
		'description'		=> __bo('Nombre de secondes d\'inactivité d\'un utilisateur avant qu\'il soit supprimé de la liste des utilisateurs en ligne.'),
	),
);

// séparation des actions par fichier pour une meilleur lisibilité
$MIB_PLUGIN['action'] = mib_get_request_infos();
if ( $MIB_PLUGIN['action'] && file_exists($MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php') ) {
	require_once $MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php';

	// remet le bon fuseau horraire
	mib_date_timezone_set($MIB_USER['timezone']);

	return;
}
// erreur, l'action n'a pas de fichier correspondant
else if ( $MIB_PLUGIN['action'] ) {
	if ( defined('MIB_JSON') )
		define('MIB_JSONED', 1);
	else
		define('MIB_AJAXED', 1);

	error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));
}
else if ( !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax pour la page d'accueil du plugin

/*
	Accueil
*/
if ( file_exists($MIB_PLUGIN['path'].'manage/configs.php') )
	require_once $MIB_PLUGIN['path'].'manage/configs.php';