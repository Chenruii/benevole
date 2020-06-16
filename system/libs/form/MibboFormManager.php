<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/06/2018
 * Time: 21:43
 */
require_once "MibboAbstractRender.php";
require_once "MibboForm.php";
require_once "MibboList.php";
require_once "MibboFormType.php";

class MibboFormManager
{

    static $forms = [];
    static $list = [];


    /** Méthode de processus  --------------------------------------- */

    /**
     * @param $key
     * @return MibboForm|null
     */
    public static function getForm($key,$dir =null)
    {

        // on utilise le cache
        if (!empty(self::$forms[$key])) {
            return self::$forms[$key];
        }

        $dir  = empty($dir) ?  MIB_PATH_VAR .'forms' : $dir;
        $form = self::_getForm($key, $dir);
        if (empty($form))
            if (empty($form))
                return null;
        // on l'ajoute au cache
        self::$forms[$key] = $form;

        return $form;
    }

    /**
     * @param $key
     * @return MibboList|null
     */
    public static function getList($key,$dir=null)
    {

        // on utilise le cache
        if (!empty(self::$list[$key])) {
            return self::$list[$key];
        }

        $listObj  =  new MibboList();
        $dir  = empty($dir) ?  MIB_PATH_VAR .'forms' : $dir;
        $listObj  = self::_getForm($key, $dir,$listObj);
        if (empty($listObj))
                return null;
        // on l'ajoute au cache
        self::$list[$key] = $listObj;
        return $listObj;
    }

    /**
     * @param $formKey
     * @param $dir
     * @param null $obj
     * @return MibboForm|MibboList
     */
    private static function _getForm($formKey, $dir,$obj = null )
    {
        // on va chercher le fichier json correspondant à la clé
        $filePath = $dir.DIRECTORY_SEPARATOR.$formKey.".json";
        if (!file_exists($filePath))
            return null;

        $content = file_get_contents($filePath);
        $json = json_decode($content, true);

        if (empty($json['fields']))
            return null;

        // si on trouve ce qu'il faut on crée l'objet MibboForm et on lui renseigne ces valeurs
        $form = ($obj===null) ? new MibboForm() : $obj;
        foreach ($json as $key => $value) {
            if ($key === 'fields') { // pour les champs on les indexe par clé
                foreach ($value as $field) {
                    if (empty($field['key']))
                        continue;
                    $form->fields[$field['key']] = $field;
                }
                continue;
            }
            // sinon on copie la valeur directement sur l'objet
            $form->$key = $value;
        }
        $form->key = empty($form->key)? $formKey : $form->key;

        return $form;
    }

    public static function getFormData(MibboForm $form)
    {
        $datas = null;
        // on récupère les données de la base
        if ($form->getTableName() === 'options' || $form->getTableName()=== 'sitepage')
            $datas = self::getDbOptions($form);
        else
            $datas = self::getDbSpecific($form);


        self::getFormFiles($form,$form->id, $datas);
        foreach ($form->fields as $field) {
            if ($field['type'] === 'paragraph') {
                $value = !empty( $datas[$field['key']]) ? str_replace('u0022', '"', $datas[$field['key']]) : '';
                $datas[$field['key']] = $value;
            }
        }
        return $datas;
    }

    public static function deleteFormData(MibboForm $form)
    {
        $datas = null;
        // on récupère les données de la base
        if ($form->getTableName() === 'options')
             return self::deleteDbOptions($form);
        else
            return  self::deleteDbSpecific($form);
    }

    public static function getCountData(MibboAbstractRender $list,$state= [],$filters= [])
    {
        $datas = null;
        // on récupère les données de la base
        if ($list->getTableName() === 'options' || $list->getTableName() === 'sitepage')
            throw  new Exception('la pagination n\'est pas géré sur ces tables' );
        else
            $datas = self::getDbSpecificCount($list,$state,$filters);


        $list->id = null;

        return $datas;
    }

    public static function getListData(MibboAbstractRender $list,$state= [],$filters= [])
    {
        $datas = null;
        // on récupère les données de la base
        if ($list->getTableName() === 'options' || $list->getTableName() === 'sitepage')
            $datas = self::getDbOptionsList($list,$state,$filters);
        else
            $datas = self::getDbSpecificList($list,$state,$filters);

        if(!empty($datas)){
            foreach ($datas as &$row){
                $list->id =  $row['_id'];
                self::getFormFiles($list,$list->id,$row);
            }
        }

        $list->id = null;


        // on supprime les paragraphes
        foreach ($list->fields as $field) {
            if ($field['type'] === 'paragraph') {
                unset($datas[$field['key']]);
            }
        }
        return $datas;
    }

    public static function getFormFiles(MibboAbstractRender $renderer,$id, &$datas)
    {

        // on récupère les images
        if (empty($renderer->fields))
            return;

        foreach ($renderer->fields as $field) {
            if (!in_array($field['type'], ['image', 'file', 'gallery', 'filelist']))
                continue;

            $jsonData = (empty($datas[$field['key'].'_json'])) ? null : $datas[$field['key'].'_json'];

            $datas[$field['key']] = self::getFiles($renderer, $field,$id,$jsonData);
        }
    }

    public static function getFiles(MibboAbstractRender $form, $field,$id,$jsonDatas=null)
    {

        if (empty($field['type']) ||!in_array($field['type'], ['image', 'file', 'gallery', 'filelist']))
            return '';

        $isList = in_array($field['type'], ['gallery', 'filelist']);
        $value = $isList ? [] : '';
        $path = $form->getFileDirectory($field['key'],$id);
        $absPath = self::getRootPath(). $path;

        if (!is_dir($absPath)) {
            return $value;
        }

        if(!empty($jsonDatas)){

            if(!$isList){
                $jsonDatas = [$jsonDatas];
            }
            foreach ($jsonDatas as $file){
                $filename = $file['name'];

                if(!file_exists($absPath.DIRECTORY_SEPARATOR.$filename)){
                    continue;
                }
                $uniqId = !empty($file['uniqId']) ? $file['uniqId'] : '';

                $file['path'] = self::getUrlRootPath().str_replace('\\','/',$path). '/' . $filename.'?v='.uniqid();
                $file['deleteUrl'] = $form->url . '/deleteFile/' . $field['key'] . '?filename=' . $filename . "&id={$form->id}&lang={$form->lang}&uniqId={$uniqId}";
                $file['actionUrl'] = $form->url . '/{action}/' . $field['key'] . '?filename=' . $filename . "&id={$form->id}&lang={$form->lang}&uniqId={$uniqId}";
                if ($isList) {
                    $value[] = $file;
                } else {
                    $value = $file;
                    break;
                }
            }
        }else{
            $d = dir($absPath);
            while (false !== ($filename = $d->read())) {
                if ($filename == '.' || $filename == '..')
                    continue;
                $f =  self::GetFileReturn($form, $field['key'],$field['type'], $filename, null,null,$id);
                if ($isList) {
                    $value[] = $f;
                } else {
                    $value = $f;
                    break;
                }
            }
            $d->close();
        }
        return $value;
    }

    public static function formIsSubmited(MibboForm $form)
    {
        if (empty($form) || empty($form->key)) {
            return false;
        }

        if (self::getFormFileSubmited($form) !== false) {
            return true;
        }

        // on vérifie que ce formulaure a été effectivement posté
        return ($_SERVER['REQUEST_METHOD'] === 'POST' && (!empty($_POST['_formkey']) && $_POST['_formkey'] === $form->key));
    }

    public static function handleForm(MibboForm $form)
    {
        if (!self::formIsSubmited($form))
            return false;

        if (!empty($_GET)) {
            if (!empty($_GET['id']) && intval($_GET['id']) !== 0) {
                $form->id = intval($_GET['id']);
            }
            if (!empty($_GET['lang']) && strlen($_GET['lang']) == 2) {
                $form->lang = $_GET['lang'];
            }
        }
        if (!empty($_POST)) {
            $lang = (!empty($_POST['_lang'])) ? $_POST['_lang'] : null;
            $id = (!empty($_POST['_id'])) ? $_POST['_id'] : null;
            if (intval($id) !== 0)
                $form->id = $id;
            if (strlen($lang === 2))
                $lang->lang = $lang;
        }

        // on test les fichiers
        $fielKey = self::getFormFileSubmited($form);
        if(!empty($fielKey['action'])){
            $datas = $_GET;

            if ( $fielKey['action'] == 'addFile')
                return self::handleAddFile($form, $fielKey['fieldKey'],$datas);
            if ($fielKey['action'] == 'deleteFile')
                return self::handleRemoveFile($form, $fielKey['fieldKey']);
            if ($fielKey['action'] == 'editFile')
                return self::handleEditFile($form, $fielKey['fieldKey'],$datas);
            if ($fielKey['action'] == 'sortFile'){
                $orders =!empty($_POST['order']) ? json_decode($_POST['order'],true) : [];
                return self::handleSortFile($form, $fielKey['fieldKey'],$orders);
            }

        }


        $datas = $_POST['form'];

        $valid = self::validateDatas($form, $datas);
        if ($valid !== true) // si valid n'est pas true il contient un tableau des erreurs
            return $valid;

        $ok = self::saveFormData($form, $datas);

        if ($ok === false) {

            return array(
                'title' => __('Erreur'),
                'value' => __('La Sauvegarde n\'a  pas été effectuée. : '),
                'options' => array('type' => 'error')

            );
        }
        $form->datas = $datas;
        return array(
            'title' => __('Sauvegarde effectuée'),
            'value' => __('La Sauvegarde a été effectuée avec succès. : '),
            'options' => array('type' => 'valid'),
            'callBack' => 'forms.' . $form->domId . '.saveDataCallBack',
            'id'=> $form->id
        );
    }

    private static function getFormFileSubmited(MibboForm $form)
    {
        $fieldKey = null;
        $url = $_SERVER['REQUEST_URI'];
        $action = '';
        foreach ($form->fields as $field) {
            if (in_array($field['type'], ['image', 'file', 'gallery', 'filelist'])) {
                $uploadUrl =  $form->url . '/'.$form->id.'/addFile/' . $field['key'] . '?';
                if (strpos($url, $uploadUrl) !== false) {
                    $fieldKey = $field['key'];
                    $action = 'addFile';
                    break;
                }

                $urlRegex = '/'.str_replace('/','\\/',$form->url).'\/([a-zA-Z]*)\/' . $field['key'].'\?/m';
                preg_match_all($urlRegex, $url, $matches, PREG_SET_ORDER, 0);

                if(!empty($matches[0][1])){
                    if(in_array($matches[0][1],['deleteFile','editFile','sortFile'])){
                        $action = $matches[0][1];
                        $fieldKey = $field['key'];
                    }
                }

            }
        }

        if (!$fieldKey)
            return false;

        return ['action' => $action, 'fieldKey' => $fieldKey];
    }

    private static function handleFileToJsonChange($form,$fieldDef){
        $filePath = $form->getFileDirectory($fieldDef['key'], $form->id);
        $files = [];
        if(is_dir($filePath)){
            $dir = opendir($filePath);
                while ($existing = readdir($dir)) {
                    if ($existing === "." || $existing === "..")
                        continue;
                    $uniqId  = uniqid($fieldDef['key'], true);
                    $files[] = [
                        'name' => $existing,
                        'type' => $fieldDef['type'],
                        'legend'=>  '' ,
                        'title'=>  '',
                        'uniqId'=>$uniqId
                    ];
                }
                closedir($dir);
        }
        return $files;


    }

    private static function GetFileReturn($form, $key,$type, $newFileName, $uniqId,$datas,$id){
        $filePath = $form->getFileDirectory($key, $id, false);
        return [
            'path' => str_replace('\\','/',self::getUrlRootPath() . $filePath . DIRECTORY_SEPARATOR . $newFileName.'?v='.uniqid()),
            'name' => $newFileName,
            'type' => $type,
            'deleteUrl' =>$form->url . '/deleteFile/' . $key . '?filename=' . $newFileName . "&id={$form->id}&lang={$form->lang}&uniqId={$uniqId}",
            'actionUrl' =>$form->url . '/{action}/' . $key . '?filename=' . $newFileName . "&id={$form->id}&lang={$form->lang}&uniqId={$uniqId}",
            'legend'=> empty($datas['legend']) ? '' : $datas['legend'],
            'title'=> empty($datas['title']) ? '' : $datas['title'],
            'uniqId' => $uniqId
        ];
    }

    public static function handleAddFile(MibboForm $form, $key,$datas)
    {
        $fieldDef = $form->fields[$key];
        if (empty($fieldDef) || empty($_FILES))
            return false;

        $isList = in_array($fieldDef['type'], ['gallery', 'filelist']);
        if($isList){
            // pour les cas de première fois quand on ajoute  des datas dans une gallerie
            // qui n'a pas d'information JSON
            $existing = self::getFormData($form);
            if(empty($existing[$key.'_json'])){
                $jsonDatas =  self::handleFileToJsonChange($form,$fieldDef);

            }else {
                $jsonDatas = $existing[$key.'_json'];
            }
        }

        $files = [];
        $jsonDatas = empty($jsonDatas) ? [] :  $jsonDatas ;

        foreach ($_FILES as $uploadFile) {
            if ($uploadFile['error'] !== 0){
                switch ($uploadFile['error']){
                    case 1:
                    case 2:
                        $message = "le fichier est trop volumineux";
                        break ;
                    case 8:
                        $message = "l'extension n'est pas valide";
                        break ;
                    default:
                        $message = "Erreur du chargement du fichier";
                        break ;
                }
                return array(
                    'title' => __('Fichier non ajouté'),
                    'fieldKey' => $key,
                    'filesUploaded' => $files,
                    'value' => __($message),
                    'options' => array('type' => 'error')
                );
            }

            $filePath = $form->getFileDirectory($fieldDef['key'], $form->id, true);
            $infos = pathinfo($uploadFile['name']);
            $filename = mib_strtourl($infos['filename']) . '.' . $infos['extension'];

            $realPath = self::getRootPath(). $filePath;
            // si on est en champ unique on supprime les éléments précédents
            $dir = opendir($realPath);
            if (!$isList) {
                while ($existing = readdir($dir)) {
                    if ($existing === "." || $existing === "..")
                        continue;
                    unlink($realPath . DIRECTORY_SEPARATOR . $existing);
                }
                closedir($dir);
            }

            // on déplace le fichier
            $ok = move_uploaded_file($uploadFile['tmp_name'], $realPath . DIRECTORY_SEPARATOR . $filename);

            if ($ok) {
                $width = null;
                $height = null;
                $max = true;
                $newFileName = $filename;
                if(in_array($fieldDef['type'],['image','gallery'])) { // resize pour les images
                    if (!empty($fieldDef['WidthMax']) || !empty($fieldDef['HeightMax'])) {
                        $width = !empty($fieldDef['WidthMax']) ? floatval($fieldDef['WidthMax']) : null;
                        $height = !empty($fieldDef['HeightMax']) ? floatval($fieldDef['HeightMax']) : null;
                        $max = true;
                    }
                    if (!empty($fieldDef['Width']) || !empty($fieldDef['Height'])) {
                        $width = !empty($fieldDef['Width']) ? floatval($fieldDef['Width']) : null;
                        $height = !empty($fieldDef['Height']) ? floatval($fieldDef['Height']) : null;
                        $max = false;
                    }
                    $newFileName = self::resizeImage($realPath . DIRECTORY_SEPARATOR, $filename, $width, $height, $max);
                }
                $uniqId  = uniqid($key, true);
                $files[] =  self::GetFileReturn($form, $key,$fieldDef['type'], $newFileName, $uniqId,$datas,$form->id);

//                $fileData['legend'] = $legend ? htmlentities($legend,ENT_COMPAT | ENT_HTML401,"utf-8",false):'';
//                $fileData['title'] = $title? htmlentities($title,ENT_COMPAT | ENT_HTML401,"utf-8",false):'';
                // on s'occupe des JSON DATAS
                if($isList){
                    $jsonDatas[] = [
                        'name' => $newFileName,
                        'type' => $fieldDef['type'],
                        'legend'=> empty($datas['legend']) ? '' : htmlentities($datas['legend'],ENT_COMPAT | ENT_HTML401,"utf-8",false),
                        'title'=> empty($datas['title']) ? '' : htmlentities($datas['title'],ENT_COMPAT | ENT_HTML401,"utf-8",false),
                        'uniqId' => $uniqId
                    ];
                }
                else {
                    $jsonDatas = [
                        'name' => $newFileName,
                        'type' => $fieldDef['type'],
                        'legend'=> empty($datas['legend']) ? '' : htmlentities($datas['legend'],ENT_COMPAT | ENT_HTML401,"utf-8",false),
                        'title'=> empty($datas['title']) ? '' : htmlentities($datas['title'],ENT_COMPAT | ENT_HTML401,"utf-8",false),
                        'uniqId' => $uniqId
                    ];
                }
            }
            if(!$isList){ // si ce n'ets pas une liste on ne traite qu'un seul fichier
                break;
            }
        }

        $fileDatas = [$key.'_json'=>$jsonDatas];
        self::saveFormData($form,$fileDatas);

        // on met à jour le JSON
        return array(
            'title' => __('Fichier ajouté'),
            'callBack' => 'forms.' . $form->domId . '.addFileCallBack',
            'fieldKey' => $key,
            'filesUploaded' => $files,
            'value' => __('La Fichier à bien été ajouté  '),
            'options' => array('type' => 'valid')
        );
    }

    public static function handleSortFile(MibboForm $form, $key, $order)
    {
        $fieldDef = $form->fields[$key];
        if (empty($fieldDef) )
            return false;

        $isList = in_array($fieldDef['type'], ['gallery', 'filelist']);
        if(!$isList)
            return ;

        $existing = self::getFormData($form);
        $jsonDatas = null;
        if(!empty($existing[$key.'_json'])){
            $jsonDatas = $existing[$key.'_json'];
        }

        if($isList && empty($jsonDatas)){
            if(empty($existing[$key.'_json'])){
                $jsonDatas =  self::handleFileToJsonChange($form,$fieldDef);
            }
        }


        $jsonDatas = empty($jsonDatas) ? null :  $jsonDatas ;
        $newJsonDatas = [];
        $path = $form->getFileDirectory($key,$form->id);
        $absPath = self::getRootPath(). $path;
        foreach ($order as $item ){
            $uniqId = $item['uniqId'];
            $filename = $item['filename'];
            if(!empty($uniqId)){
                $findImage = function($img) use ($uniqId){
                    return $img['uniqId']===$uniqId;
                };
            }else {
                $findImage = function($img) use ($filename){
                    return $img['name']===$filename;
                };
            }
            $itemData = array_filter($jsonDatas,$findImage);
            if(!empty($itemData)){
                $itemData = current($itemData);
                if(!file_exists($absPath.DIRECTORY_SEPARATOR.$itemData['name'])){
                    continue;
                }
                $newJsonDatas[] = $itemData;
            }
        }

        $fileDatas = [$key.'_json'=>$newJsonDatas];
        self::saveFormData($form,$fileDatas);


        // on met à jour le JSON
        return array(
            'title' => __('Fichier trié'),
            'fieldKey' => $key,
            'newDatas' => $newJsonDatas,
            'value' => __('Le Fichier à bien été trié  '),
            'options' => array('type' => 'valid')
        );
    }

    public static function handleEditFile(MibboForm $form, $key,$datas)
    {
        $fieldDef = $form->fields[$key];
        if (empty($fieldDef) )
            return false;

        $isList = in_array($fieldDef['type'], ['gallery', 'filelist']);
        $existing = self::getFormData($form);
        $jsonDatas = null;
        if(!empty($existing[$key.'_json'])){
            $jsonDatas = $existing[$key.'_json'];
        }

        if($isList && empty($jsonDatas)){
            if(empty($existing[$key.'_json'])){
                $jsonDatas =  self::handleFileToJsonChange($form,$fieldDef);
            }
        }

        $filename = $datas['filename'];
        $uniqId = $datas['uniqId'];
        $id = $datas['id'];



        $jsonDatas = empty($jsonDatas) ? null :  $jsonDatas ;


        $path = $form->getFileDirectory($key,$id);
        $absPath = self::getRootPath(). $path;

        if(!file_exists($absPath.DIRECTORY_SEPARATOR.$filename)){
            return array(
                'title' => __('Erreur'),
                'value' => __('Erreur  '),
                'options' => array('type' => 'error')
            );
        }

        $legend= $datas['legend'];
        $title= $datas['title'];
        if($isList){
            foreach ($jsonDatas as &$f){
                if($uniqId) {
                    if( $f['uniqId']===$uniqId){
                        $fileData = &$f;
                    }
                }else {
                    if( $f['name']===$filename){
                        $fileData = &$f;
                    }
                }
            }
        }else {
            $fileData= &$jsonDatas;
        }
        $fileData['name'] = empty($fileData['name'])? $filename : $fileData['name'];
        $fileData['uniqId'] = !empty($fileData['uniqId']) ? $fileData['uniqId'] : uniqId($key,true);
        $fileData['legend'] = $legend ? htmlentities($legend,ENT_COMPAT | ENT_HTML401,"utf-8",false):'';
        $fileData['title'] = $title? htmlentities($title,ENT_COMPAT | ENT_HTML401,"utf-8",false):'';
        $fileDatas = [$key.'_json'=>$jsonDatas];
        self::saveFormData($form,$fileDatas);



        $files = self::GetFileReturn($form,$key,$fieldDef['type'],$filename,$uniqId,$fileData,$form->id);
        // on met à jour le JSON
        return array(
            'title' => __('Fichier ajouté'),
            'fieldKey' => $key,
            'filesUploaded' => [$files],
            'value' => __('Le Fichier à bien été modifié  '),
            'options' => array('type' => 'valid')
        );
    }

    public static function handleRemoveFile(MibboForm $form, $key)
    {
        $fieldDef = $form->fields[$key];
        if (empty($fieldDef) || empty($_GET['filename']))
            return false;

        $filename = $_GET['filename'];


        $isList = in_array($fieldDef['type'], ['gallery', 'filelist']);
        $jsonDatas = null;
        if($isList){
            // pour les cas de première fois quand on ajoute  des datas dans une gallerie
            // qui n'a pas d'information JSON
            $existing = self::getFormData($form);
            if(!empty($existing[$key.'_json'])){
                $uniqId = $_GET['uniqId'];
                if(!empty($uniqId)){
                    $findImage = function($img) use ($uniqId){
                        return $img['uniqId']!==$uniqId;
                    };
                }else {
                    $findImage = function($img) use ($filename){
                        return $img['filename']!==$filename;
                    };
                }
                $jsonDatas = array_filter($existing[$key.'_json'],$findImage);
                $jsonDatas = array_values($jsonDatas);

            }
        }

        $fileDatas = [$key.'_json'=>$jsonDatas];
        self::saveFormData($form,$fileDatas);

        $filePath = $form->getFileDirectory($key,$form->id);
        $filePath = self::getRootPath() . $filePath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }


        // on met à jour le JSON
        return array(
            'title' => 'Fichier Supprimé',
            'callBack' => 'forms.' . $form->domId  . '.removeFileCallBack',
            'fieldKey' => $key,
            'fileRemoved' => $filename,
            'value' => 'La Fichier à bien été supprimé  ',
            'options' => array('type' => 'valid')
        );
    }

    public static function validateDatas(MibboForm $form, &$datas)
    {

        // TODO ajouter les validations en focntion du type de champs

        return true;

    }

    public static function saveFormData(MibboForm $form, $formDatas)
    {
        // on définit si on sauve dans la table générique options ou dans une table en particulier,puis on crée la table au besoin


        $tableName = $form->getTableName();
        self::createDbTable($form->getTableName());
        $datas = null;
        if (!empty($formDatas)) {
            if(!empty($form->id)){
                $existing = self::getFormData($form);

                if ($existing) { // s'il existe des données on fusionne les existantes et les nouvelles
                    $datas = array_merge($existing, $formDatas);
                }
            }else {
                $datas = $formDatas;
            }
            // cas particulier
            // on supprime les datas des images  ----
            // pour les check box lists on gère le cas du empty
            foreach ($form->fields as $field) {
                if (in_array($field['type'], ['image', 'file', 'gallery', 'filelist'])) {
                    unset($datas[$field['key']]);
                }

                if (in_array($field['type'], ['choicelist'])) {
                    if (!isset($formDatas[$field['key']])) {
                        $datas[$field['key']] = [];
                    }
                }
                if (in_array($field['type'], ['paragraph'])) {

                    $datas[$field['key']] = stripcslashes($datas[$field['key']]);

                }
                if (in_array($field['type'], ['text'])) {

                    $datas[$field['key']] = htmlentities($datas[$field['key']],ENT_COMPAT | ENT_HTML401,"utf-8",false);

                }
            }

            if ($tableName === 'options' || $tableName === 'sitepage' )
                return self::saveDbOptions($form, $datas);
            else
                return self::saveDbSpecific($form, $datas);
        }
        return false;
    }

    /** Méthode d'écritures  SQL  --------------------------------------- */

    private static function createDbTable($table)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;
        $tableName = "{$MIB_DB->prefix}$table";
        if ($table === 'options' || $table === 'sitepage') {
            $sql =
                "CREATE TABLE  IF NOT  EXISTS $tableName (
                   `autoid` INT(12) not null  AUTO_INCREMENT PRIMARY KEY
                 , `key` VARCHAR(200) not null 
                 , `id` VARCHAR(200) 
                 , `lang` VARCHAR(6) 
                 , `value` JSON  DEFAULT NULL )";

        } else {
            $sql =
                "CREATE TABLE  IF NOT  EXISTS $tableName (
                   `id` INT(12) not null  AUTO_INCREMENT PRIMARY KEY
                 , `value` JSON  DEFAULT NULL )";
        }
        $MIB_DB->query($sql);
        // on crée la table en function de la clé du formulaire

    }

    private static function saveDbOptions(MibboForm $form, $datas)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $action = (!empty($datas['_autoid'])) ? 'update' : 'insert';
        unset($datas['_autoid']);
        unset($datas['_lang']);
        unset($datas['_id']);
        $json = json_encode($datas, JSON_HEX_QUOT|JSON_HEX_APOS);

        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        if ($action == 'update') { // UPDATE
            $langCrit = (empty($form->lang)) ? ' `lang` is null ' : " `lang` = '{$form->lang}'";
            $idCrit = (empty($form->id)) ? ' `id` is null ' : " `id` = '{$form->id}'";
            $query = "UPDATE  $tableName  SET  `value` = '{$json}'  WHERE `key` = '{$form->key}' AND  {$langCrit} AND {$idCrit}";
           $ok =  $MIB_DB->query($query);

        } else { // INSERT
            $langval = (empty($form->lang)) ? 'null' : "'{$form->lang}'";
            $idVal = (empty($form->id)) ? 'null' : "'{$form->id}'";
            $query = "INSERT INTO   $tableName (`key`,`id`,`lang`,`value` ) VALUES ('{$form->key}',$idVal,$langval,'$json' )";

            $ok = $MIB_DB->query($query);

        }
        if($ok === false ){
            return false;
        }
        return true;
    }

    private static function deleteDbOptions(MibboAbstractRender $form)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        $langCrit = (empty($form->lang)) ? ' `lang` is null ' : " `lang` = '{$form->lang}'";
        $idCrit = (empty($form->id)) ? ' `id` is null ' : " `id` = '{$form->id}'";
        $query = "DELETE from  $tableName  WHERE `key` = '{$form->key}' AND  {$langCrit} AND {$idCrit}   ";
        $MIB_DB->query($query);
        return true ;

    }

    private static function getDbOptions(MibboAbstractRender $form)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        $langCrit = (empty($form->lang)) ? ' `lang` is null ' : " `lang` = '{$form->lang}'";
        $idCrit = (empty($form->id)) ? ' `id` is null ' : " `id` = '{$form->id}'";
        $query = "SELECT * from  $tableName  WHERE `key` = '{$form->key}' AND  {$langCrit} AND {$idCrit}  LIMIT 1 ";
        $result = $MIB_DB->query($query);
        if (empty($result) || $result->num_rows == 0){
            $value =[];
            $value['_lang'] = $form->lang;
            $value['_id'] = $form->id;
            return $value;
        }

        $row = $MIB_DB->fetch_assoc($result);
        $value = json_decode($row['value'], true, 512);
        $value['_autoid'] = $row['autoid'];
        $value['_lang'] = $form->lang;
        $value['_id'] = $form->id;
        return $value;

    }

    private static function getPageAssociation()
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}options";

        $query = "SELECT * from  $tableName  WHERE `key` = 'pageAssociation'  LIMIT 1 ";
        $result = $MIB_DB->query($query);
        if (empty($result) || $result->num_rows == 0){
            $value =[];
            return $value;
        }
        $row = $MIB_DB->fetch_assoc($result);
        $value = ['association'=>json_decode($row['value'], true, 512)];
        $value['_autoid'] = $row['autoid'];
        return $value;

    }

    private static function savePageAssociation($datas)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;



        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;



        $action = (!empty($datas['_autoid'])) ? 'update' : 'insert';
        unset($datas['_autoid']);

        $jsondatas  = $datas['association'];

        $json = json_encode($jsondatas, JSON_HEX_QUOT|JSON_HEX_APOS);

        $tableName = "{$MIB_DB->prefix}options";

        if ($action == 'update') { // UPDATE

            $query = "UPDATE  $tableName  SET  `value` = '{$json}'  WHERE `key` = 'pageAssociation' ";
            $ok =  $MIB_DB->query($query);

        } else { // INSERT
            $query = "INSERT INTO   $tableName (`key`,`id`,`lang`,`value` ) VALUES ('pageAssociation',null,null,'$json' )";
            $ok = $MIB_DB->query($query);

        }
        if($ok === false ){
            return false;
        }
        return true;

    }

    private static function saveDbSpecific(MibboForm $form, $datas)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $id = $form->id;
        unset($datas['_id']);
        unset($datas['_lang']);
        $json = json_encode($datas,JSON_HEX_QUOT);
      //  $json = json_encode($datas);

        $json = str_replace("'","''",$json);
        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        if (!empty($id)) { // UPDATE
            $query = "UPDATE $tableName SET  `value` = '{$json}'  WHERE `id` = $id";
            $ok = $MIB_DB->query($query);
            if (!$ok)
                return false;
        } else { // INSERT
            $query = "INSERT INTO  $tableName (`value` ) VALUES ('$json' )";
            $ok = $MIB_DB->query($query);
            if (!$ok)
                return false;
            $id = $MIB_DB->insert_id();
            $form->loadData($id);
            $form->domId = MibboFormManager::processId($form->key . '_');


        }
        return true;
    }

    private static function getDbOptionsList(MibboAbstractRender $form,$state = [])
    {
        throw  new Exception("Not implemented");
    }

    private  static function getDbSpecificCount(MibboAbstractRender $obj,$state = [],$filters=[])
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;
        $query = self::createSqlForSpecific($obj,$state,$filters,true);
        $result = $MIB_DB->query($query);
        $rows = $MIB_DB->fetch_assoc($result);
        return empty($rows['count']) ? 0 : intval($rows['count']);
    }

    private  static function getDbSpecificList(MibboAbstractRender $obj,$state = [],$filters=[])
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $query = self::createSqlForSpecific($obj,$state,$filters,false);
        $result = $MIB_DB->query($query);
        $rows = [];
        while( ($row = $MIB_DB->fetch_assoc($result))){
            $value = json_decode($row['value'], true, 512, JSON_HEX_QUOT);
            $value['_id'] = $row['id'];
            $rows[] =  $value;
        }
        return $rows;
    }

    private static function  createSqlForSpecific(MibboAbstractRender $obj,$state = [],$filters=[],$forCount){
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}{$obj->getTableName()}";
        $select = !$forCount ? '*' : 'count(*) as count';

        $query = "SELECT $select FROM  $tableName WHERE 1= 1 ";

        if(!empty($filters)){

            $searchFilters = [];


            foreach ($filters as $filter){
                if($filter['field']==='keyWordSearch' ||  $filter['field']==='categorySearch'){
                    $searchFilters[]= $filter;
                    continue ;
                }
                $operator = empty($filter['operator']) ? "=" : $filter['operator'];
                $query .=" AND   JSON_EXTRACT(value,  '$.{$filter['field']}') {$operator}  '{$filter['value']}' ";
            }

            if(!empty($searchFilters)){
                $orx = [];
                foreach ($searchFilters as $sFilter ){
                    if($sFilter['field']==='keyWordSearch'){
                        $keyWords = $sFilter;
                        $search = htmlentities($keyWords['search']);
                        // $search = $keyWords['search'];

                        foreach($keyWords['fields'] as $f){
                            $orx[]= " LOWER(JSON_EXTRACT(value,  '$.{$f}')) like LOWER(JSON_QUOTE('%{$search}%')) ";
                        }
                    }

                    if($sFilter['field']==='categorySearch' && !empty($sFilter['ids'])){
                        foreach ($sFilter['ids'] as $id){
                            $orx[] = "  JSON_EXTRACT(value,  '$.{$sFilter['key']}') ='$id' " ;
                        }
                    }
                }
                if(!empty($orx)){
                    $or = join(' OR ', $orx);
                    $query.= "AND ({$or})";
                }

            }


        }




        if(!empty($state['sort_by'])){
            if($state['sort_by']==='_id'){
                $dir =!empty($state['sort_dir']) &&  $state['sort_dir']==='desc' ? 'desc' : 'asc';
                $query .= " order by id $dir ";
            }else {
                $dir = $state['sort_dir']==='desc' ? 'desc' : 'asc';
                $query .= " order by JSON_EXTRACT(value, '$.".$state['sort_by']."') $dir ";
            }


        }

        if(!$forCount && !empty($state['take'])){

            $query .= " LIMIT  {$state['take']}";
        }
        if(!$forCount && !empty($state['start'])){

            $query .= " OFFSET  {$state['start']}";
        }
       return $query;
    }

    private static function getDbSpecific(MibboForm $form)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        $query = "SELECT * from  $tableName  WHERE `id` = $form->id  LIMIT 1 ";
        $result = $MIB_DB->query($query);
        if (empty($result) || $result->num_rows == 0)
            return null;

        $row = $MIB_DB->fetch_assoc($result);
        $value = json_decode($row['value'], true, 512, JSON_HEX_QUOT);
       // $value = json_decode($row['value'], true, 512);
        $value['_id'] = $form->id;
        return $value;
    }

    private static function deleteDbSpecific(MibboForm $form)
    {
        /**  @var MIB_DbLayerX $MIB_DB */
        global $MIB_DB;

        $tableName = "{$MIB_DB->prefix}{$form->getTableName()}";

        $query = "DELETE from  $tableName  WHERE `id` = $form->id   ";
        $MIB_DB->query($query);
        return true;

    }

    /** Tools  --------------------------------------- */

    private static function resizeImage($filePath, $fileName, $width, $height, $max)
    {

        $absmaxWidth = 2000;
        $type = mime_content_type($filePath . $fileName);
        $ext = substr($type, 6);
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                $source = imagecreatefromjpeg($filePath . $fileName);
                break;
            case 'gif':
                $source = imagecreatefromgif($filePath . $fileName);
                break;
            case 'png':
                $source = imagecreatefrompng($filePath . $fileName);
                break;
            default:
                return $fileName;
        }

        if (empty($source)) {
            return $fileName;
        }

        $newFilename = str_replace('.' . $ext, '.jpg', $fileName);
        $centerX = 0;
        $centerY = 0;
        // traitement de la taille
        list($actualWidth, $actualHeight) = getimagesize($filePath . $fileName);
        $newHeight = $actualHeight;
        $newWidth = $actualWidth;
        if ($width != null && $height != null) {
            $width = min($absmaxWidth, $width);
            $ratioWidth =  $actualWidth / $width;
            $ratioHeight = $actualHeight / $height;

            if ($max != true) { // on force le redimmensionnement
                $ratio = max($ratioWidth, $ratioHeight);
                $newHeight = $height;
                $newWidth = $width;
                // gestion de la déformation
                // le plug gros ration est la données qui n'est pas modifié
                if ($ratioWidth === $ratio) { // on change la  la hauteur
                    $newCalculatedHeight =  $actualHeight * $ratioWidth / $ratioHeight;
                    $diff = $newCalculatedHeight -  $actualHeight   ;
                    $centerX =$diff / 2;
                } else {
                    $newCalculatedWidth =  $actualWidth * $ratioHeight / $ratioWidth;
                    // $newCalculatedHeight =$actualHeight + ( $actualHeight * (1-  $ratioResize) ) ;
                    $diff = $newCalculatedWidth -  $actualWidth   ;
                    $centerY = $diff / 2;
                }
            } else {
                $ratio = min($ratioWidth, $ratioHeight);
                $newHeight = $ratio * $actualHeight;
                $newWidth = $ratio * $actualWidth;
                $newHeight = min($newHeight, $actualHeight);
                $newWidth = min($newWidth, $actualWidth);
            }
        } else {
            // si pas de correction demandé on fige tout de même la width max à $absmaxWidth
            if ($actualWidth > $absmaxWidth) {
                $newWidth = $absmaxWidth;
                $newHeight = $height * ($absmaxWidth / $actualWidth);
            }
        }

        try {
            // redimensionnement de l'image
            $target = imagecreatetruecolor($newWidth, $newHeight);

            //imagecopyresampled($target, $source, 0, 0, $centerX, $centerY, $newWidth, $newHeight,$actualWidth, $actualHeight);

            imagecopyresized($target, $source, 0, 0, $centerX, $centerY,  $newWidth, $newHeight, $actualWidth - $centerX, $actualHeight-$centerY);
            imagejpeg($target, $filePath . $newFilename, 85);
            imagedestroy($source);

            // si l'extension à changée on supprime l'ancienne image
            if($newFilename!==$fileName){
                unlink($filePath . $fileName);
            }
        } catch (\Exception $ex) {
            return $fileName;
        }
        return $newFilename;
    }

    /*-- Template de page --------------------------------------*/

    public static function getPageTemplates()
    {
        // on va chercher le fichier json correspondant à la clé



        $filePath = MIB_PATH_VAR . "pages/templates/templates.json";
        if (!file_exists($filePath))
            return null;

        try {
            $content = file_get_contents($filePath);
            $json = json_decode($content, true);

            return $json;
        } catch (Exception $ex) {
            return null;
        }
    }

    public static function getPageTemplate($pageRef)
    {
        $pageRef = self::processId($pageRef);

        $assoc = self::getPageAssociation();
        // on va chercher le fichier json correspondant à la clé
        //$filePath = MIB_PATH_VAR . "pages/templates/association.json";
        if (empty($assoc))
            return null;

        try {

            $associations = empty($assoc['association']) ? [] : $assoc['association'];
            if (is_array($associations)) {
                foreach ($associations as $assoc) {
                    if (isset($assoc['page']) && $assoc['page'] == $pageRef) {
                        return empty($assoc['key']) ? null : $assoc;
                    }
                }
            }
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }

    public static function getPageForm($currentTpl,$pageRef,$lang)
    {
        $pageRef = self::processId($pageRef);
        $dir  = MIB_PATH_VAR .'pages/templates';
        $form =  self::_getForm($currentTpl,$dir);
        if($form===null)
            return null;
        $form->table = 'sitepage';
        $form->lang = $lang;
        $form->loadData($pageRef,$lang);
        return $form;
    }

    public static function getPageContent($form)
    {

        $filePath = MIB_PATH_VAR . "pages/templates/{$form->key}.php";
        if (!file_exists($filePath))
            return null;

        ob_start();
        include $filePath;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public static function removePage(string $pageKey)
    {
        $assocs = self::getPageAssociation();
        if(empty($assocs['association']))
            return ;
        $removeIndex = -1 ;
        foreach ($assocs['association'] as $index => $assoc){
            if($assoc['page']===$pageKey){
                $removeIndex = $index;
                break ;
            }
        }
        if($removeIndex!==-1){
            unset($assocs['association'][$removeIndex]);
            $associationPage = array_values($assocs['association']);
            $assocs['association'] = $associationPage;
           self::savePageAssociation($assocs);
        }

    }

    public static function changePageTemplate($pageRef, $val,$handler='page')
    {
        $pageRef = self::processId($pageRef);


        try {
            $foundIndex = false ;
            $assocPage = self::getPageAssociation();
            $associations = empty($assocPage['association'])?[] :  $assocPage['association'];
            if (is_array($associations)) {
                foreach ($associations as $index  => $assoc) {
                    if (isset($assoc['page']) && $assoc['page'] == $pageRef) {
                        $foundIndex  = $index;
                    }
                }
            }
            $change = false ;
            if(empty($val) && $foundIndex!==false){
                unset($associations[$foundIndex]);
                $associations = array_values($associations);
                $change = true ;
            }

            if(!empty($val)){

                if($foundIndex!==false ){
                    $associations[$foundIndex]['key'] = $val;
                    $associations[$foundIndex]['handler'] = $handler;
                    $change = true ;
                }
                if($foundIndex===false ) {
                    $associations[] = ['page'=>$pageRef,'key'=>$val,'handler'=>$handler];
                    $change = true ;
                }
            }

            // on réécrit le fichier asssociation
            if($change==true){

                $assocPage['association'] = $associations;
                self::savePageAssociation($assocPage);
            }
        } catch (Exception $ex) {
            return false;
        }
        return true ;

    }

    public  static function processId($id){
        $id = str_replace('/','_',$id);
        $id = str_replace('-','_',$id);
        return $id;
    }

    public static function getRootPath(){
        $root = $_SERVER['DOCUMENT_ROOT'] ;
        $index = strrpos($_SERVER['PHP_SELF'],'/');
        if($index!==false){
            $root.= substr($_SERVER['PHP_SELF'],0,$index);
        }
        return $root. DIRECTORY_SEPARATOR;

    }

    public static function getUrlRootPath(){
        $index = strrpos($_SERVER['PHP_SELF'],'/');
        $url = '';
        if($index!==false){
            $url= substr($_SERVER['PHP_SELF'],0,$index);
        }
        return $url .'/';

    }

    public static function getCssAndJsLInks(){
        /* <script src="'.self::getUrlRootPath().'libs/ckeditor5/12.0.0/ckeditor.js"></script> */
        return  '
            <script src="'.self::getUrlRootPath().'libs/ckeditor/ckeditor.js"></script>
            <link rel="stylesheet" href="'.self::getUrlRootPath().'libs/form/mibbo-form.css?v=20190903">
            <script src="'.self::getUrlRootPath().'libs/form/mibbo-form.js?v=20190903"></script>
        ';
    }

    public static function getPageKey(array $MIB_PAGE)
    {
        if(empty( $MIB_PAGE['url']))
            return '';

        return $MIB_PAGE['lang']."_".str_replace(['/','-'],['_','_'], $MIB_PAGE['url']);
    }



    /* Display -----------------------------*/

    /** retour l'affichage pour un champ  */
    public static function getFieldDisplay(MibboAbstractRender $form,$id, $datas,$fields, $fieldKey){

        $val = empty($datas[$fieldKey]) ? '': $datas[$fieldKey];
        $fieldDef = empty($fields[$fieldKey]) ? '': $fields[$fieldKey];

        if(empty($fieldDef))
            return '';

        switch ($fieldDef['type']){
            case 'select':
            case 'choicelist':
                $value =  self::getSelectDisplay($form, $fieldDef,$val);
                break ;
            case 'image':
            case 'file':

                $file  =  self::getFiles($form,$fieldDef,$id);

                $value = empty($file['path']) ? '' : $file['path'];

                break ;
            case 'gallery':
            case 'filelist':

                $files  =  self::getFiles($form,$fieldDef,$id);
                $value = [];
                foreach ($files as $file){

                    $value[]= $file['path'];
                }

                break ;
            case 'date':
                $date = DateTime::createFromFormat('Y-m-d',$val);
                $value = ($date)  ? $date->format('d/m/Y') : '';
                break ;
            case 'boolean':
                 return  $val && $val!=='false'  ? 'oui' : '';
                break ;
            default:
                $value = $val;

        }

        if($fieldDef['type']==='number' && !empty($value) ){
            switch ($fieldDef['format']){
                case 'int':
                    $value = number_format($value,0,',', ' ');
                    break ;
                case 'money':
                    $value = number_format($value,2,',', ' ') . ' €';
                    break ;
                case 'percent':
                    $value = number_format($value,0,',', ' ') . ' %';
                    break ;
                default:
                    $value = number_format($value,2,',', ' ') ;
                    break;
            }
        }

        return empty($value) ? '': $value;
    }

    /* retour l'affcihage d'un champ choix */
    private static function getSelectDisplay(MibboAbstractRender $form, $fieldDef,$val ){

        if (!in_array($fieldDef['type'], ['select', 'choicelist']))
            return '';
        $isList = $fieldDef['type'] === 'choicelist';
        $value = $isList ? [] : '';
        if(!empty($val)){
            $source  = [];
            if( (empty($fieldDef['source']) || $fieldDef['source']==='dynamic' )){
                if(!empty($form->sources[$fieldDef['key']])){
                    $source = $form->sources[$fieldDef['key']];
                }

            }else {
                $source  =  $fieldDef['source'];
            }

            $value = [];
            if(!empty($source) && is_array($source)){
                foreach ($source as $item){
                    if($isList){
                        if(in_array($item['id'],$val)){
                            $value[] = $item['label'];
                        }
                    }else {
                        if($item['id']==$val){
                            $value = $item['label'];
                            break ;
                        }
                    }
                }
            }
        }
        return $value;
    }




}