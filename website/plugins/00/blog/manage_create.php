<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$blog = BlogPlugin::getInstance();

$form = $blog->getForm();
$form->url = $MIB_PLUGIN['name']. '/' . $MIB_PLUGIN['action'];


if (!empty($MIB_PLUGIN['id']) && $MIB_PLUGIN['action'] === 'edit') {
    $form->loadData($MIB_PLUGIN['id']);
}





//$partList = $blog->getContentPartList();

if ($form->isSubmited()) {

    //$form->addParts($partList);

    $retour = $form->handleRequest();


    if(!in_array('addFile',$MIB_PLUGIN['options']) &&  !in_array('deleteFile',$MIB_PLUGIN['options']) ){
        if ($MIB_PLUGIN['action'] === 'create') {
            $retour['page'] = ['remove'=> $MIB_PLUGIN['name'].'/create' , 'update'=> $MIB_PLUGIN['name'],'load'=> $MIB_PLUGIN['name'].'/edit/'.$retour['id']];
        }
    }

    $pageKey = MibboFormManager::getPageKey(['url'=>$form->datas['slug'], 'lang'=>'fr']);
    MibboFormManager::changePageTemplate($pageKey, 'blog','blog');

    $MIB_PLUGIN['json'] = $retour;
    define('MIB_JSONED', 1);
    return;
}

?>

<h1><Articles</h1>



<!--Ajouter un des élements suivants :-->
<!--    <br>-->
<?php //foreach ($partList as $part): ?>
<!--    <button type="button" class="BlogPart-add BlogPart----><?//= $part['type'] ?><!--" data-type="--><?//= $part['type'] ?><!--">--><?//= $part['label'] ?><!--</button>-->
<?php //endforeach; ?>


  <br>

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
<?php $form->renderFormField('title') ?>
<?php $form->renderFormField('slug') ?>
<?php $form->renderFormField('category') ?>
<?php $form->renderFormField('publishState') ?>
<?php $form->renderFormField('date') ?>
<?php $form->renderFormField('year') ?>
<?php $form->renderFormField('interviewArticle') ?>
<?php $form->renderFormFooter(); ?>


