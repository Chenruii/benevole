<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 27/03/19
 * Time: 16:12
 */
defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

$action = basename(__FILE__, '.php');

if ( !$MIB_USER['can_write_plugin'] ) error(__bo('Vous n\'avez pas la permission d\'effectuer cette action.'));
if ( !$cur_result = mib_db_get_row_from_table($MIB_PLUGIN['dbtable'], mib_get_request_infos(2)) ) error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));

if ( defined('MIB_JSON') ) { // requète JSON
    if ( !defined('MIB_JSONED') ) define('MIB_JSONED', 1); // confirme qu'on renvoit du json

    // un formulaire a été envoyé
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        // vérifie si on à la bonne URL sans "truc" en plus !
        mib_confirm_current_url($MIB_PAGE['base_url'].'/json/'.$MIB_PLUGIN['name'].'/'.$action.'/'.$cur_result['id']);

        // modification des infos en base de données
        if ( $updated = bo_prospective_update($cur_result['id'], $_POST, $MIB_PLUGIN['dbtable']) ) {
            $MIB_PLUGIN['json'] = array(
                'title'		=> __bo('success'),
                'value'		=> __bo('The modification has been made.'),
                'options'	=> array('type' => 'valid'),
                'page'		=> array(
                    'update'	=> $MIB_PLUGIN['name'],
                    'replace'	=> array(
                        '_self' => $MIB_PLUGIN['name'].'/view/'.$cur_result['id']
                    )
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
<style>
    fieldset.overview{
        float: left;
        width: 45%;
        text-align: left;
    }
    fieldset.history{
        float: right;
        width: 50%;
        text-align: left;
    }
    textarea{
        width: 120%;
        /*margin-left: 20.5%;*/
        height: 140%;
        padding-top: 10%;
        margin-top: 20px;
        line-height: 1.5;
        font-size: .8rem;
        letter-spacing: 1px;
    }
</style>
<fieldset><legend>Investor Details</legend>
    <form method="post" action="<?php echo $MIB_PLUGIN['name'].'/'.$action.'/'.$cur_result['id']; ?>" target="_json" >
        <?php mib_form_build($action, $MIB_PLUGIN['inputs'], '*', $cur_result); ?>
        <div class="option-actions" >
            <a href="<?php echo $MIB_PLUGIN['name'].'/view/'.$cur_result['id']; ?>" target="_self" class="button"><span class="back"><?php _ebo('Step back'); ?></span></a>
            <button type="submit" class="button"><span class="save"><?php _ebo('Save'); ?></span></button>
        </div>
    </form>


</fieldset>


<fieldset><legend><?php _ebo('Delete'); ?> :</legend>
    <?php if ( $MIB_USER['can_write_plugin'] ): ?>
        <div class="option-actions">
            <?php mib_form_delete_button('button-secure', $MIB_PLUGIN['name'].'/delete/'.$cur_result['id'], __bo('Delete a  Prospective').' : '.mib_html($cur_result['name'])); ?>
        </div>
    <?php endif; ?>
</fieldset>
