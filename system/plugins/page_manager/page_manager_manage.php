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


require_once 'BoPageManagerPlugin.php';
$boPageManager = new BoPageManagerPlugin();
$boPageManager->initRequest($MIB_PLUGIN);

// Requète JSON
if (defined('MIB_JSON') && $MIB_PLUGIN['action'] !== 'form') {

    // L'utilisateur a t'il les permissions d'écriture ?
    if (!$MIB_USER['can_write_plugin'])
        error(__('Vous n\'avez pas la permission d\'effectuer cette action.'));

    // Dossier
    if ($boPageManager->isRubrique()) {

        // Création d'une nouvelle rubrique
        if ($MIB_PLUGIN['action'] == 'newrub') {
            $boPageManager->processNewRub();
        } // Création d'une nouvelle page
        else if ($MIB_PLUGIN['action'] == 'newpage') {
            $boPageManager->processNewPage();
        } // Renome une rubrique
        else if ($MIB_PLUGIN['action'] == 'rename') {
            $boPageManager->processRename();
        }
    } // Page
    else if ($boPageManager->isPage()) {
        // récupère uniquement le contenu de la page
        if ($MIB_PLUGIN['action'] == 'content') {
            $content = $boPageManager->getPageContent();
            $MIB_PLUGIN['json'] = array(
                'content' => empty($content) ? '<p>&nbsp;</p>' : $content
            );
        } // Edition
        else if ($MIB_PLUGIN['action'] == 'edit') {
            $boPageManager->savePageContent();
        }
    }
    // Concerne les page et les rubrique
    if (file_exists($MIB_PLUGIN['pages_folder'] . $MIB_PLUGIN['element'])) {

        // Supprime une page ou une rubrique
        if ($MIB_PLUGIN['action'] == 'delete') {
            $boPageManager->deleteContent();
        }

    }

    define('MIB_JSONED', 1);
    return;
} // Requète AJAX
else if (defined('MIB_AJAX'))
    define('MIB_AJAXED', 1);

// Affiche une page
if ($MIB_PLUGIN['action'] === 'form' || (isset($MIB_PAGE['ext']) && $MIB_PAGE['ext'] == 'html' && is_file($MIB_PLUGIN['pages_folder'] . $MIB_PLUGIN['element']))) {

    // Edition
    if ($MIB_PLUGIN['action'] == 'form') {
        $curPageTitle = $MIB_PLUGIN['pages_lang'] . '/' . current(explode('.html', $MIB_PLUGIN['element'], 2));
        if ($boPageManager->isChangeTemplateRequest()) {
            $boPageManager->displayFormPage($curPageTitle);
            return;
        }
        $tplData = MibboFormManager::getPageTemplate($curPageTitle);
        $currentTpl  = !empty($tplData['key'])? $tplData['key'] : null;
        $form = MibboFormManager::getPageForm($currentTpl, $curPageTitle, $MIB_PLUGIN['pages_lang']);
        if (empty($form)) {
            error("Erreur dans le chargement du formulaire");
            exit();
        }
        $form->lang = $MIB_PLUGIN['pages_lang'];
        if ($form->isSubmited()) {
            $retour = $form->handleRequest();
            $MIB_PLUGIN['json'] = $retour;
            define('MIB_JSONED', 1);
            return;
        }
    } else if ($MIB_PLUGIN['action'] == 'edit') {

        $curPageTitle = $MIB_PLUGIN['pages_lang'] . '/' . current(explode('.html', $MIB_PLUGIN['element'], 2));
        $urlManage = $MIB_PLUGIN['name'] . '/' . $MIB_PLUGIN['pages_lang'] . '/form/' . $MIB_PLUGIN['element'];
        $selectId = uniqid();
        // on charge la liste des modèles de page
        $pageTemplates = MibboFormManager::getPageTemplates();
        if (!empty($pageTemplates)) {
            // on vérifie si la page est associé à un des modèle
            $tplData = MibboFormManager::getPageTemplate($curPageTitle);
            $currentTpl  = !empty($tplData['key'])? $tplData['key'] : null;
            $foundTpl = false;
            // on vérifie que le modèle existe
            if (!empty($currentTpl)) {
                foreach ($pageTemplates as $pageTpl) {
                    if (!empty($pageTpl['key']) && $pageTpl['key'] == $currentTpl) {
                        $foundTpl = true;
                        break;
                    }
                }
                $currentTpl = $foundTpl ? $currentTpl : null;
            }
        }
        if (!empty($currentTpl)) {
            $form = MibboFormManager::getPageForm($currentTpl, $curPageTitle, $MIB_PLUGIN['pages_lang']);
            if (empty($form)) {
                error("Erreur dans le chargement du formulaire");
                exit();
            }
            $form->lang = $MIB_PLUGIN['pages_lang'];
            $form->url = $urlManage;
            //domId = "form_".$MIB_PAGE['uniqid'];

            if ($form->isSubmited()) {
                $retour = $form->handleRequest();
                $MIB_PLUGIN['json'] = $retour;
                define('MIB_JSONED', 1);
                return;
            }
        }
        $boPageManager->displayPageEdit($curPageTitle,$currentTpl,  $selectId, $urlManage,$form??null,$pageTemplates);
    } // Affiche une rubrique

}else if ($boPageManager->isRubrique()) {
    $boPageManager->displayRubriqueEdit();
}