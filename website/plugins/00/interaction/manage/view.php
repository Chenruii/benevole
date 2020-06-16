<?php
/**
 * Created by PhpStorm.
 * User: mibbo
 * Date: 29/03/19
 * Time: 17:30
 */

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

if ( !$cur_result = mib_db_get_row_from_table($MIB_PLUGIN['dbtable'], mib_get_request_infos(2)) ) error(__bo('Le lien que vous avez suivi est incorrect ou périmé.'));

if ( defined('MIB_AJAX') && !defined('MIB_AJAXED') ) define('MIB_AJAXED', 1); // confirme qu'on renvoit de l'ajax

$contact = mib_db_get_row_from_table('contact', $cur_result['contact_id']);

?>
<fieldset><legend><?php _ebo('Interaction'); ?> :</legend>
    <div class="option-row">
        <div class="option-title">Investor Contact</div>
        <div class="option-item">
            <p><?php echo mib_html(ucfirst($contact['surname'])." ".ucfirst($contact['firstname'])); ?></p>
        </div>
    </div>
    <?php if ( !empty($cur_result['date']) ): ?>
        <div class="option-row">
            <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['date']['label']; ?></div>
            <div class="option-item">
                <p><?php echo mib_html($cur_result['date']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    <?php if ( !empty($cur_result['type']) ): ?>
        <div class="option-row">
            <div class="option-title"><?php echo $MIB_PLUGIN['inputs']['type']['label']; ?></div>
            <div class="option-item">
                <p><?php echo mib_html($cur_result['type']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    <?php if ( !empty($cur_result['contact_alpha']) ): ?>
        <div class="option-row">
            <div class="option-title">Contact Alpha</div>
            <div class="option-item">
                <p><?php echo mib_html($cur_result['contact_alpha']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    <?php if ( !empty($cur_result['notes_interactions']) ): ?>
        <div class="option-row">
            <div class="option-title">Notes</div>
            <div class="option-item">
                <p><?php echo mib_html($cur_result['notes_interactions']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( $MIB_USER['can_write_plugin'] ): ?>
        <div class="option-actions">
            <a href="<?php echo $MIB_PLUGIN['name'].'/edit/'.$cur_result['id']; ?>" target="_self" class="button"><span class="edit"><?php _ebo('Edit'); ?></span></a>
        </div>
    <?php endif; ?>
</fieldset>
<fieldset><legend><?php _ebo('Delete'); ?> :</legend>
    <?php if ( $MIB_USER['can_write_plugin'] ): ?>
        <div class="option-actions">
            <?php mib_form_delete_button('button-secure', $MIB_PLUGIN['name'].'/delete/'.$cur_result['id'], __bo('Delete').' : '.mib_html($cur_result['name'])); ?>
        </div>
    <?php endif; ?>
</fieldset>