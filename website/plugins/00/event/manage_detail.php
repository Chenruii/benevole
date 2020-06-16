<?php

/*--------------------------------------------*/
/*  chargement du formulaire                  */
/*--------------------------------------------*/
$form = MibboFormManager::getForm($MIB_PLUGIN['name']);
$form->url = $MIB_PLUGIN['name']. '/' . $MIB_PLUGIN['action'];
if (empty($form)) {
    error("Erreur dans le chargement du formulaire");
    exit();
}

// on ajoute les sources de données dynamiques définies dans  [plugin]_manage.php
$form->sources = $sources;

// on charge les données

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
            'remove'		=> $MIB_PLUGIN['name'].'/edit' // Il faut update pour regénérer la liste des utilisateurs
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

    <h1><?= strtoupper($MIB_PLUGIN['name']) ?></h1>
    <br>
    <hr>
    <a href="<?php echo $MIB_PLUGIN['name'] ?>/delete/<?php echo $MIB_PLUGIN['id'] ?>" target="_json">Supprimer </a>
    <hr>

<?php $form->renderFormHeader() ?>
    <fieldset>
        <legend> Données générales </legend>
        <?php $form->renderFormField('title') ?>
        <?php $form->renderFormField('imagelist') ?>
        <?php $form->renderFormField('eventDate') ?>
    </fieldset>
    <fieldset>
        <legend> Description </legend>
        <img src="../.././website/themes/default/admin/img/flags/fr.png">
        <?php $form->renderFormField('summaryFR') ?>
        <?php $form->renderFormField('descriptionFR') ?>
        <div class="Form-field Form-field--submit">
            <button type="submit" class="Form-submit"> valider</button>
            <br>
        </div>
        <hr/>
        <img src="../.././website/themes/default/admin/img/flags/en.png">
        <?php $form->renderFormField('summaryEN') ?>
        <?php $form->renderFormField('descriptionEN') ?>
        <div class="Form-field Form-field--submit">
            <button type="submit" class="Form-submit"> valider</button>
            <br>
        </div>
        <hr/>
        <img src="../.././website/themes/default/admin/img/flags/it.png">
        <?php $form->renderFormField('summaryIT') ?>
        <?php $form->renderFormField('descriptionIT') ?>
        <div class="Form-field Form-field--submit">
            <button type="submit" class="Form-submit"> valider</button>
            <br>
        </div>
        <hr/>
        <img src="../.././website/themes/default/admin/img/flags/nl.png">
        <?php $form->renderFormField('summaryNL') ?>
        <?php $form->renderFormField('descriptionNL') ?>
        <div class="Form-field Form-field--submit">
            <button type="submit" class="Form-submit"> valider</button>
            <br>
        </div>
        <hr/>
    </fieldset>
    <fieldset>
        <?php $form->renderFormField('slider') ?>
        <?php $form->renderFormField('imageSliderMobile') ?>
    </fieldset>
<?php $form->renderFormFooter();