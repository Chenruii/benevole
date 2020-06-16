<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

if ( defined('MIB_AJAX') )
    define('MIB_AJAXED', 1);



$info =  null;
if (isset($MIB_PAGE['info'])) {
    $MIB_PLUGIN['options'] = explode('/', $MIB_PAGE['info']);
    $MIB_PLUGIN['action'] = mib_trim($MIB_PLUGIN['options'][0]); // "remove", "create", "edit", "default"
    $MIB_PLUGIN['id'] = !empty($MIB_PLUGIN['options'][1]) ? intval($MIB_PLUGIN['options'][1]) : null; // "id du groupe"
    $info = $MIB_PAGE['info'];
}

require_once  'AccountPluginClass.php';
require_once  'AccountSourcePluginClass.php';

if(strpos($info,'recurrent_')===0){
    require_once MIB_ACCOUNT_HTMLPARTS."manage-recurrent.php";
    return;
}


switch ($info){
    case "importAccount":
        $source = new AccountSourcePlugin();
        $error = null;
        $ok = $source->importAccount($error);
        $MIB_PLUGIN['json'] = array(
            'title'		=> 'Import de fichier ',
            'value'		=> $ok ? 'Fichier importé avec succés':  ('Erreur dans l\'import : '+ $error) ,
            'options'		=> array('type' =>  $ok ? 'success': 'error')
        );
        return;
    case 'getNewsLetterFile':
        $accManager = new AccountPlugin();
        $accManager->downloadListEmails();
        return ;
    case 'getCompteFile':
        $accManager = new AccountPlugin();
        $accManager->downloadListAccounts();
        return ;
    case 'previewNewsLetter':
        $accManager = new AccountPlugin();
        $countArticles = intval($_GET['nbarticles']);
        if(empty($count)){
            $MIB_PLUGIN['json'] = array(
                'title'		=> 'Creation de newsLetter ',
                'value'		=>'Il faut préciser une nombre d\'articles',
                'options'		=>array('type'=>'error')
            );
        }
        $accManager->initMailchimp();
        $html = $accManager->mailChimp->getPreviewTemplate($countArticles);
        echo  $html;
        return;
    case 'createNewsLetter':
        $accManager = new AccountPlugin();
        $countArticles = intval($_POST['nbarticles']);
        if(empty($count)){
            $MIB_PLUGIN['json'] = array(
                'title'		=> 'Creation de newsLetter ',
                'value'		=>'Il faut préciser une nombre d\'articles',
                'options'		=>array('type'=>'error')
            ); 
        }
        $accManager->initMailchimp();
        $ok = $accManager->mailChimp->createNewLetter($countArticles);
        $MIB_PLUGIN['json'] = array(
            'title'		=> 'Creation de newsLetter',
            'value'		=> $ok ? 'Creation de newsLetter avec succés': 'Erreur Creation de newsLetter',
            'options'		=> array('type' =>  $ok ? 'success': 'error')
        );
        return ;
    case 'recurrent':

}
require_once MIB_ACCOUNT_HTMLPARTS."manage-home.php";

//$accManager = new AccountPlugin();     $accManager->initMailchimp();
//$ok = $accManager->mailChimp->test();