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
 * Fonction de débugage
 * 
 * @return function Le résultat de la fonction
 */
function MIB_Debug() {
	global $MIB_DB, $MIB_CONFIG, $MIB_PAGE, $MIB_USER, $MIB_PLUGIN, $MIB_WIDGETS, $MIB_URL, $MIB_LANG;

	// Calculate script generation time
	list($usec, $sec) = explode(' ', microtime());
	$time_diff = sprintf('%.3f', ((float)$usec + (float)$sec) - $MIB_PAGE['start']);

	?>
	<style>
		html { margin-top: 25px !important; }
		#DEBUG {
			position: absolute;
			z-index: 999999;
			left: 0;
			top: 0;
			width: 100%;
			font-size: 11px;
			font-family: Verdana, Arial, Helvetica, sans-serif;
			color: #000;
		}
		#DEBUG_MIBBO {
			position: relative;
			float: right;
			font-weight: bold;
			padding: 0 5px;
			cursor: default;
		}
		#DEBUG_MIBBO .about {
			position: absolute;
			right: 0;
			top: 25px;
			background: #000;
			padding: 0 7px;
			line-height: 20px;
			font-weight: normal;
			text-align: right;
			display: none;
		}
		#DEBUG_MIBBO:hover { background: #000; }
		#DEBUG_MIBBO:hover .about { display: block; }
		#DEBUG .pannel {
			background: #ff7d7d;
			color: #fff;
			padding: 0;
			padding-left: 5px;
			line-height: 25px;
			white-space: nowrap;
			height: 25px;
		}
		#DEBUG .pannel a {
			color: #fff !important;
			text-decoration: none;
		}
		#DEBUG .pannel a:hover {
			text-decoration: underline;
		}
		#DEBUG .infos {
			background: #fff;
			padding: 0;
			margin: 0;
			font-size: 12px;
			overflow: hidden;
		}
		#DEBUG .infos div {
			display: none;
			background: #fff;
			border: 2px solid #ff7d7d;
			border-top: none;
			padding: 5px;
			margin: 0px;
		}
		#DEBUG .infos h1 {
			font-size: 14px;
			margin: 0px 0px 5px 0px;
		}
		#DEBUG .infos table {
			width: 100%;
		}
		#DEBUG .infos table td, #DEBUG .infos table th {
			padding: 3px;
			text-align: left;
		}
		#DEBUG .infos table .row2 {
			background: #eee;
		}
		#DEBUG .infos pre {
			margin: 0px 0px 5px 5px;
			padding: 3px;
			font-family: monaco, "Bitstream Vera Sans Mono", "Courier New", courier, monospace;
			background: #f5f5f5;
			border: 1px solid #ddd;
		}
	</style>
	<script>
		function mib_debug(onglet) {
			var onglets = new Array('globals','db','config','user','page','plugin','widgets','url','lang');
			var onglet_actif = null;

			for (var i=0; i < onglets.length; ++i) {
				if(document.getElementById('mib_debug_' + onglets[i]).style.display == 'block')
					onglet_actif = onglets[i];
				document.getElementById('mib_debug_' + onglets[i]).style.display = 'none';
			}

			if(onglet && onglet != onglet_actif)
				document.getElementById('mib_debug_' + onglet).style.display = 'block';
		}
	</script>
	<div id="DEBUG">
		<div class="pannel">
			<span id="DEBUG_MIBBO">
				<?php echo MIB_SYS_NAME.' '.MIB_SYS_VERSION; ?>
				<p class="about"><strong>DB: [<?php echo DB_HOST.' - '.DB_NAME; ?>]</strong><br>Généré en <?php echo $time_diff ?> sec., <?php echo $MIB_DB->get_num_queries() ?> req. exécutées</p>
			</span>
			<a href="#globals" onclick="javascript:mib_debug('globals');return false;">$GLOBALS</a> |
			<a href="#db" onclick="javascript:mib_debug('db');return false;">$MIB_DB</a> |
			<a href="#config" onclick="javascript:mib_debug('config');return false;">$MIB_CONFIG</a> |
			<a href="#user" onclick="javascript:mib_debug('user');return false;">$MIB_USER</a> |
			<a href="#page" onclick="javascript:mib_debug('page');return false;">$MIB_PAGE</a> |
			<a href="#plugin" onclick="javascript:mib_debug('plugin');return false;">$MIB_PLUGIN</a> |
			<a href="#blocs" onclick="javascript:mib_debug('widgets');return false;">$MIB_WIDGETS</a> |
			<a href="#ref" onclick="javascript:mib_debug('url');return false;">$MIB_URL</a> |
			<a href="#lang" onclick="javascript:mib_debug('lang');return false;">$MIB_LANG</a>
		</div>
		<div class="infos">
			<div id="mib_debug_globals">
				<h1>$_GET</h1>
				<?php mib_dump($_GET); ?>

				<h1>$_POST</h1>
				<?php mib_dump($_POST); ?>

				<h1>$_COOKIE</h1>
				<?php mib_dump($_COOKIE); ?>

				<h1>$_SESSION</h1>
				<?php mib_dump($_SESSION); ?>

				<?php if(isset($MIB_PAGE['dump'])): ?>
					<h1>$_DUMP</h1>
					<?php mib_dump($MIB_PAGE['dump']); ?>
				<?php endif; ?>
			</div>

			<div id="mib_debug_db">
				<h1><?php echo $MIB_DB->get_num_queries() ?> requètes exécutées</h1>
				<?php
					// On récupère les requètes pour les afficher
					$saved_queries = $MIB_DB->get_saved_queries(); 
				?>
					<table cellspacing="0">
					<thead>
						<tr>
							<th scope="col">Tps (s)</th>
							<th scope="col">Requète</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$query_time_total = 0.0;
					$row = 0;
					foreach ( $saved_queries as list(, $cur_query)){
						$row++;
						$query_time_total += $cur_query[1];
					?>
						<tr>
							<td class="<?php echo ($row&1) ? 'row1' : 'row2'; ?>"><?php echo ($cur_query[1] != 0) ? $cur_query[1] : '&#160;' ?></td>
							<td class="<?php echo ($row&1) ? 'row1' : 'row2'; ?>"><?php echo mib_html($cur_query[0]) ?></td>
						</tr>
					<?php
					}
				?>
					<tr>
						<td colspan="2"><strong>Temps Total des Requètes : <?php echo $query_time_total ?> s</strong></td>
					</tr>
				</tbody>
				</table>
			</div>

			<div id="mib_debug_config">
				<h1>$MIB_CONFIG</h1>
				<?php mib_dump($MIB_CONFIG); ?>
			</div>

			<div id="mib_debug_user">
				<h1>$MIB_USER</h1>
				<?php mib_dump($MIB_USER); ?>
			</div>

			<div id="mib_debug_page">
				<?php
				$mib_debug_page_header = $MIB_PAGE['header'];
				foreach($MIB_PAGE['header'] as $k => $v)
					$MIB_PAGE['header'][$k] = mib_html($v);

				$mib_debug_page_footer = $MIB_PAGE['footer'];
				foreach($MIB_PAGE['footer'] as $k => $v)
					$MIB_PAGE['footer'][$k] = mib_html($v);

				$mib_debug_page_tpl = $MIB_PAGE['tpl']; unset($MIB_PAGE['tpl']);
				$mib_debug_page_template = $MIB_PAGE['template']; unset($MIB_PAGE['template']);
				$mib_debug_page_main = $MIB_PAGE['main']; unset($MIB_PAGE['main']);
				?>
					<h1>$MIB_PAGE</h1>
					<?php mib_dump($MIB_PAGE); ?>
				<?php
				$MIB_PAGE['header'] = $mib_debug_page_header;
				$MIB_PAGE['footer'] = $mib_debug_page_footer;
				$MIB_PAGE['tpl'] = $mib_debug_page_tpl;
				$MIB_PAGE['template'] = $mib_debug_page_template;
				$MIB_PAGE['main'] = $mib_debug_page_main;
				?>
			</div>

			<div id="mib_debug_plugin">
				<?php
				$mib_debug_plugin_tpl = $MIB_PLUGIN['tpl'];		unset($MIB_PLUGIN['tpl']);
				?>
					<h1>$MIB_PLUGIN</h1>
					<?php
						if(isset($MIB_PLUGIN['time']) && is_array($MIB_PLUGIN['time'])) {
							foreach($MIB_PLUGIN['time'] as $k => $v)
								$MIB_PLUGIN['time'][$k] = sprintf('%.4f', $v);
						}
						mib_dump($MIB_PLUGIN);
					?>
				<?php
				$MIB_PLUGIN['tpl'] = $mib_debug_plugin_tpl;
				?>
			</div>

			<div id="mib_debug_widgets">
				<h1>$MIB_WIDGETS</h1>
				<?php
					if(isset($MIB_WIDGETS) && is_array($MIB_WIDGETS)) {
						foreach($MIB_WIDGETS as $widget => $value) {
							if(isset($value['time']) && is_array($value['time'])) {
								foreach($value['time'] as $k => $v)
									$MIB_WIDGETS[$widget]['time'][$k] = sprintf('%.4f', $v);
							}
						}
					}
					mib_dump($MIB_WIDGETS);
				?>
			</div>

			<div id="mib_debug_url">
				<h1>$MIB_URL</h1>
				<?php mib_dump($MIB_URL); ?>
			</div>

			<div id="mib_debug_lang">
				<h1>$MIB_LANG</h1>
				<?php mib_dump($MIB_LANG); ?>
			</div>
		</div>
	</div>
	<?php
}