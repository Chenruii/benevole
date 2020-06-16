<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 15:51
 */

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

$action = basename(__FILE__, '.php');

if ( !$MIB_USER['can_write_plugin'] ) error(__bo('Vous n\'avez pas la permission d\'effectuer cette action.'));

if ( defined('MIB_JSON') ) { // requète JSON
    if ( !defined('MIB_JSONED') ) define('MIB_JSONED', 1); // confirme qu'on renvoit du json

    // un formulaire a été envoyé
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        // vérifie si on à la bonne URL sans "truc" en plus !
        mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/'.$action);

        // ajoute les infos en base de données
        if ( $added = bo_prospective_add($_POST, $MIB_PLUGIN['dbtable']) ) {
            $MIB_PLUGIN['json'] = array(
                'title'		=> __bo('success'),
                'value'		=> __bo('The addition has been made.'),
                'options'	=> array('type' => 'valid'),
                'page'		=> array(
                    'update'	=> $MIB_PLUGIN['name']
                )
            );
        }
        else
            mib_error_notify(); // affiche les erreurs rencontrées
    }
    return;
}
else if ( defined('MIB_AJAX') && !defined('MIB_AJAXED') ) // requète AJAX
    define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax
?>

<fieldset class="toggle"><legend><?php echo __('Add a new Prospective Investor'); ?></legend>
    <form method="post" action="<?php echo $MIB_PLUGIN['name']; ?>/<?php echo $action; ?>" target="_json">
        <?php mib_form_build($action, $MIB_PLUGIN['inputs'], '*'); ?>
        <div class="option-actions">
            <button type="submit" class="button"><span class="add"><?php echo __('Add a new Prospective Investor'); ?></span></button>
        </div>
    </form>
</fieldset>