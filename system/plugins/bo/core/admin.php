<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;
defined('MIB_MANAGE') or exit;

// as t'on un favicon personalisé pour le site ?
if ( file_exists(MIB_THEME_DIR.'img/logo-admin-square.png') )
	$favicon = 'logo-admin-square-20px.png';
else
	$favicon = false;

// a t'on un logo personalisé pour le template par defaut ?
if ( file_exists(MIB_THEME_DIR.'img/logo-admin-full.png') )
	$logo = 'logo-admin-full-200px-max.png';
else if ( file_exists(MIB_THEME_DEFAULT_DIR.'img/logo-mibbo-full.png') )
	$logo = 'logo-mibbo-full-200px-max.png';
else
	$logo = false;

?>

<?= MibboFormManager::getCssAndJsLInks() ?>


<script src="js/mootools-1.2.6-core.min.js"></script>
<script src="js/mootools-1.2.2.2-more.js"></script>
<script src="js/MIB_admin-1.2.1-core.js"></script>

<!-- -prefix-free, http://leaverou.github.io/prefixfree/ -->
<script src="js/prefixfree.min.js"></script>
<script src="js/prefixfree.dynamic-dom.min.js"></script>

<?php
// Fichier JS personnalisé dédié à l'admin
if( file_exists(MIB_THEME_DIR.'js/mibbo-admin.js') )
	echo '<script src="js/mibbo-admin.js"></script>';
?>

<iframe style="display:none" src="about:blank" id="MIB_upload_iframe" name="MIB_upload_iframe"></iframe>

<div id="MIB_loader"></div>

<div id="MIB_head">
	<p class="site_info" <?php if($favicon) { echo 'style="padding-left:25px;background-image: url(\'../../img/theme/'.$favicon.'\');"'; } ?> >
		<a class="tips" title="Front Office" rel="Accès au Front Office <?php echo mib_html($MIB_CONFIG['site_title']); ?>." href="<?php echo $MIB_CONFIG['base_url']; ?>" target="_blank" id="MIB_site_title"><?php echo mib_html($MIB_CONFIG['site_title']); ?></a>
	</p>
	<ul id="MIB_userbar">
		<li><a href="bo/profile" class="tips" title="Mon profil" rel="Editez vos informations personnelles." favicon="{{tpl:MIB_THEME}}/admin/img/icons/user_silhouette.png"><strong id="MIB_user_username"><?php echo mib_html($MIB_USER['username']); ?></strong></a> (<span id="MIB_user_email"><?php echo mib_html($MIB_USER['email']); ?></span>)</li>
		<li><a confirm="Se déconnecter du Back Office ?::Déconnexion::logout" href="bo/logout/<?php echo $MIB_USER['id']; ?>/<?php echo mib_hmac(mib_remote_address(), $MIB_USER['salt']); ?>" target="_json" class="button minbutton">Déconnexion</a></li>
		<li><a alert="<a href='//mibbo.net' target='_blank'><img src='../img/theme/logo-mibbo-full-250px-max.png'></a><br><br><?php echo mib_sprintftpl(__('Développée par la société française 2BO&CO, [[%sys_name%]] est une solution open source permettant d\'accélérer la création, le développement et la gestion de sites internet de qualité.'), array('sys_name'=>MIB_SYS_NAME)); ?>::<?php e_html(MIB_SYS_NAME.' '.MIB_SYS_VERSION); ?>" class="button minbutton">?</a></li>
	</ul>
	<hr class="clear">
</div>

<div id="MIB_menu" >
		<ul id="MIB_menuOptions" class="menu">
			<li><div id="MIB_pannelToggle" class="iconimg closed" ></div></li>
			<li id="tab___bo/dashboard" class="selected"><a href="bo/dashboard"><span>Tableau de bord</span></a></li>
			<li id="MIB_menuPreviewToggle"><div class="iconimg" ></div></li>
		</ul>
		<ul id="MIB_menuPages" class="menu"></ul>
		<hr class="clear">
</div>

<div id="MIB_pannel">
<?php

if( $logo ) {
?>
	<div style="text-align:center;margin: 20px;overflow: hidden;">
		<img src="../../img/theme/<?php echo $logo; ?>" style="max-width:100%;" alt="<?php echo mib_html($MIB_CONFIG['site_title']); ?>"></a>
	</div>
<?php
}

	$admin_cats = get_BO_cat(); // Catégories
	if(!is_array($admin_cats))
		error(sprintf(__('Impossible de charger le fichier des catégories %s.'), '<code>admin_categories.xml</code>'), __FILE__, __LINE__);
	$admin_plugins = get_plugin(); // Plugins

	/*
		On affiche le tout en fonction des droits
		ATTENTION :
			La catégorie doit :
				uid != hidden
			Le plugin doit :
				dans les permissions : $MIB_USER['bo_perms'] || $MIB_USER['g_bo_perms']
	*/
	$num_plugins_authorized = 0;
	foreach ($admin_cats as $cat_uid => $cur_cat) {
		if($cat_uid != 'hidden') { // Permission de la catégorie
			$num_plugins_in_cat = 0;
			foreach ($admin_plugins as $cur_admin_plugin) {
				if(isset($cur_admin_plugin['categorie']) && $cur_admin_plugin['categorie'] == $cat_uid && is_valid_bo_plugin($cur_admin_plugin['uid']) && get_plugin_bo_perms($cur_admin_plugin['uid'], 'read')) { // Aucune erreur dans le plugin
					$num_plugins_in_cat++;
					$num_plugins_authorized++;
				}
			}
			if ($num_plugins_in_cat > 0) { //Si la catégorie contient des plugins valides
				echo "\t".'<h2 class="toggler headbar bdinbox">'.mib_html($cur_cat['title']).'</h2>'."\n";
				echo "\t".'<div class="element">'."\n";
				if(!empty($cur_cat['description'])) // affiche la description si il y en a une
					echo "\t\t".'<div class="message"><p>'.mib_html($cur_cat['description']).'</p></div>'."\n";
				echo "\t\t".'<ul>'."\n";

				foreach ($admin_plugins as $cur_admin_plugin) {
					if(isset($cur_admin_plugin['categorie']) && $cur_admin_plugin['categorie'] == $cat_uid && is_valid_bo_plugin($cur_admin_plugin['uid']) && get_plugin_bo_perms($cur_admin_plugin['uid'], 'read')) { // Aucune erreur dans le plugin
						$cur_admin_plugin['favicon'] = !empty($cur_admin_plugin['favicon']) ? ' favicon="../../'.$cur_admin_plugin['favicon'].'" style="background-image:url(\'../../'.$cur_admin_plugin['favicon'].'\');" ' : '';
						$cur_admin_plugin['admin_write'] = !get_plugin_bo_perms($cur_admin_plugin['uid'], 'write') ? '<span class="iconimg nowrite tips" title="Lecture seule" >lck</span>' : '';

						echo "\t\t\t".'<li>'."\n";
						echo "\t\t\t\t".'<a href="'.$cur_admin_plugin['uid'].'" class="tips" title="'.mib_html($cur_admin_plugin['title']).'" rel="'.mib_html($cur_admin_plugin['description']).'" '.$cur_admin_plugin['favicon'].' >';
						echo $cur_admin_plugin['admin_write'];
						echo mib_html($cur_admin_plugin['title']).'</a>'."\n";
						echo "\t\t\t".'</li>'."\n";
					}
				}

				echo "\t\t".'</ul>'."\n";
				echo "\t".'</div>'."\n";
			}
		}
	}

	if($num_plugins_authorized == 0) { // l'utilisateur n'a accès à aucun plugin (mais à bien accès au BO !)
		echo "\t".'<h2 class="toggler headbar bdinbox">'.__('Aucun plugin disponible').'</h2>'."\n";
		echo "\t".'<div class="element">'."\n";
		echo "\t\t".'<div class="message error"><p>Aucun plugin du Back Office n\'est disponible.</p></div>'."\n";
		echo "\t".'</div>'."\n";
	}
?>
</div>

<div id="MIB_separator"></div>

<div id="MIB_page" class="page"></div>

<script type="text/javascript">
	var MIB_Bo, MIB_secureIdle;
	window.addEvent('domready', function(){
		// Lance le BO
		MIB_Bo = new MIB_BackOffice({'dashboardId': 'bo/dashboard', 'dashboardTitle': 'Tableau de bord'});
		// Configure le datepicker en fonction de la langue
		MIB_Bo.datePickers.options.dateformat = '<?php echo mib_jsspecialchars(__('Y-m-d')); ?>';
		MIB_Bo.datePickers.options.days = [
			'<?php echo mib_jsspecialchars(__('Sunday')); ?>',
			'<?php echo mib_jsspecialchars(__('Monday')); ?>',
			'<?php echo mib_jsspecialchars(__('Tuesday')); ?>',
			'<?php echo mib_jsspecialchars(__('Wednesday')); ?>',
			'<?php echo mib_jsspecialchars(__('Thursday')); ?>',
			'<?php echo mib_jsspecialchars(__('Friday')); ?>',
			'<?php echo mib_jsspecialchars(__('Saturday')); ?>'
		];
		MIB_Bo.datePickers.options.months = [
			'<?php echo mib_jsspecialchars(__('January')); ?>',
			'<?php echo mib_jsspecialchars(__('February')); ?>',
			'<?php echo mib_jsspecialchars(__('March')); ?>',
			'<?php echo mib_jsspecialchars(__('April')); ?>',
			'<?php echo mib_jsspecialchars(__('May')); ?>',
			'<?php echo mib_jsspecialchars(__('June')); ?>',
			'<?php echo mib_jsspecialchars(__('July')); ?>',
			'<?php echo mib_jsspecialchars(__('August')); ?>',
			'<?php echo mib_jsspecialchars(__('September')); ?>',
			'<?php echo mib_jsspecialchars(__('October')); ?>',
			'<?php echo mib_jsspecialchars(__('November')); ?>',
			'<?php echo mib_jsspecialchars(__('December')); ?>'
		];

		// Lance la sécuritée d'inactivitée qui déco un utilisateur inactif de plus de $MIB_CONFIG['timeout_online']
		MIB_secureIdle = new MIB_IdleNotifier(<?php echo ($MIB_CONFIG['timeout_online'] - 10); ?>);
		MIB_secureIdle.addEvent('idle', function(){ // Quand un utilisateur devient inactif
			if(!MIB_Bo.idle) {
				MIB_Bo.idle = true; // "Active" l'inactivité du BO

				// On le déconnecte
				MIB_Bo.load('bo/idle/<?php echo $MIB_USER['id']; ?>/<?php echo mib_hmac(mib_remote_address(), $MIB_USER['salt']); ?>',{target: '_json', method: 'post', data: 'logout=<?php echo urlencode(base64_encode($MIB_USER['email'])); ?>'});

				$prompt('Suite à une période d\'inactivitée vous avez été déconnecté du Back Office par mesure de sécuritée.<br><br>Veuillez indiquer votre mot de passe pour vous reconnecter.', '<span class="key"><?php echo mib_jsspecialchars(mib_html($MIB_USER['username'])); ?></span>', {
					id: 'idle-box',
					hide: false,
					type: 'password',
					onClose: function(value) {
						if(value && value != '') {// On lance la connexion
							value = value.replace(/\&/g, '%26');
							value = value.replace(/\=/g, '%3D');
							value = value.replace(/\+/g, '%2B');

							MIB_Bo.load('bo/idle/<?php echo $MIB_USER['id']; ?>/<?php echo mib_hmac(mib_remote_address(), $MIB_USER['salt']); ?>',{target: '_json', method: 'post', data: 'login=<?php echo urlencode(base64_encode($MIB_USER['email'])); ?>' + '&password=' + value});
						}else
							window.location.href = '<?php echo $MIB_PAGE['base_url']; ?>';
					}
				});
			}
		});

		// Affiche le message d'accueil du BO
		$growl('Bonjour <strong><?php echo mib_jsspecialchars(mib_html($MIB_USER['username'])); ?></strong><br>Votre dernière visite : <?php echo mib_jsspecialchars(format_time($MIB_USER['last_visit'])); ?>', 'Bienvenue sur le Back Office <?php echo mib_jsspecialchars(mib_html($MIB_CONFIG['site_title'])); ?>', {duration: 10000});

		// Charge le DashBoard
		//MIB_Bo.load('bo/dashboard', {'target': '_popup', 'title': 'test'});
	});
</script>