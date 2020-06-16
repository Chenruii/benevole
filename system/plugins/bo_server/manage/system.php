<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

// seulement si les fonction d'analyse sont disponibles
if ( PHP_OS != 'Linux' || !mib_function_is_enabled('shell_exec') || !mib_function_is_enabled('exec') ) return;

?>
<fieldset><legend><?php echo mib_clean(shell_exec('/usr/bin/lsb_release -ds;/bin/uname -r')); ?></legend>
	<div class="option-row">
		<div class="option-title"><?php _ebo('Nom du serveur'); ?></div>
		<div class="option-item">
			<p><?php echo mib_clean(shell_exec('/bin/hostname')); ?></p>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title"><?php _ebo('Uptime'); ?></div>
		<div class="option-item">
			<p>
<?php
			$totalSeconds = shell_exec('/usr/bin/cut -d. -f1 /proc/uptime');
			$totalMin   = $totalSeconds / 60;
			$totalHours = $totalMin / 60;

			$days  = floor($totalHours / 24);
			$hours = floor($totalHours - ($days * 24));
			$min   = floor($totalMin - ($days * 60 * 24) - ($hours * 60));

			$uptime = '';
			if ( $days != 0 ) $uptime .= $days.' '.($days > 1 ? 'jours' : 'jour').' ';
			if ( $hours != 0 ) $uptime .= $hours. ' '.($hours > 1 ? 'heures' : 'heure').' ';
			if ( $min != 0 ) $uptime .= $min.' '.($hours > 1 ? 'minutes' : 'minute');
			echo $uptime;
?>
			</p>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title"><?php _ebo('Mémoire'); ?></div>
		<div class="option-item">
			<p>
<?php
			exec(
				'/usr/bin/free -tmo | /usr/bin/awk \'{print $1","$2","$3-$6-$7","$4+$6+$7}\'',
				$result_ram
			);

			$ram_infos = explode(',', $result_ram[1]);

			$ram = array(
				'full'=> array(
					'go'	=> $ram_infos[1]*1024*1024,
					'%'		=> 100,
				),
				'used'=> array(
					'go'	=> $ram_infos[2]*1024*1024,
					'%'		=> 0,
				),
				'free'=> array(
					'go'	=> $ram_infos[3]*1024*1024,
					'%'		=> 0,
				)
			);

			$ram['free']['%'] = $ram['free']['go'] / $ram['full']['go'] * 100;
			$ram['used']['%'] = $ram['used']['go'] / $ram['full']['go'] * 100;
?>
				<?php echo mib_bytestohuman($ram['full']['go']); ?>
			</p>
			<p>
				<span class="stat-bar fleft"><span class="stat-inbar" style="width: <?php echo round($ram['used']['%']); ?>%"></span></span>
				<span class="fleft" style="margin-left:3px;"><?php echo round($ram['used']['%']); ?>% / <?php echo mib_bytestohuman($ram['used']['go']); ?> <?php _ebo('utilisé'); ?></span>
				<br class="clear">
			</p>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title"><?php _ebo('Processeur'); ?></div>
		<div class="option-item">
			<p>
<?php

			echo mib_clean(shell_exec("cat -n /proc/cpuinfo | grep '57' | cut -c 21-"));

			$intOpts = array(
				'options' => array(
					'min_range' => 1,
				),
			);

			$numOfCores = shell_exec('LC_ALL=C /bin/grep -c ^processor /proc/cpuinfo');
			$numOfCores = filter_var(
				$numOfCores[0],
				FILTER_VALIDATE_INT,
				$intOpts
			);

			// si on ne trouve pas le nombre de coeur
			if ( $numOfCores === false ) {
				$numOfCores = filter_var(
					shell_exec('/usr/bin/nproc'),
					FILTER_VALIDATE_INT,
					$intOpts
				);
			}

			if ($numOfCores !== false) {
				echo ' × '.$numOfCores;
			}
?>
			</p>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title"><?php _ebo('Type d\'OS'); ?></div>
		<div class="option-item">
			<p>
<?php
			if ( mib_clean(shell_exec("uname -m")) == 'x86_64' )
				echo '64 bits';
			else
				echo '32 bits';
?>
			</p>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title"><?php _ebo('Systèmes de fichiers'); ?></div>
		<div class="option-item">
<?php
		exec('/bin/df -k|awk \'{print $1","$2","$3","$4","$5","$6}\'', $result_disk);

		$ed = array();
		foreach( $result_disk as $d ) {
			$d = explode(',', $d);

			if ( count($d) == 6 && strpos($d[0], '/dev/') !== false ) {

				$ed[$d[0]] = array(
					'Filesystem'	=> $d[0],
					'Size'			=> $d[1],
					'Used'			=> $d[2],
					'Avail'			=> $d[3],
					'Use_100'		=> $d[4],
					'Mounted'		=> $d[5],
				);
			}
		}
?>
		<style>
		#<?php echo $MIB_PAGE['uniqid']; ?>-results-ED .tc-Filesystem,
		#<?php echo $MIB_PAGE['uniqid']; ?>-results-ED .tc-Mounted {
			width: 100px;
		}
		#<?php echo $MIB_PAGE['uniqid']; ?>-results-ED .tc-Size,
		#<?php echo $MIB_PAGE['uniqid']; ?>-results-ED .tc-Avail {
			width: 100px;
			text-align: right;
		}
		</style>
		<table id="<?php echo $MIB_PAGE['uniqid']; ?>-results-ED" class="table-results mg0">
		<thead><tr>
			<th class="tc tc-Filesystem"><?php _ebo('Périphérique'); ?></th>
			<th class="tc tc-Mounted"><?php _ebo('Répertoire'); ?></th>
			<th class="tc tc-Size"><?php _ebo('Total'); ?></th>
			<th class="tc tc-Avail"><?php _ebo('Disponible'); ?></th>
			<th class="tc tc-Usage"><?php _ebo('Utilisé'); ?></th>
		</tr></thead>
		<tbody>
<?php
		mib_sort($ed, 'Filesystem ASC');

		foreach( $ed as $d_k => $d_v ) {
			$d_v['Avail_100'] =  $d_v['Used'] * 100 / $d_v['Size'];
?>
			<tr>
				<td class="tc tcl tc-Filesystem"><?php echo mib_html($d_v['Filesystem']); ?></td>
				<td class="tc tc-Mounted"><?php echo mib_html($d_v['Mounted']); ?></td>
				<td class="tc tc-Size"><?php echo mib_bytestohuman($d_v['Size']*1024); ?></td>
				<td class="tc tc-Avail"><?php echo mib_bytestohuman($d_v['Avail']*1024); ?></td>
				<td class="tc tcr tc-Usage">
					<span class="stat-bar fleft mg3"><span class="stat-inbar" style="width: <?php echo round($d_v['Avail_100']); ?>%"></span></span>
					<?php echo ceil($d_v['Avail_100']); ?>% / <?php echo mib_bytestohuman($d_v['Used']*1024); ?>
				</td>
			</tr>
<?php
		}
?>
		</tbody>
		</table>
		</div>
	</div>

	<div class="option-row">
		<div class="option-title">
			<?php _ebo('Processus'); ?><br>
			<a href="<?php echo $MIB_PLUGIN['name'].($MIB_PLUGIN['action'] != 'process_all' ? '/process_all' : ''); ?>" target="_self" class="button minbutton" style="font-weight:normal;margin-top:3px;"><?php echo ($MIB_PLUGIN['action'] != 'process_all' ? __bo('Afficher le détail des processus') : __bo('Retour à la liste par défaut')); ?></a>
		</div>
		<div class="option-item">
<?php

		exec(
			'/bin/ps aux | /usr/bin/awk ' .
			"'NR>1{print ".'$1","$2","$3","$4","$5","$6","$7","$8","$9","$10","$11'."}'",
			$result_process
		);

		$ps = array();
		foreach( $result_process as $p ) {
			$p = explode(',', $p);

			if ( count($p) == 11 ) {

				$ps[$p[1]] = array(
					'user'			=> $p[0],
					'pid'			=> $p[1],
					'cpu'			=> $p[2],
					'mem'			=> $p[3],
					'vsz'			=> $p[4],
					'rss'			=> $p[5],
					'cmd'			=> $p[10],
					'name'			=> strpos($p[10], '/') !== false ? substr(strrchr($p[10],'/'), 1) : $p[10] 
				);

				$ps[$p[1]]['name'] = mib_trim(mib_clean($ps[$p[1]]['name']), "[]");
			}
		}

		if ( $MIB_PLUGIN['action'] != 'process_all' ) {
			$ps_basic = array();
			foreach($ps as $p_k => $p_v) {
				if ( $p_v['vsz'] > 0 || $p_v['rss'] > 0 ) {
					if ( isset($ps_basic[$p_v['cmd']]) ) {
						$ps_basic[$p_v['cmd']]['cpu'] += $p_v['cpu'];
						$ps_basic[$p_v['cmd']]['mem'] += $p_v['mem'];
						$ps_basic[$p_v['cmd']]['vsz'] += $p_v['vsz'];
						$ps_basic[$p_v['cmd']]['rss'] += $p_v['rss'];
					}
					else {
						$ps_basic[$p_v['cmd']] = $p_v;
					}
				}
			}
			$ps = $ps_basic;
		}

		// colonnes à affiché pour le tableau des résultats
		$cols = array(
			'name'			=> __bo('Nom'),
			'user'			=> __bo('User'),
			'pid'			=> __bo('PID'),
			'cpu'			=> __bo('CPU'),
			'mem'			=> __bo('MEM'),
			'vsz'			=> '<span class="tips" title="'.__bo('Mém. virtuelle').'">'.__bo('VSZ').'</span>',
			'rss'			=> '<span class="tips" title="'.__bo('Mém. résidente').'">'.__bo('RSS').'</span>',
			'cmd'			=> __bo('Command'),
		);

		// ne pas afficher le détail si ce n'est pas demandé
		if ( $MIB_PLUGIN['action'] != 'process_all' ) {
			unset($cols['user']);
			unset($cols['pid']);
		}

		// Tableau de l'affichage des résultats
		$search = mib_search_build(array(
			'uid'					=> mib_uid($MIB_PLUGIN['name'].basename(__FILE__, '.php')),
			'target'				=> '_self',
			'href'					=> $MIB_PLUGIN['action'] == 'process_all' ? $MIB_PLUGIN['name'].'/process_all' : '',
			'sort_by_autorized'		=> array('user','pid','cpu','mem','vsz','rss','name','cmd'),
			'sort_by_default'		=> 'mem',
			'sort_dir_default'		=> 'DESC',
			'cols'					=> $cols
		));
?>
		<style>
		#<?php echo $search['uid']; ?>-results { margin: 0; }
		#<?php echo $search['uid']; ?>-results .tc-user {
			text-align: center;
			width: 100px;
		}
		#<?php echo $search['uid']; ?>-results .tc-pid {
			text-align: center;
			width: 50px;
		}
		#<?php echo $search['uid']; ?>-results .tc-cpu,
		#<?php echo $search['uid']; ?>-results .tc-mem,
		#<?php echo $search['uid']; ?>-results .tc-vsz,
		#<?php echo $search['uid']; ?>-results .tc-rss {
			text-align: right;
			width: 80px;
		}
		</style>
<?php

		$search = mib_search_table_start($search); // initialisation du tableau de résultats

		mib_sort($ps, $search['sort_by'].' '.$search['sort_dir'].', rss DESC');

		foreach( $ps as $p_k => $p_v ) {
			echo '<tr>';

				foreach($search['cols'] as $k => $v) { echo '<td class="tc tc-'.$k.'">'; // START COLONNE

					if ( $k == 'cpu' )
						echo mib_html($p_v[$k]).' %';
					else if ( $k == 'mem' )
						echo mib_html($p_v[$k]).' %';
					else if ( $k == 'vsz' )
						echo mib_bytestohuman($p_v[$k]*1024);
					else if ( $k == 'rss' )
						echo mib_bytestohuman($p_v[$k]*1024);
					else
						echo mib_html($p_v[$k]);

				echo '</td>'; } // END COLONNE

			echo '</tr>';
		}

		$search = mib_search_table_end($search); // fin du tableau de résultats
?>
		</div>
	</div>
</fieldset>