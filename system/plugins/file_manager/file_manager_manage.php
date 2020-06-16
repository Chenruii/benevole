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

if (isset($MIB_PAGE['info'])) {
	$MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info'], 2);

	$MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "browse", "delete", "newfolder", "rename", "upload"
	$MIB_PLUGIN['element'] = !empty($MIB_PLUGIN['options'][1]) ? mib_trim(mib_trim($MIB_PLUGIN['options'][1]), '/') : ''; // "fichier/répertoire"
	if(isset($MIB_PAGE['ext']))
		$MIB_PLUGIN['element'] = $MIB_PLUGIN['element'].'.'.$MIB_PAGE['ext'];
}
else
	$MIB_PLUGIN['element'] = '';


// Requète JSON
if(defined('MIB_JSON')) {
	// L'utilisateur a t'il les permissions d'écriture ?
	if(!$MIB_USER['can_write_plugin'])
		error(__('Vous n\'avez pas la permission d\'effectuer cette action.'));

	// Dossier
	if(is_dir(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'])) {
		// Upload
		if($MIB_PLUGIN['action'] == 'upload') {
			$errors = array();

			// Valide l'envois du fichier
			$errors = array_merge($errors, mib_core_function('MIB_isUploaded', $_FILES['file']));

			if (empty($errors)) { // Pas d'erreurs pour le moment le fichier à bien été envoyé dans le dossier TMP
				// formate le nom pour enlevé les espace et les caractère spéciaux
				$newfile_name = mib_strtourl($_FILES['file']['name'], '0-9A-Za-z\s\._\-');
				$newfile_path = MIB_PUBLIC_DIR.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$newfile_name;

				if(!move_uploaded_file($_FILES['file']['tmp_name'], $newfile_path))
					error(__('L\'envoi a échoué. Déplacement du fichier impossible. Vérifiez les droits en écriture dans le dossier.'.$newfile_path));

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Fichier envoyé'),
					'value'		=> __('Le fichier ['.mib_html($newfile_name).'] a été envoyé avec succès.'),
					'options'		=> array(
						'type'		=> 'valid'
					),
					'page'		=> array(
						'update'		=> $MIB_PLUGIN['name']
					)
				);
			}

			// Il y a eu une erreur lors de l'envois
			if (!empty($errors))
				error(current($errors)); // PAS de balise du à un bug avec l'iframe
		}
		// Création d'un nouveau dossier
		else if($MIB_PLUGIN['action'] == 'newfolder') {

			$errors = array();

			$newfolder = (isset($_POST['new'])) ? mib_trim($_POST['new']) : '';
			$newfolder_path = MIB_PUBLIC_DIR.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$newfolder;

			if(strlen($newfolder) < 3)
				$errors[] = __('Le nom du dossier doit être constitué d\'au moins 3 caractères.');
			else if(preg_match('/[^0-9a-zA-Z\-_\.]/', $newfolder))
				$errors[] = __('Il y a des caractères invalides dans le nom du dossier.');
			else if(is_dir($newfolder_path))
				$errors[] = __('Un dossier avec ce nom existe déjà.');

			// Aucune erreur
			if (empty($errors)) {
				// création du dosier
				@mkdir($newfolder_path);

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Dossier créé'),
					'value'		=> __('Le dossier <strong>'.mib_html($newfolder).'</strong> a été ajouté avec succès.'),
					'options'		=> array(
						'type'		=> 'valid'
					),
					'page'		=> array(
						'update'		=> $MIB_PLUGIN['name']
					),
					'element'		=> array(
						'newfodler-box' => array(
							'setStyles' => array(
								'visibility'		=> 'hidden',
								'opacity'		=> '0'
							),
							'erase'	=> 'id'
						),
						'newfodler-box-overlay' => array(
							'morph' => array(
								'opacity'		=> '0'
							)
						)
					)
				);
			}
			else
				error('<p>'.implode('</p><p>', $errors).'</p>');
		}
	}

	// Concerne les fichiers et les dossiers
	if(file_exists(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'])) {
		// Renome un fichier ou un dossier
		if($MIB_PLUGIN['action'] == 'rename') {

			$errors = array();

			$newname = (isset($_POST['new'])) ? mib_trim($_POST['new']) : '';

			$oldname_path = MIB_PUBLIC_DIR.$MIB_PLUGIN['element'];
			$newname_folder = implode('/',explode('/', $MIB_PLUGIN['element'], -1));
			$newname_path = MIB_PUBLIC_DIR.(!empty($newname_folder) ? $newname_folder.'/' : '').$newname;

			if(strlen($newname) < 3)
				$errors[] = __('Le nouveau nom doit être constitué d\'au moins 3 caractères.');
			else if(preg_match('/[^0-9a-zA-Z\-_\.]/', $newname))
				$errors[] = __('Il y a des caractères invalides dans le nouveau nom.');
			else if($oldname_path == $newname_path)
				$errors[] = __('Le nom doit être différent de celui existant.');
			else if(file_exists($newname_path))
				$errors[] = __('Un dossier/fichier avec ce nom existe déjà dans ce dossier.');

			// Aucune erreur
			if (empty($errors)) {
				// renane
				@rename($oldname_path, $newname_path);

				$MIB_PLUGIN['json'] = array(
					'title'		=> __('Renommage effectué'),
					'value'		=> __('Le nouveau nom <strong>'.mib_html($newname).'</strong> a été enregistré avec succès.'),
					'options'		=> array(
						'type'		=> 'valid'
					),
					'page'		=> array(
						'update'		=> $MIB_PLUGIN['name']
					),
					'element'		=> array(
						'rename-box' => array(
							'setStyles' => array(
								'visibility'		=> 'hidden',
								'opacity'		=> '0'
							),
							'erase'	=> 'id'
						),
						'rename-box-overlay' => array(
							'morph' => array(
								'opacity'		=> '0'
							)
						)
					)
				);
			}
			else
				error('<p>'.implode('</p><p>', $errors).'</p>');
		}
		// Supprime un fichier ou un dossier
		elseif($MIB_PLUGIN['action'] == 'delete') {

			$deleted_name = end(explode('/', $MIB_PLUGIN['element']));

			$delete = @mib_rmdirr(MIB_PUBLIC_DIR.$MIB_PLUGIN['element']);

			$MIB_PLUGIN['json'] = array(
				'title'		=> __('Supression effectuée'),
				'value'		=> __('<strong>'.mib_html($deleted_name).'</strong> a été supprimé avec succès.'),
				'options'		=> array(
					'type'		=> 'valid'
				),
				'page'		=> array(
					'update'		=> $MIB_PLUGIN['name']
				)
			);
		}
	}

	define('MIB_JSONED', 1);
	return;
}
// Requète AJAX
else if(defined('MIB_AJAX'))
	define('MIB_AJAXED', 1);


// Affiche un dossier
if(is_dir(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'])) {
?>
	<style type="text/css">
		#MIB_page .button .upload { background-image: url('{{tpl:MIB_PLUGIN}}/img/document--plus.png'); }
		#MIB_page .button .create_folder { background-image: url('{{tpl:MIB_PLUGIN}}/img/folder--plus.png'); }
		#MIB_page .button .public_folder { background-image: url('{{tpl:MIB_PLUGIN}}/img/folder-stand.png'); }
		#MIB_page .button .folder { background-image: url('{{tpl:MIB_PLUGIN}}/img/folder-horizontal.png'); }
		#MIB_page .button .current_folder { background-image: url('{{tpl:MIB_PLUGIN}}/img/folder-horizontal-open.png'); }
	</style>
	<p>
<?php
	$last_folder = mib_trim(MIB_PUBLIC_DIR, '/.');
	$cur_folder = '';

	echo '<a href="'.$MIB_PLUGIN['name'].'/browse" class="button fleft" style="margin-right:5px;" target="'.$MIB_PLUGIN['name'].'" ><span class="public_folder">'.$last_folder.'</span></a>';

	if(!empty($MIB_PLUGIN['element'])) {
		$folders = explode('/', $MIB_PLUGIN['element']);
		$i = 0;
		foreach($folders as $folder) {
			$i++;
			$cur_folder .= $folder.'/';
			echo ' <a href="'.$MIB_PLUGIN['name'].'/browse/'.$cur_folder.'" class="button fleft" style="margin-right:5px;" target="'.$MIB_PLUGIN['name'].'" ><span class="'.(($i == count($folders)) ? 'current_folder' : 'folder').'">'.$folder.'</span></a>';
			$last_folder = $folder;
		}
	}
?>
		<a class="button fright tips" style="margin-left:5px;" href="<?php echo $MIB_PLUGIN['name'].'/upload/'.$cur_folder; ?>" title="Envoyer un fichier" upload="Uploader un fichier dans le dossier [<?php echo mib_html($last_folder); ?>]:: Envoyer un fichier (<?php echo str_replace('M','Mo',ini_get('upload_max_filesize')); ?> max)" rel="Uploader un fichier dans [<?php echo mib_html($last_folder); ?>]" ><span class="upload">Fichier</span></a>
		<a id="create_folder" method="post" class="button fright tips" style="margin-left:5px;" href="<?php echo $MIB_PLUGIN['name'].'/newfolder/'.$cur_folder; ?>" title="Créer un dossier" rel="Ajouter un nouveau dossier dans [<?php echo mib_html($last_folder); ?>]" target="_action"><span class="create_folder">Dossier</span></a>
	</p>
	<br class="clear">
<?php

	// Tableau de l'affichage des fichiers/documents
	$search = mib_search_build(array(
		'target'					=> $MIB_PLUGIN['name'], // URL de la page dans laquelle s'affiche le tableau des résulats (généralement $MIB_PLUGIN['name'])
		'sort_by_autorized'			=> array('name','size','modified'), // Champs de la table sur lequels le tri est autorizé
		'sort_by_default'			=> 'name', // Champ de la table utilisé pour le tri par défaut
		'sort_dir_default'			=> 'ASC', // Ordre de tri par défaut
		'cols'					=> array( // Colonnes à affiché pour le tableau des résultats
			'name'			=> 'Nom',
			'size'			=> 'Taille',
			'modified'		=> 'Date de modification',
		)
	));
?>
	<style type="text/css">
	.<?php echo $MIB_PLUGIN['name']; ?>-fileresults .tc1 img {
		vertical-align: middle;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-fileresults .tc1 img.fright {
		margin: 3px 2px;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-fileresults .tc2 {
		width: 110px;
		text-align: center;
	}
	.<?php echo $MIB_PLUGIN['name']; ?>-fileresults .tc3 {
		width: 160px;
		text-align: center;
	}
	</style>
	<table class="table-results <?php echo $MIB_PLUGIN['name']; ?>-fileresults" style="margin-top:10px;">
<?php

	// Construit le header des résultats de la recherche
	if(!isset($search['sort_by_autorized']) || !is_array($search['sort_by_autorized']))
		$search['sort_by_autorized'] = array();

	// Préparation des données necessaires à l'affichage des résultats
	if(empty($search['num_cols']))
		$search['num_cols'] = count($search['cols']); // Nombre de colonnes dans le tableau de résultats

	echo '<thead><tr>';

	$i = 1;
	foreach($search['cols'] as $k => $v) {
		echo '<th class="tc'.$i.($i == 1 ? ' tcl' : '').($i == $search['num_cols'] ? ' tcr' : '').'">';
		if(in_array($k, $search['sort_by_autorized'])) { // Il y a un tri possible sur cette colonne
			echo '<a href="'.$MIB_PLUGIN['name'].'/browse/'.$cur_folder.'?'.implode('&amp;', $search['filters_url']).'&amp;sort_by='.urlencode($k).($search['sort_by'] == $k ? '&amp;sort_dir='.$search['sort_dir_selected'] : '').'" class="'.($search['sort_by'] == $k ? 'sort_dir_'.strtoupper($search['sort_dir']).' to_sort_dir_'.strtoupper($search['sort_dir_selected']) : 'to_sort_dir_'.strtoupper($search['sort_dir_default'])).'" target="'.$search['target'].'">'.$v.'</a>';
		}
		else
			echo $v;
		echo '</th>';
		$i++;
	}

	echo '</tr></thead>';

?>
	<tbody>
<?php
	$MIB_PLUGIN['dir'] = array();
	$MIB_PLUGIN['dir']['scandir'] = scandir(MIB_PUBLIC_DIR.$MIB_PLUGIN['element']);
	$MIB_PLUGIN['dir']['view'] = $MIB_PLUGIN['dir']['order']['name'] = $MIB_PLUGIN['dir']['order']['size'] = $MIB_PLUGIN['dir']['order']['modified'] = array();
	foreach($MIB_PLUGIN['dir']['scandir'] as $k => $v) {
		// Permet de na pas afficher les fichiers cachés et le retour à la raçine
		if($v{0} != '.') {
			$MIB_PLUGIN['dir']['view'][$k] = array(
				'name'		=> $v,
				'size'		=> 0,
				'modified'	=> 0,
				'icon'		=> 'document.png',
				'type'		=> false,
			);

			// Dossier
			if(is_dir(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'].'/'.$v)) {
				$MIB_PLUGIN['dir']['view'][$k]['type'] = 'dir';
				$MIB_PLUGIN['dir']['view'][$k]['elements'] = count(scandir(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'].'/'.$v)) - 2;
				$MIB_PLUGIN['dir']['view'][$k]['icon'] = 'folder-horizontal.png';

				$MIB_PLUGIN['dir']['order']['name'][$k] = '000000000_'.utf8_strtolower($v); // petit hack permettant de tjs classer les dossier a part
				$MIB_PLUGIN['dir']['order']['size'][$k] = $MIB_PLUGIN['dir']['view'][$k]['elements'] / 1000000; // petit hack permettant de tjs classer les dossier a part
				$MIB_PLUGIN['dir']['order']['modified'][$k] = 0;
			}
			// Fichier
			else if(is_file(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'].'/'.$v)) {
				$MIB_PLUGIN['dir']['view'][$k]['type'] = 'file';

				// Détection de l'icon en fonction de lextension
				$current_ext = strtolower(@end(explode('.', $v)));
				$knowed_icons = array(
					'document-zipper.png'		=> array('zip','rar','001','002', '003','ace','jar','r00','sit','sitx','7z','7zip','ar','bz2','cbr','cbz','gz','lzma','tar'),
					'document-excel.png'		=> array('xls','xlsm','xlsx','csv'),
					'document-film.png'			=> array('3gp','avi','3g2','m4v','mov','mp4','mpg','wmv','mkv'),
					'document-flash-movie.png'	=> array('fla','flv','swf'),
					'document-globe.png'		=> array('htm','html','xml','json','mht','xpi'),
					'document-illustrator.png'	=> array('ai','eps'),
					'document-image.png'		=> array('jpg','jpeg','gif','png','ico','bmp'),
					'document-music.png'		=> array('mp3','wma','wav','ogg','m4p','m4a','m3u','aup','amr','aac','cda'),
					'document-pdf.png'			=> array('pdf'),
					'document-photoshop.png'		=> array('psd','ps','abr'),
					'document-text.png'			=> array('txt','srt','sub'),
					'document-word.png'			=> array('doc','docm','docx','dot','dotm','dotx'),
					'document-access.png'		=> array('accdb','accdt','mdb'),
					'document-code.png'			=> array('asp','aspx','js','php', 'php3', 'php4','css'),
					'document-powerpoint.png'	=> array('potx','pps','ppsm','ppsx','ppt','pptm','pptx','thmx'),
					//'xxx'		=> array('xxx','xxx','xxx','xxx'),
				);
				foreach ($knowed_icons as $png_icon => $exts) {
					if(in_array($current_ext, $exts)) {
						$MIB_PLUGIN['dir']['view'][$k]['icon'] = $png_icon;
						break;
					}
				}

				$MIB_PLUGIN['dir']['order']['name'][$k] = utf8_strtolower($v);
				$MIB_PLUGIN['dir']['order']['size'][$k] = sprintf('%u', filesize(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'].'/'.$v));
				$MIB_PLUGIN['dir']['order']['modified'][$k] = filemtime(MIB_PUBLIC_DIR.$MIB_PLUGIN['element'].'/'.$v);
			}

			$MIB_PLUGIN['dir']['view'][$k]['size'] = $MIB_PLUGIN['dir']['order']['size'][$k];
			$MIB_PLUGIN['dir']['view'][$k]['modified'] = $MIB_PLUGIN['dir']['order']['modified'][$k];

		}
	}

	if($search['sort_dir'] == 'ASC')
		asort($MIB_PLUGIN['dir']['order'][$search['sort_by']]);
	else
		arsort($MIB_PLUGIN['dir']['order'][$search['sort_by']]);

	// Affiche le résultat
	if(!empty($MIB_PLUGIN['dir']['view'])) {
		foreach($MIB_PLUGIN['dir']['order'][$search['sort_by']] as $ViewK => $ViewV) {
			echo '<tr>';

			// Affiche les colonnes
			$i = 1;
			foreach($search['cols'] as $k => $v) {
				echo '<td class="tc'.$i.($i == 1 ? ' tcl' : '').($i == $search['num_cols'] ? ' tcr' : '').'">';

				if($k == 'name') {
					// Editer/Supprimer
					if($MIB_PLUGIN['dir']['view'][$ViewK]['type'] == 'dir') {
						echo '<a class="tips" title="Supprimer" rel="Supprimer le dossier ['.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).']" href="'.$MIB_PLUGIN['name'].'/delete/'.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" confirm="Le dossier <strong>['.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).']</strong> contient '.($MIB_PLUGIN['dir']['view'][$ViewK]['elements'] > 1 ? $MIB_PLUGIN['dir']['view'][$ViewK]['elements'].' éléments' : $MIB_PLUGIN['dir']['view'][$ViewK]['elements'].' élément').'. Voulez vous le supprimer avec son contenu ?::Supprimer ?::question" target="_json"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/folder--minus.png" alt="[supprimer]" /></a>';
						echo '<a id="action-rename-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" method="post" class="tips btn-rename" title="Renommer" rel="Renommer le dossier ['.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).']" href="'.$MIB_PLUGIN['name'].'/rename/'.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" target="_action"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/folder--pencil.png" alt="[éditer]" /></a>';
					}
					else {
						echo '<a class="tips" title="Supprimer" rel="Supprimer le fichier '.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).'" href="'.$MIB_PLUGIN['name'].'/delete/'.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" confirm="Voulez vous le supprimer le fichier <strong>'.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).'</strong> ?::Supprimer ?::question" target="_json"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/document--minus.png" alt="[supprimer]" /></a>';
						echo '<a id="action-rename-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" method="post" class="tips btn-rename" title="Renommer" rel="Renommer le fichier '.mib_html($MIB_PLUGIN['dir']['view'][$ViewK]['name']).'" href="'.$MIB_PLUGIN['name'].'/rename/'.(!empty($MIB_PLUGIN['element']) ? $MIB_PLUGIN['element'].'/' : '').$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" target="_action"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/document--pencil.png" alt="[éditer]" /></a>';
					}

					echo '<img src="{{tpl:MIB_PLUGIN}}/img/'.$MIB_PLUGIN['dir']['view'][$ViewK]['icon'].'" alt="[ico]"> ';
					if($MIB_PLUGIN['dir']['view'][$ViewK]['type'] == 'dir')
						echo '<a id="item-name-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" href="'.$MIB_PLUGIN['name'].'/browse'.(!empty($MIB_PLUGIN['element']) ? '/'.$MIB_PLUGIN['element'].'/' : '/').$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" target="'.$MIB_PLUGIN['name'].'">'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'</a>';
					else
						echo '<a id="item-name-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" href="'.$MIB_CONFIG['base_url'].'/'.mib_trim(MIB_PUBLIC_DIR, '/.').(!empty($MIB_PLUGIN['element']) ? '/'.$MIB_PLUGIN['element'] : '').'/'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'" target="_blank">'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'</a>';
				}
				else if($k == 'size') {
					if($MIB_PLUGIN['dir']['view'][$ViewK]['type'] == 'dir') {
						echo '<span id="item-elements-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'">'.($MIB_PLUGIN['dir']['view'][$ViewK]['elements'] > 1 ? $MIB_PLUGIN['dir']['view'][$ViewK]['elements'].' éléments' : $MIB_PLUGIN['dir']['view'][$ViewK]['elements'].' élément').'</span>';
					}
					else
						echo '<span id="item-size-'.$MIB_PLUGIN['dir']['view'][$ViewK]['name'].'">'.mib_bytestohuman($MIB_PLUGIN['dir']['view'][$ViewK]['size']).'</span>';
				}
				else if($k == 'modified') {
					echo format_time($MIB_PLUGIN['dir']['view'][$ViewK]['modified'], 'd/m/Y - H:i', true);
				}

				echo '</td>';
				$i++;
			}

			echo '</tr>';
		}
	}
	else // Le dossier est vide
		echo '<tr><td class="tc-no-result" colspan="'.$search['num_cols'].'">Le dossier <strong>['.$last_folder.']</strong> est vide.</td></tr>';
?>
	</tbody>
	</table>
	<script type="text/javascript">
	// Créer un nouveau dossier
	$('create_folder').addEvent('click', function(e) {
		e.stop();
		$prompt(this.get('rel'),this.retrieve('tip:title', this.get('title')), {
			id: 'newfodler-box',
			hide: false,
			onClose: function(value) {
				if(value) { // Une valeur a été entrée
					this.store('data','new='+encodeURIComponent(value));
					this.set('target','_json');
					MIB_Bo.load(this);
				}
				else {
					$('newfodler-box').setStyles({
						'visibility' : 'hidden',
						'opacity' : 0
					}).erase('id');
					$('newfodler-box-overlay').morph({'opacity':0});
				}
			}.bind(this)
		});
	});

	// Renomer dossiers/fichiers
	$$('.btn-rename').addEvent('click', function(e) {
		e.stop();
		var elementUID = this.get('id').replace('action-rename-','');

		$prompt(this.get('rel'),this.retrieve('tip:title', this.get('title')), {
			id: 'rename-box',
			hide: false,
			value: $('item-name-' + elementUID).get('text'),
			onClose: function(value) {
				if(value) { // Une valeur a été entrée
					this.store('data','new='+encodeURIComponent(value));
					this.set('target','_json');
					MIB_Bo.load(this);
				}
				else {
					$('rename-box').setStyles({
						'visibility' : 'hidden',
						'opacity' : 0
					}).erase('id');
					$('rename-box-overlay').morph({'opacity':0});
				}
			}.bind(this)
		});
	});
	</script>
<?php
}