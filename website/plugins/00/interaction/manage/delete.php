<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 29/03/19
 * Time: 17:30
 */


defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

if ( !$MIB_USER['can_write_plugin'] ) error(__bo('Vous n\'avez pas la permission d\'effectuer cette action.'));
if ( !$cur_result = mib_db_get_row_from_table($MIB_PLUGIN['dbtable'], mib_get_request_infos(2)) ) error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));

if ( defined('MIB_JSON') ) { // requète JSON
    if ( !defined('MIB_JSONED') ) define('MIB_JSONED', 1); // confirme qu'on renvoit du json

    // vérifie si on à la bonne URL sans "truc" en plus !
    mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/'.basename(__FILE__, '.php').'/'.$cur_result['id'].'?secure='.$_GET['secure']);

    // Vérification du MDP
    if( mib_hmac(mib_trim($_GET['secure']), $MIB_USER['salt']) == $MIB_USER['password'] ) {

        if ( $deleted = interaction_delete($cur_result['id'], $MIB_PLUGIN['dbtable']) ) {
            $MIB_PLUGIN['json'] = array(
                'title'		=> __bo('success'),
                'value'		=> __bo('Permanent deletion carried out.'),
                'options'		=> array('type' => 'valid'),
                'page'		=> array(
                    'update'		=> $MIB_PLUGIN['name'],
                    'remove'		=> $MIB_PLUGIN['name'].'/view/'.$cur_result['id']
                )
            );
        }
        else
            mib_error_notify(); // affiche les erreurs rencontrées
    }
    else
        error(__bo('Your password is incorrect!'));

    return;
}