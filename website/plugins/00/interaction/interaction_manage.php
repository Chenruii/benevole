<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 15:44
 */

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

// charge les fonctions dédiés
if ( file_exists($MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php') ) require_once $MIB_PLUGIN['path'].$MIB_PLUGIN['name'].'_functions.php';

// table utilisée dans la base de données
$MIB_PLUGIN['dbtable'] = 'interaction';


// champs de formulaire du plugin
$MIB_PLUGIN['inputs'] = array(
    'date'  =>array(
        'label'      =>__bo('Date'),
        'type'       =>'date',
    ),
    'type'  =>array(
        'label'      =>__bo('Type'),
        'type'       =>'text'
    ),
    'description'  =>array(
        'label'      =>__bo('Description'),
        'type'       =>'text',
    ),
    'notes_interactions'  =>array(
        'label'      =>__bo('Notes'),
        'type'       =>'text',
    ),

);

$contact_id = NULL;
if( isset($_REQUEST['contact_id']) ){
    $contact_id = $_REQUEST['contact_id'];
}

if( $contact_id == NULL )
{
    $reverse = array_reverse( $MIB_PLUGIN['inputs'], true );
    $reverse['contact_id'] = array(
                                'label'             => __bo('Investor Contact'),
                                'type'              => 'select',
                                'options'           => array( 'contacts' => interaction_select_contact() )
                              );
    $MIB_PLUGIN['inputs'] = array_reverse( $reverse , true );
}
else
{
    $reverse = array_reverse( $MIB_PLUGIN['inputs'], true );
    $reverse['contact_id'] = array(
                                    'label'             => __bo('Investor Contact'),
                                    'type'              => 'select',
                                    'default'           => $contact_id,
                                    'options'           => array( 'contacts' => interaction_select_contact() )
                                );
    $MIB_PLUGIN['inputs'] = array_reverse( $reverse , true );
}

// séparation des actions par fichier pour une meilleur lisibilité
$MIB_PLUGIN['action'] = mib_get_request_infos();
if ( $MIB_PLUGIN['action'] && file_exists($MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php') ) {
    require_once $MIB_PLUGIN['path'].'manage/'.$MIB_PLUGIN['action'].'.php';

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
if ( $MIB_USER['can_write_plugin'] && file_exists($MIB_PLUGIN['path'].'manage/add.php') ) // l'utilisateur à les droits en écriture
    require_once $MIB_PLUGIN['path'].'manage/add.php';
if ( file_exists($MIB_PLUGIN['path'].'manage/search.php') )
    require_once $MIB_PLUGIN['path'].'manage/search.php';

