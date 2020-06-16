<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

?>
<fieldset><legend><?php _ebo('Informations du serveur'); ?></legend>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Heure du serveur'); ?></div>
		<div class="option-item">
			<p><?php echo format_time(time(),__('Y-m-d').' - H:i',true); ?></p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Système d\'exploitation'); ?></div>
		<div class="option-item">
			<p><?php echo mib_html(PHP_OS); ?></p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Serveur web'); ?></div>
		<div class="option-item">
			<p><?php echo mib_html($_SERVER['SERVER_SOFTWARE']); ?></p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Version de PHP'); ?></div>
		<div class="option-item">
			<p><?php echo mib_html(PHP_VERSION); ?> - <a href="<?php echo $MIB_PLUGIN['name']; ?>/phpinfo" target="_blank">phpinfo()</a></p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Limite mémoire de PHP'); ?></div>
		<div class="option-item">
			<p><?php echo mib_html(@ini_get('memory_limit')); ?></p>
		</div>
	</div>
<?php if ( $server = mib_remote_address_infos() ): ?>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Adresse IP'); ?></div>
		<div class="option-item">
			<p><?php echo mib_html($server['query']); ?></p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('FAI'); ?></div>
		<div class="option-item">
			<p>
<?php
			echo mib_html($server['isp']);
			if ( $server['isp'] != $server['org'] ) echo ' / '.mib_html($server['org']);
?>
			</p>
		</div>
	</div>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Géolocalisation'); ?></div>
		<div class="option-item">
			<p>
				<a href="http://maps.google.com/maps?q=<?php echo mib_html($server['lat']); ?>,<?php echo mib_html($server['lon']); ?>" target="_blank">
<?php
					$address = array();
					
					if ( !empty($server['zip']) && !empty($server['city']) )
						$address[] = mib_html($server['zip']).' '.mib_html($server['city']);
					else if ( !empty($server['city']) )
						$address[] = mib_html($server['city']);

					if ( !empty($server['regionName']) )
						$address[] = mib_html($server['regionName']);

					if ( !empty($server['country']) )
						$address[] = mib_html($server['country']);

					echo implode('<br>',$address);
?>
				</a>
			</p>
		</div>
	</div>
<?php endif; ?>
</fieldset>