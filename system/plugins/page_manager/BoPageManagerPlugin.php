<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 08/03/2019
 * Time: 16:49
 */

class BoPageManagerPlugin
{
    public $mibPlugin  ;

    public function initRequest(&$MIB_PLUGIN)
    {
        global   $MIB_CONFIG, $MIB_PAGE;
        $this->mibPlugin  = &$MIB_PLUGIN;

        $this->mibPlugin['options'] = isset($MIB_PAGE['info']) ? explode('/', $MIB_PAGE['info'], 3) : array();
        $this->mibPlugin['pages_lang'] = (isset($this->mibPlugin['options'][0]) && array_key_exists($this->mibPlugin['options'][0], $MIB_CONFIG['languages'])) ? $this->mibPlugin['options'][0] : MIB_LANG; // "fr", "en"
        $this->mibPlugin['pages_folder'] = MIB_PATH_VAR . 'pages/' . $this->mibPlugin['pages_lang'] . '/';
        $this->mibPlugin['action'] = isset($this->mibPlugin['options'][1]) ? mib_trim($this->mibPlugin['options'][1]) : 'browse'; // "browse", "delete", "rename", "edit", "newpage", "newrub", "content"
        $this->mibPlugin['element'] = isset($this->mibPlugin['options'][2]) ? mib_trim(mib_trim($this->mibPlugin['options'][2]), '/') : ''; // "page/rubrique"
        if (isset($MIB_PAGE['ext']) && !empty($this->mibPlugin['element']))
            $this->mibPlugin['element'] = $this->mibPlugin['element'] . '.' . $MIB_PAGE['ext'];


    }

    public function processNewRub()
    {
       

        $errors = array();

        $newfolder = (isset($_POST['new'])) ? mib_trim($_POST['new']) : '';
        $newfolder_path = $this->mibPlugin['pages_folder'] . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $newfolder;

        if (strlen($newfolder) < 3)
            $errors[] = __('Le nom de la rubrique doit être constitué d\'au moins 3 caractères.');
        else if (preg_match('/[^0-9a-zA-Z\-_]/', $newfolder))
            $errors[] = __('Il y a des caractères invalides dans le nom de la rubrique.');
        else if (is_dir($newfolder_path))
            $errors[] = __('Une rubrique avec ce nom existe déjà.');

        // Aucune erreur
        if (empty($errors)) {
            // création du dosier
            @mkdir($newfolder_path);

            $this->mibPlugin['json'] = array(
                'title' => __('Rubrique créée'),
                'value' => __('La rubrique <strong>' . mib_html($newfolder) . '</strong> a été ajoutée avec succès.'),
                'options' => array(
                    'type' => 'valid'
                ),
                'page' => array(
                    'update' => $this->mibPlugin['name']
                ),
                'element' => array(
                    'new-box' => array(
                        'setStyles' => array(
                            'visibility' => 'hidden',
                            'opacity' => '0'
                        ),
                        'erase' => 'id'
                    ),
                    'new-box-overlay' => array(
                        'morph' => array(
                            'opacity' => '0'
                        )
                    )
                )
            );
        } else
            error('<p>' . implode('</p><p>', $errors) . '</p>');

    }

    public function processNewPage()
    {
       

        $errors = array();

        $newpage = (isset($_POST['new'])) ? mib_trim($_POST['new']) : '';
        $newpage_path = $this->mibPlugin['pages_folder'] . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $newpage . '.html';

        if (strlen($newpage) < 3)
            $errors[] = __('Le nom de la page doit être constitué d\'au moins 3 caractères.');
        else if (preg_match('/[^0-9a-zA-Z\-_]/', $newpage))
            $errors[] = __('Il y a des caractères invalides dans le nom de la page.');
        else if (is_file($newpage_path))
            $errors[] = __('Une page avec ce nom existe déjà.');

        // Aucune erreur
        if (empty($errors)) {
            // création de la page
            $fp = fopen($newpage_path, 'w');
            fwrite($fp, '<p>&nbsp;</p>');
            fclose($fp);

            $this->mibPlugin['json'] = array(
                'title' => __('Page créée'),
                'value' => __('La page <strong>' . mib_html($newpage) . '</strong> a été ajoutée avec succès.'),
                'options' => array(
                    'type' => 'valid'
                ),
                'page' => array(
                    'update' => $this->mibPlugin['name']
                ),
                'element' => array(
                    'new-box' => array(
                        'setStyles' => array(
                            'visibility' => 'hidden',
                            'opacity' => '0'
                        ),
                        'erase' => 'id'
                    ),
                    'new-box-overlay' => array(
                        'morph' => array(
                            'opacity' => '0'
                        )
                    )
                )
            );
        } else
            error('<p>' . implode('</p><p>', $errors) . '</p>');
    }

    public function processRename()
    {
       

        $errors = array();

        $newname = (isset($_POST['new'])) ? mib_trim($_POST['new']) : '';

        $oldname_path = $this->mibPlugin['pages_folder'] . $this->mibPlugin['element'];
        $newname_folder = implode('/', explode('/', $this->mibPlugin['element'], -1));
        $newname_path = $this->mibPlugin['pages_folder'] . (!empty($newname_folder) ? $newname_folder . '/' : '') . $newname;

        if (strlen($newname) < 3)
            $errors[] = __('Le nouveau nom doit être constitué d\'au moins 3 caractères.');
        else if (preg_match('/[^0-9a-zA-Z\-_]/', $newname))
            $errors[] = __('Il y a des caractères invalides dans le nouveau nom.');
        else if ($oldname_path == $newname_path)
            $errors[] = __('Le nom doit être différent de celui existant.');
        else if (file_exists($newname_path))
            $errors[] = __('Une rubrique avec ce nom existe déjà.');

        // Aucune erreur
        if (empty($errors)) {
            // renane
            @rename($oldname_path, $newname_path);

            $this->mibPlugin['json'] = array(
                'title' => __('Renommage effectué'),
                'value' => __('Le nouveau nom <strong>' . mib_html($newname) . '</strong> a été enregistré avec succès.'),
                'options' => array(
                    'type' => 'valid'
                ),
                'page' => array(
                    'update' => $this->mibPlugin['name']
                ),
                'element' => array(
                    'rename-box' => array(
                        'setStyles' => array(
                            'visibility' => 'hidden',
                            'opacity' => '0'
                        ),
                        'erase' => 'id'
                    ),
                    'rename-box-overlay' => array(
                        'morph' => array(
                            'opacity' => '0'
                        )
                    )
                )
            );
        } else
            error('<p>' . implode('</p><p>', $errors) . '</p>');
    }

    public function getPageContent()
    {
       
        return file_get_contents($this->mibPlugin['pages_folder'] . $this->mibPlugin['element']);
    }

    public function savePageContent()
    {

        global  $MIB_CONFIG;
        $errors = array();

        $page_path = $this->mibPlugin['pages_folder'] . $this->mibPlugin['element'];
        $page = (isset($_POST['page'])) ? mib_trim($_POST['page']) : '';
        if (empty($page)) $page = '<p>&nbsp;</p>';

        // liens relatif
        $page = str_replace('href="' . $MIB_CONFIG['base_url'] . '/public/', 'href="../public/', $page);
        foreach ($MIB_CONFIG['languages'] as $iso => $language) {
            if ($this->mibPlugin['pages_lang'] == $iso)
                $page = str_replace('href="' . $MIB_CONFIG['base_url'] . '/' . $iso . '/', 'href="', $page);
            else
                $page = str_replace('href="' . $MIB_CONFIG['base_url'] . '/' . $iso . '/', 'href="../' . $iso, $page);
        }
        $page = str_replace('href="' . $MIB_CONFIG['base_url'] . '/', 'href="', $page);
        $page = str_replace('src="' . $MIB_CONFIG['base_url'] . '/public/', 'src="../public/', $page);

        // Aucune erreur
        if (empty($errors)) {
            // création de la page
            $fp = fopen($page_path, 'w');
            fwrite($fp, $page);
            fclose($fp);

            $this->mibPlugin['json'] = array(
                'title' => __('Modifications effectuées'),
                'value' => __('La page a été mise à jour avec succès.'),
                'options' => array('type' => 'valid')
            );

        }
    }

    public function deleteContent()
    {

        $url =  !empty($this->mibPlugin['options'][2]) ? $this->mibPlugin['options'][2]: '';
        $lang = !empty($this->mibPlugin['options'][0]) ? $this->mibPlugin['options'][0]: '';
        $pageKey = MibboFormManager::getPageKey(['url'=>$url, 'lang'=>$lang]);
        $tplData = MibboFormManager::getPageTemplate($pageKey);
        if(!empty($tplData)){
            MibboFormManager::removePage($pageKey);
        }


        $deleted_name = end(explode('/', $this->mibPlugin['element']));
        @mib_rmdirr($this->mibPlugin['pages_folder'] . $this->mibPlugin['element']);
        $this->mibPlugin['json'] = array(
            'title' => __('Supression effectuée'),
            'value' => __('<strong>' . mib_html(current(explode('.html', $deleted_name))) . '</strong> a été supprimée avec succès.'),
            'options' => array(
                'type' => 'valid'
            ),
            'page' => array(
                'update' => $this->mibPlugin['name'],
                'remove' => $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/edit/' . $this->mibPlugin['element']
            )
        );
    }

    public function isPage()
    {
       
        return is_file($this->mibPlugin['pages_folder'] . $this->mibPlugin['element']);
    }

    public function isRubrique()
    {
       
        return is_dir($this->mibPlugin['pages_folder'] . $this->mibPlugin['element']);
    }

    public function isChangeTemplateRequest()
    {
       
        return strpos($this->mibPlugin['element'], '/changeTemplate') !== false;
    }

    public function displayFormPage($curPageTitle)
    {
       
        $parts = explode('/changeTemplate/', $this->mibPlugin['element']);

        $val = empty($parts[1]) ? null : $parts[1];

        $ok = MibboFormManager::changePageTemplate($curPageTitle, $val);
        if ($ok) {
            $this->mibPlugin['json'] = array(
                'title' => __('Modèle de page'),
                'value' => __('Le modèle de page a été  modifié '),
                'options' => array('type' => 'valid'),
                'page' => array(
                    'update' => ''
                )
            );
        } else {
            $this->mibPlugin['json'] = array(
                'title' => __('Modèle de page'),
                'value' => __('Le modèle de page n\'a pas  été  modifié '),
                'options' => array('type' => 'error')
            );
        }
        define('MIB_JSONED', 1);
    }

    public function displayPageEdit($curPageTitle,$currentTpl,  $selectTemplateId, $urlManage,$form,$pageTemplates)
    {
        global  $MIB_PAGE, $MIB_CONFIG;

        ?>
        <style type="text/css">
            #MIB_page .button .reload_page {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/arrow-circle.png');
            }

            #MIB_page .button .remove_page {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/document--minus.png');
            }
        </style>

        <?php if (empty($currentTpl)): ?>

        <form method="post" id="form_<?php echo $MIB_PAGE['uniqid']; ?>"
              action="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/edit/' . $this->mibPlugin['element']; ?>"
              target="_json">
            <?php else : ?>
                <?php $form->renderFormHeader() ?>
            <?php endif ?>
            <fieldset>
                <legend><?php echo mib_html('Page : ' . $curPageTitle); ?></legend>

                <?php if (!empty($pageTemplates)): ?>
                    <div class="option-row">
                        <div class="option-title">Modèle :</div>
                        <div class="option-item">
                            <select class="input" data-templatechange id="<?= $selectTemplateId ?>">
                                <option value="">pas de modèle</option>
                                <?php foreach ($pageTemplates as $pageTpl): ?>
                                    <?php $tplSelected = (!empty($currentTpl) && $currentTpl == $pageTpl['key']) ? 'selected' : ''; ?>
                                    <option value="<?= $pageTpl['key'] ?>"
                                            title="<?= $pageTpl['comment'] ?>" <?= $tplSelected ?>><?= $pageTpl['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="option-row">
                    <div class="option-title">Réf :</div>
                    <div class="option-item">
                        <p>
                            <a href="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/edit/' . $this->mibPlugin['element']; ?>"
                               confirm="Attention, le rechargement du contenu de la page effacera les modifications en cours non enregistrées.::Recharger le contenu ?">
                                <?php echo $curPageTitle; ?>
                            </a></p>
                    </div>
                </div>
                <div class="option-row">
                    <div class="option-title">URL :</div>
                    <div class="option-item">
                        <p>
                            <?php
                            // Page d'accueil
                            if ($this->mibPlugin['element'] == 'index.html') {
                                echo '<a href="' . $MIB_CONFIG['base_url'] . '/' . $this->mibPlugin['pages_lang'] . '" target="_blank">' . $MIB_CONFIG['base_url'] . '/' . $this->mibPlugin['pages_lang'] . '</a>';
                            } else {
                                echo '<a href="' . $MIB_CONFIG['base_url'] . '/' . $curPageTitle . '" target="_blank">' . $MIB_CONFIG['base_url'] . '/' . $curPageTitle . '</a>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </fieldset>
            <div>
                <?php if (empty($currentTpl)): ?>
                <textarea class="input" id="page<?php echo $MIB_PAGE['uniqid']; ?>" name="page" style="height:500px;">Chargement du contenu de la page en cours...</textarea>
                <script type="text/javascript">
                    // Charge le contenu de la page
                    var Editor_<?php echo $MIB_PAGE['uniqid']; ?> = new MIB_Wysiwyg($('page<?php echo $MIB_PAGE['uniqid']; ?>'), {
                        'iframe': '<?php if ($this->mibPlugin['element'] == 'index.html') {
                            echo $MIB_CONFIG['base_url'] . '/' . $this->mibPlugin['pages_lang'];
                        } else {
                            echo $MIB_CONFIG['base_url'] . '/' . $curPageTitle;
                        } ?>',
                        'loadJSON': 'json/<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/content/' . $this->mibPlugin['element']; ?>'
                    });
                    $('form_<?php echo $MIB_PAGE['uniqid']; ?>').addEvent('submit', function () {
                        Editor_<?php echo $MIB_PAGE['uniqid']; ?>.toTextarea();
                    });
                </script>
            </div>
            <div class="option-actions" style="margin: 10px 0;">
                <button type="submit" class="button"><span class="save">Enregistrer</span></button>
                <a href="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/delete/' . $this->mibPlugin['element']; ?>"
                   target="_json" class="button"
                   confirm="Voulez vous le supprimer la page <strong><?php echo mib_html($curPageTitle); ?></strong> ?::Supprimer ?::question"><span
                            class="remove_page">Supprimer la page</span></a>
            </div>
        </form>
    <?php else : ?>

        <?php $form->renderFormAllFields() ?>
        <?php $form->renderFormFooter(); ?>
        <a href="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/delete/' . $this->mibPlugin['element']; ?>"
           target="_json" class="button"
           confirm="Voulez vous le supprimer la page <strong><?php echo mib_html($curPageTitle); ?></strong> ?::Supprimer ?::question"><span
                    class="remove_page">Supprimer la page</span></a>
    <?php endif ?>
        <script>

            var selectTpl = document.getElementById('<?= $selectTemplateId ?>');
            if (selectTpl) {
                selectTpl.addEventListener('change', function () {
                    var value = this.value;
                    mibApp.showLoader();
                    window.mibApp.sendAjax('json/<?=$urlManage ?>/changeTemplate/' + value, null, function (response) {
                        mibApp.hideLoader();
                        MIB_Bo.jsontoaction(response);
                    });
                });
            }
        </script>
        <?php
    }

    public function displayRubriqueEdit()
    {
        global  $MIB_CONFIG;

        ?>
        <style type="text/css">
            <?php
                    foreach($MIB_CONFIG['languages'] as $iso => $language)
                        echo '#MIB_page .button .lang_'.$iso.' { background-image: url(\'../../'.MIB_THEME_DEFAULT_DIR.'admin/img/flags/'.$iso.'.png\'); }';
            ?>
            #MIB_page .button .folder {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/folder-horizontal.png');
            }

            #MIB_page .button .current_folder {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/folder-horizontal-open.png');
            }

            #MIB_page .button .create_page {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/document--plus.png');
            }

            #MIB_page .button .create_rub {
                background-image: url('{{tpl:MIB_PLUGIN}}/img/folder--plus.png');
            }
        </style>
        <p>
            <?php

            echo '<a href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/browse" class="button fleft" style="margin-right:5px;" target="' . $this->mibPlugin['name'] . '" ><span class="lang_' . $this->mibPlugin['pages_lang'] . '">' . $MIB_CONFIG['languages'][$this->mibPlugin['pages_lang']] . '</span></a>';

            $last_folder = $this->mibPlugin['pages_lang'];
            $cur_folder = '';

            if (!empty($this->mibPlugin['element'])) {
                $folders = explode('/', $this->mibPlugin['element']);
                $i = 0;
                foreach ($folders as $folder) {
                    $i++;
                    $cur_folder .= $folder . '/';
                    echo ' <a href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/browse/' . $cur_folder . '" class="button fleft" style="margin-right:5px;" target="' . $this->mibPlugin['name'] . '" ><span class="' . (($i == count($folders)) ? 'current_folder' : 'folder') . '">' . $folder . '</span></a>';
                    $last_folder = $folder;
                }
            }
            ?>
            <a id="create_page" method="post" class="button fright tips" style="margin-left:5px;"
               href="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/newpage/' . $cur_folder; ?>"
               title="Créer une page"
               rel="Ajouter une nouvelle page dans la rubrique [<?php echo mib_html($last_folder); ?>]"
               target="_action"><span class="create_page">Page</span></a>
            <a id="create_pagerub" method="post" class="button fright tips" style="margin-left:5px;"
               href="<?php echo $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/newrub/' . $cur_folder; ?>"
               title="Créer une rubrique"
               rel="Ajouter une nouvelle rubrique dans [<?php echo mib_html($last_folder); ?>]" target="_action"><span
                        class="create_rub">Rubrique</span></a>
            <?php
            foreach ($MIB_CONFIG['languages'] as $iso => $language)
                echo '<a href="' . $this->mibPlugin['name'] . '/' . $iso . '/browse" class="fright tips" style="margin:7px 2px; 0px 2px" target="' . $this->mibPlugin['name'] . '" title="' . $language . '" rel="Afficher les pages de la langue : ' . $language . '." ><img src="../../{{tpl:MIB_THEME_DEFAULT_DIR}}admin/img/flags/' . $iso . '.png" alt="[' . $iso . ']" /></a>';
            ?>
        </p>
        <br class="clear">
        <?php

        // Tableau de l'affichage des pages
        $MIB_PAGE_search = mib_search_build(array(
            'target' => $this->mibPlugin['name'], // URL de la page dans laquelle s'affiche le tableau des résulats (généralement $this->mibPlugin['name'])
            'sort_by_autorized' => array('name', 'size', 'modified'), // Champs de la table sur lequels le tri est autorizé
            'sort_by_default' => 'name', // Champ de la table utilisé pour le tri par défaut
            'sort_dir_default' => 'ASC', // Ordre de tri par défaut
            'cols' => array( // Colonnes à affiché pour le tableau des résultats
                'name' => 'Nom',
                'size' => 'Taille',
                'modified' => 'Date de modification',
            )
        ));
        ?>
        <style type="text/css">
            .<?php echo $this->mibPlugin['name']; ?>-pageresults .tc1 img {
                vertical-align: middle;
            }

            .<?php echo $this->mibPlugin['name']; ?>-pageresults .tc1 img.fright {
                margin: 3px 2px;
            }

            .<?php echo $this->mibPlugin['name']; ?>-pageresults .tc2 {
                width: 110px;
                text-align: center;
            }

            .<?php echo $this->mibPlugin['name']; ?>-pageresults .tc3 {
                width: 160px;
                text-align: center;
            }
        </style>
        <table class="table-results <?php echo $this->mibPlugin['name']; ?>-pageresults" style="margin-top:7px;">
            <?php

            // Construit le header des résultats de la recherche
            if (!isset($MIB_PAGE_search['sort_by_autorized']) || !is_array($MIB_PAGE_search['sort_by_autorized']))
                $MIB_PAGE_search['sort_by_autorized'] = array();

            // Préparation des données necessaires à l'affichage des résultats
            if (empty($MIB_PAGE_search['num_cols']))
                $MIB_PAGE_search['num_cols'] = count($MIB_PAGE_search['cols']); // Nombre de colonnes dans le tableau de résultats

            echo '<thead><tr>';

            $i = 1;
            foreach ($MIB_PAGE_search['cols'] as $k => $v) {
                echo '<th class="tc' . $i . ($i == 1 ? ' tcl' : '') . ($i == $MIB_PAGE_search['num_cols'] ? ' tcr' : '') . '">';
                if (in_array($k, $MIB_PAGE_search['sort_by_autorized'])) { // Il y a un tri possible sur cette colonne
                    echo '<a href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/browse/' . $cur_folder . '?' . implode('&amp;', $MIB_PAGE_search['filters_url']) . '&amp;sort_by=' . urlencode($k) . ($MIB_PAGE_search['sort_by'] == $k ? '&amp;sort_dir=' . $MIB_PAGE_search['sort_dir_selected'] : '') . '" class="' . ($MIB_PAGE_search['sort_by'] == $k ? 'sort_dir_' . strtoupper($MIB_PAGE_search['sort_dir']) . ' to_sort_dir_' . strtoupper($MIB_PAGE_search['sort_dir_selected']) : 'to_sort_dir_' . strtoupper($MIB_PAGE_search['sort_dir_default'])) . '" target="' . $MIB_PAGE_search['target'] . '">' . $v . '</a>';
                } else
                    echo $v;
                echo '</th>';
                $i++;
            }

            echo '</tr></thead>';

            ?>
            <tbody>
            <?php
            $this->mibPlugin['dir'] = array();
            $this->mibPlugin['dir']['scandir'] = scandir($this->mibPlugin['pages_folder'] . $this->mibPlugin['element']);
            $this->mibPlugin['dir']['view'] = $this->mibPlugin['dir']['order']['name'] = $this->mibPlugin['dir']['order']['size'] = $this->mibPlugin['dir']['order']['modified'] = array();

            foreach ($this->mibPlugin['dir']['scandir'] as $k => $v) {
                // Permet de na pas afficher les fichiers cachés et le retour à la raçine
                if ($v{0} != '.') {
                    $this->mibPlugin['dir']['view'][$k] = array(
                        'name' => $v,
                        'size' => 0,
                        'modified' => 0,
                        'icon' => 'document.png',
                        'type' => false,
                    );

                    // Dossier
                    if (is_dir($this->mibPlugin['pages_folder'] . $this->mibPlugin['element'] . '/' . $v)) {
                        $this->mibPlugin['dir']['view'][$k]['type'] = 'dir';
                        $this->mibPlugin['dir']['view'][$k]['elements'] = count(scandir($this->mibPlugin['pages_folder'] . $this->mibPlugin['element'] . '/' . $v)) - 2;
                        $this->mibPlugin['dir']['view'][$k]['icon'] = 'folder-horizontal.png';

                        $this->mibPlugin['dir']['order']['name'][$k] = '000000000_' . utf8_strtolower($v); // petit hack permettant de tjs classer les dossier a part
                        $this->mibPlugin['dir']['order']['size'][$k] = $this->mibPlugin['dir']['view'][$k]['elements'] / 1000000; // petit hack permettant de tjs classer les dossier a part
                        $this->mibPlugin['dir']['order']['modified'][$k] = 0;
                    } // Fichier
                    else if (is_file($this->mibPlugin['pages_folder'] . $this->mibPlugin['element'] . '/' . $v)) {
                        $this->mibPlugin['dir']['view'][$k]['type'] = 'file';

                        // Détection de l'icon en fonction de lextension
                        $current_ext = strtolower(@end(explode('.', $v)));
                        if ($current_ext == 'html')
                            $this->mibPlugin['dir']['view'][$k]['icon'] = 'document-text-image.png';

                        $this->mibPlugin['dir']['order']['name'][$k] = utf8_strtolower($v);
                        $this->mibPlugin['dir']['order']['size'][$k] = sprintf('%u', filesize($this->mibPlugin['pages_folder'] . $this->mibPlugin['element'] . '/' . $v));
                        $this->mibPlugin['dir']['order']['modified'][$k] = filemtime($this->mibPlugin['pages_folder'] . $this->mibPlugin['element'] . '/' . $v);
                    }

                    $this->mibPlugin['dir']['view'][$k]['size'] = $this->mibPlugin['dir']['order']['size'][$k];
                    $this->mibPlugin['dir']['view'][$k]['modified'] = $this->mibPlugin['dir']['order']['modified'][$k];

                }
            }

            if ($MIB_PAGE_search['sort_dir'] == 'ASC')
                asort($this->mibPlugin['dir']['order'][$MIB_PAGE_search['sort_by']]);
            else
                arsort($this->mibPlugin['dir']['order'][$MIB_PAGE_search['sort_by']]);

            // Affiche le résultat
            if (!empty($this->mibPlugin['dir']['view'])) {
                foreach ($this->mibPlugin['dir']['order'][$MIB_PAGE_search['sort_by']] as $ViewK => $ViewV) {
                    echo '<tr>';

                    // Affiche les colonnes
                    $i = 1;
                    foreach ($MIB_PAGE_search['cols'] as $k => $v) {
                        echo '<td class="tc' . $i . ($i == 1 ? ' tcl' : '') . ($i == $MIB_PAGE_search['num_cols'] ? ' tcr' : '') . '">';

                        if ($k == 'name') {
                            // Editer/Supprimer
                            if ($this->mibPlugin['dir']['view'][$ViewK]['type'] == 'dir') {
                                echo '<a class="tips" title="Supprimer" rel="Supprimer la rubrique [' . mib_html($this->mibPlugin['dir']['view'][$ViewK]['name']) . ']" href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/delete/' . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" confirm="La rubrique <strong>[' . mib_html($this->mibPlugin['dir']['view'][$ViewK]['name']) . ']</strong> contient ' . ($this->mibPlugin['dir']['view'][$ViewK]['elements'] > 1 ? $this->mibPlugin['dir']['view'][$ViewK]['elements'] . ' éléments' : $this->mibPlugin['dir']['view'][$ViewK]['elements'] . ' élément') . '. Voulez vous la supprimer avec son contenu ?::Supprimer ?::question" target="_json"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/folder--minus.png" alt="[supprimer]" /></a>';
                                echo '<a id="action-rename-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" method="post" class="tips btn-rename" title="Renommer" rel="Renommer la rubrique [' . mib_html($this->mibPlugin['dir']['view'][$ViewK]['name']) . ']" href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/rename/' . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" target="_action"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/folder--pencil.png" alt="[renomer]" /></a>';
                            } else {
                                echo '<a class="tips" title="Supprimer" rel="Supprimer la page ' . mib_html(current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2))) . '" href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/delete/' . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" confirm="Voulez vous le supprimer la page <strong>' . mib_html(current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2))) . '</strong> ?::Supprimer ?::question" target="_json"><img class="fright" src="{{tpl:MIB_PLUGIN}}/img/document--minus.png" alt="[supprimer]" /></a>';
                                echo '<a title="' . $this->mibPlugin['pages_lang'] . '/' . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2)) . '" href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/edit/' . (!empty($this->mibPlugin['element']) ? $this->mibPlugin['element'] . '/' : '') . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" favicon="{{tpl:MIB_PLUGIN}}/img/document--pencil.png"><img class="fright tips" title="Éditer" rel="Éditer la page ' . mib_html(current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2))) . '" src="{{tpl:MIB_PLUGIN}}/img/document--pencil.png" alt="[éditer]" /></a>';
                            }

                            // Page d'accueil
                            if (empty($this->mibPlugin['element']) && $this->mibPlugin['dir']['view'][$ViewK]['name'] == 'index.html') {
                                echo '<img src="{{tpl:MIB_PLUGIN}}/img/home.png" alt="[ico]" /> ';
                                echo '<a id="item-name-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" href="' . $MIB_CONFIG['base_url'] . '/' . $this->mibPlugin['pages_lang'] . '" target="_blank">index (page d\'accueil ' . $this->mibPlugin['pages_lang'] . ')</a>';
                            } else {
                                echo '<img src="{{tpl:MIB_PLUGIN}}/img/' . $this->mibPlugin['dir']['view'][$ViewK]['icon'] . '" alt="[ico]" /> ';

                                if ($this->mibPlugin['dir']['view'][$ViewK]['type'] == 'dir')
                                    echo '<a id="item-name-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" href="' . $this->mibPlugin['name'] . '/' . $this->mibPlugin['pages_lang'] . '/browse' . (!empty($this->mibPlugin['element']) ? '/' . $this->mibPlugin['element'] . '/' : '/') . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" target="' . $this->mibPlugin['name'] . '">' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '</a>';
                                else
                                    echo '<a id="item-name-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '" href="' . $MIB_CONFIG['base_url'] . '/' . $this->mibPlugin['pages_lang'] . (!empty($this->mibPlugin['element']) ? '/' . $this->mibPlugin['element'] : '') . '/' . current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2)) . '" target="_blank">' . current(explode('.html', $this->mibPlugin['dir']['view'][$ViewK]['name'], 2)) . '</a>';
                            }
                        } else if ($k == 'size') {
                            if ($this->mibPlugin['dir']['view'][$ViewK]['type'] == 'dir') {
                                echo '<span id="item-elements-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '">' . ($this->mibPlugin['dir']['view'][$ViewK]['elements'] > 1 ? $this->mibPlugin['dir']['view'][$ViewK]['elements'] . ' éléments' : $this->mibPlugin['dir']['view'][$ViewK]['elements'] . ' élément') . '</span>';
                            } else
                                echo '<span id="item-size-' . $this->mibPlugin['dir']['view'][$ViewK]['name'] . '">' . mib_bytestohuman($this->mibPlugin['dir']['view'][$ViewK]['size']) . '</span>';
                        } else if ($k == 'modified') {
                            echo format_time($this->mibPlugin['dir']['view'][$ViewK]['modified'], 'd/m/Y - H:i', true);
                        }

                        echo '</td>';
                        $i++;
                    }

                    echo '</tr>';
                }
            } else // La rubrique est vide
                echo '<tr><td class="tc-no-result" colspan="' . $MIB_PAGE_search['num_cols'] . '">La rubrique <strong>[' . $last_folder . ']</strong> est vide.</td></tr>';
            ?>
            </tbody>
        </table>
        <script type="text/javascript">
            // Créer une nouvelle rubrique
            $$('#create_pagerub, #create_page').addEvent('click', function (e) {
                e.stop();
                $prompt(this.get('rel'), this.retrieve('tip:title', this.get('title')), {
                    id: 'new-box',
                    hide: false,
                    onClose: function (value) {
                        if (value) { // Une valeur a été entrée
                            this.store('data', 'new=' + encodeURIComponent(value));
                            this.set('target', '_json');
                            MIB_Bo.load(this);
                        } else {
                            $('new-box').setStyles({
                                'visibility': 'hidden',
                                'opacity': 0
                            }).erase('id');
                            $('new-box-overlay').morph({'opacity': 0});
                        }
                    }.bind(this)
                });
            });

            // Renomer une rubrique
            $$('.btn-rename').addEvent('click', function (e) {
                e.stop();
                var elementUID = this.get('id').replace('action-rename-', '');

                $prompt(this.get('rel'), this.retrieve('tip:title', this.get('title')), {
                    id: 'rename-box',
                    hide: false,
                    value: $('item-name-' + elementUID).get('text'),
                    onClose: function (value) {
                        if (value) { // Une valeur a été entrée
                            this.store('data', 'new=' + encodeURIComponent(value));
                            this.set('target', '_json');
                            MIB_Bo.load(this);
                        } else {
                            $('rename-box').setStyles({
                                'visibility': 'hidden',
                                'opacity': 0
                            }).erase('id');
                            $('rename-box-overlay').morph({'opacity': 0});
                        }
                    }.bind(this)
                });
            });
        </script>
        <?php
    }
}
