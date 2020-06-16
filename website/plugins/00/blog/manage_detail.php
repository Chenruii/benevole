<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$blog = BlogPlugin::getInstance();

$form = $blog->getForm();
$form->url = $MIB_PLUGIN['name']. '/' . $MIB_PLUGIN['action'];


if (!empty($MIB_PLUGIN['id']) && $MIB_PLUGIN['action'] === 'edit') {
    $form->loadData($MIB_PLUGIN['id']);
}


if ( $MIB_PLUGIN['action'] === 'delete') {

    echo $MIB_PLUGIN['id']  .' delete';
    if(empty($MIB_PLUGIN['id'])){
        $MIB_PLUGIN['json'] = array(
            'title'		=> __('Erreur'),
            'value'		=> __('Erreur.'),
            'options'		=> array('type' => 'error'),
            'page'		=> array(
                'update'		=> $MIB_PLUGIN['name'] // Il faut update pour regénérer la liste des utilisateurs
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
            'update'		=> $MIB_PLUGIN['name'], // Il faut update pour regénérer la liste des utilisateurs
            'remove'		=> $MIB_PLUGIN['name'].'/edit/'.$MIB_PLUGIN['id'] // Il faut update pour regénérer la liste des utilisateurs
        )
    );
    define('MIB_JSONED', 1);
    return ;
}



//$partList = $blog->getContentPartList();

if ($form->isSubmited()) {

    //$form->addParts($partList);

    $retour = $form->handleRequest();


    if(!in_array('addFile',$MIB_PLUGIN['options'])
            &&  !in_array('deleteFile',$MIB_PLUGIN['options'])
            &&  !in_array('editFile',$MIB_PLUGIN['options'] )
            &&  !in_array('sortFile',$MIB_PLUGIN['options'] )){
        if ($MIB_PLUGIN['action'] === 'create') {
            $retour['page'] = ['remove' => $MIB_PLUGIN['name'].'/create','update'=> $MIB_PLUGIN['name']];
        }else if($MIB_PLUGIN['action'] === 'edit') {
            $retour['page'] = ['remove' => $MIB_PLUGIN['name'].'/edit','update'=> $MIB_PLUGIN['name']];
        }
    }

//    $pageKey = MibboFormManager::getPageKey(['url'=>$form->datas['slug'], 'lang'=>'fr']);
//    MibboFormManager::changePageTemplate($pageKey, 'blog','blog');

    $MIB_PLUGIN['json'] = $retour;
    define('MIB_JSONED', 1);
    return;
}

?>

<h1><Articles</h1>
<br>
    <hr>
    <a href="<?php echo $MIB_PLUGIN['name'] ?>/delete/<?php echo $MIB_PLUGIN['id'] ?>" target="_json" confirm="Supprimer la page ? ">Supprimer </a>
    <hr>
    <br>


<!--Ajouter un des élements suivants :-->
<!--    <br>-->
<?php //foreach ($partList as $part): ?>
<!--    <button type="button" class="BlogPart-add BlogPart----><?//= $part['type'] ?><!--" data-type="--><?//= $part['type'] ?><!--">--><?//= $part['label'] ?><!--</button>-->
<?php //endforeach; ?>

<div>
    <a href="/<?= $form->getFieldDisplay('slug') ?>" target="_blank"> Voir la page : <?= $form->getFieldDisplay('slug') ?></a>
</div>
    <br>  <br>

<script>
    window.customizeFormEngine= function(){
        if(!this.ckEditorInit){
            CKEDITOR.config.format_callout = { element: 'p', attributes: { 'class': 'Post-callout' },name: 'Encadré' };
            CKEDITOR.config.format_tags  = 'p;h1;h2;h3;callout';
            this.ckEditorInit= true ;
        }
    }
</script>


<?php $form->renderFormHeader() ?>

<?php
    $fields = $form->fields;
    $isInterview =  isset($form->datas['interviewArticle']) && ( $form->datas['interviewArticle']==='true' ||  $form->datas['interviewArticle']===true  ) ;
    foreach ($fields as $field){

        if($field['key']==='interviewArticle')
            continue;

        if(!$isInterview && substr($field['key'],0,9)==='interview'){
            continue;
        }
        $form->renderFormField($field['key']);
    }
  ?>
<?php $form->renderFormFooter(); ?>


