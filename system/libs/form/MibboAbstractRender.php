<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 08/10/2018
 * Time: 14:42
 */

class MibboAbstractRender
{

    /** @var array liste des champs du formulaire  */
    public $fields = null;
    /** @var string  clé du formulaire   */
    public $key = null;
    /** @var array liste des sources pour les champ de type choix configuré en dynamique  */
    public $sources = [];
    /** @var string identifiant de l'enregistrement géré ( null pour les listes )  */
    public $id;
    /** @var string langue gérer par le formaulaire  */
    public $lang;

    public $customRenderers = [];

    /** Retourne le nom de la table dans la base de données à utiliser */
    public function getTableName(){
        return empty($this->table) ? $this->key : $this->table;
    }

    /** Retourne le répertoire dans lequelle les images sont déposées */
    public function getFileDirectory($fieldKey,$id,$createDir=false){


        $rootDir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR ; // MibboFormManager::getUrlRootPath();


        // on determine le répertoire de destination des fichiers
        $directory = !empty($this->directory) ? $this->directory : $this->key;
        $filePath = substr(MIB_PUBLIC_DIR, strlen(MIB_ROOT)) . $directory .DIRECTORY_SEPARATOR;
        $ok = true ;
        if($createDir && !is_dir($rootDir.$filePath)){
            $ok = mkdir($rootDir.$filePath);
        }
        if(!empty($id) || !empty($this->lang)){
            $ok = $filePath .= $id.$this->lang.DIRECTORY_SEPARATOR;
        }
        if($createDir && !is_dir($rootDir.$filePath)){
            $ok =  mkdir($rootDir.$filePath);
        }
        $filePath .=  $fieldKey;
        if($createDir && !is_dir($rootDir.$filePath)){
            $ok =  mkdir($rootDir.$filePath);
        }
        return  $ok  ?  $filePath :false ;
    }



    public function getDisplay($id,$datas,$fieldKey,$options=null){

        $display =  MibboFormManager::getFieldDisplay($this,$id,$datas,$this->fields,$fieldKey);
        if (!empty($this->customRenderers[$fieldKey]) && is_callable($this->customRenderers[$fieldKey])) {
            $display = $this->customRenderers[$fieldKey]($display);
        }

        if(!empty($options)){
            $display = $this->processDisplayOptions($display,$options);
        }
        return $display;
    }


    public function processDisplayOptions($display,$options){
        foreach (array_keys($options) as $opt){
            switch ($opt){
                case 'truncate':
                    if($display ){
                        $val = intval($options['truncate']);
                        if(!empty($val)){
                            if(strlen($display)> $val ){
                                $display = substr($display,0,$val-3) . ' ...';
                            }
                        }
                    }
                    break;
            }
        }
        return $display;
    }



}