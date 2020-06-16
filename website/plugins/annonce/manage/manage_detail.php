<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/06/2018
 * Time: 21:21
 */



// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

/*--------------------------------------------*/
/*  chargement du formulaire                  */
/*--------------------------------------------*/
$form = MibboFormManager::getForm($MIB_PLUGIN['name'],__DIR__.DIRECTORY_SEPARATOR."..");
$form->url = $MIB_PLUGIN['name'].'/' . $MIB_PLUGIN['action'];
if (empty($form)) {
    error("Erreur dans le chargement du formulaire");
    exit();
}

// on ajoute les sources de données dynamiques définies dans le fichier appellant
$form->sources = empty($sources) ? [] : $sources;

// on charge les données

if ( $MIB_PLUGIN['action'] === 'delete') {

    echo $MIB_PLUGIN['id']  .' delete';
    if(empty($MIB_PLUGIN['id'])){
        $MIB_PLUGIN['json'] = array(
            'title'		=> __('Erreur'),
            'value'		=> __('Erreur.'),
            'options'		=> array('type' => 'error'),
            'page'		=> array(
                'update'		=> $MIB_PLUGIN['name']
            )
        );
        return ;
    }
    $form->deleteData($MIB_PLUGIN['id']);
    $MIB_PLUGIN['json'] = array(
        'title'		=> __('Suppression effectuée'),
        'value'		=> __('La suppression a été éfféctuée avec succès.'),
        'options'		=> array('type' => 'valid'),
        'page'		=> array(
            'update'		=> $MIB_PLUGIN['name'], // Il faut update pour regénérer la liste
            'remove'		=> $MIB_PLUGIN['name'].'/edit' // Il faut update pour regénérer la liste
        )
    );
    define('MIB_JSONED', 1);
    return ;
}


if (!empty($MIB_PLUGIN['id']) && $MIB_PLUGIN['action'] === 'edit') {
    $form->loadData($MIB_PLUGIN['id']);
}

/*---------------------------------------------------------*/
/*  Soumission du formulaire  (data et images et fichiers )*/
/*---------------------------------------------------------*/
if ($form->isSubmited()) {
    $retour = $form->handleRequest();
    if ($MIB_PLUGIN['action'] === 'create') {
        $retour['page'] = ['remove' => $MIB_PLUGIN['name'].'/create','update'		=> $MIB_PLUGIN['name']];
    }

    $MIB_PLUGIN['json'] = $retour;
    define('MIB_JSONED', 1);
    return;
}

/*--------------------------------------------*/
/*  Rendu du formulaire                       */
/*--------------------------------------------*/
?>

    <h1>Annonce</h1>
<br>
<hr>
<a href="<?php echo $MIB_PLUGIN['name'] ?>/delete/<?php echo $MIB_PLUGIN['id'] ?>" target="_json">Supprimer </a>
    <hr>

<?php $form->renderFormHeader() ?>
<?php $form->renderFormAllFields() ?>
<?php $form->renderFormFooter();
