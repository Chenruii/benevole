<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 07/06/2018
 * Time: 21:43
 */

/** Représente une instance d'un formulaire  */
class MibboForm extends  MibboAbstractRender
{

    /** @var string url de soumission du formulaire  */
    public $url = '';
    /** @var string nom de la table pour les données ( si vide on crée un table spécifique en utilisant la clé team => table mib_team  )  */
    public $table;
    /** @var array données associé au formulaire  */
    public $datas = [];

    /** @var string identifiant de la balise Form dans le DOM   */
    public $domId;

    public $saveLabel = 'Enregistrer';

    /** Rendu de l'ouverture de la balise Form */
    public function renderFormHeader(){
        $id = empty($this->id) && !empty($this->datas['_id']) ? $this->datas['_id']: $this->id;
        $this->domId = (empty($this->domId)) ? MibboFormManager::processId($this->key . '_'. $id) :$this->domId;
        echo '<form  id="'.$this->domId.'" action="'.$this->url.'" method="post" data-mibboform="'.$this->key.'" target="_json">
                <input type="hidden" name="_formkey" value="'.$this->key.'">';
        if (!empty($id)){
            echo '<input type="hidden" name="_id" value="'.$id.'">';
        }
        if (!empty($lang)){
            echo '<input type="hidden" name="_lang" value="'.$this->lang.'">';
        }
    }

    /** Rendu des balise nécessaire à la génération des champs */
    public function renderFormAllFields(){
        if (!empty($this->fields)){
            foreach (array_keys($this->fields) as $fieldKey){
                $this->renderFormField($fieldKey);
            }
        }
    }

    /** Rendu des balise nécessaire à la génération d'un champ */
    public function renderFormField($fieldKey){
        if (!empty($this->fields[$fieldKey])){
            $field  = $this->fields[$fieldKey];

            if(!empty($field['displayCondition'])){
                $parts = explode(":",$field['displayCondition']);
                if(count($parts)===2){
                    $displayField = $parts[0];
                    $displayVal =   $parts[1];
                    if(!isset($this->fields[$displayField])  ||  !isset($this->datas[$displayField]) || $displayVal!=$this->datas[$displayField]){
                        var_dump('no display' , $displayField,$displayVal );
                       return;
                    }
                }
            }

            $css= !empty($field['validators']['mandatory']) ? ' Form-field--mandatory ' : '';
            $format = !empty($field['format']) ? $field['format'] : '';
            echo '<div class="Form-field '.$css.'" data-field="'.$field['key'].'"  data-type="'.$field['type'].'"   data-format="'.$format.'">'.$field['key'].'</div>';
        }
    }

    /** Rendu de la fermeture  de la balise Form  et des méthodes javascript et des templates nécessaire pour le rendu du formulaire */
    public function renderFormFooter(){
        echo '<div class="Form-field Form-field--submit">
            <button type="submit" class="Form-submit"> '.$this->saveLabel.'</button> 
            <br>
            </div>
            </form>';
            include 'mibbo-form-template.html';
            echo '<script type="text/javascript"> 
                var forms = window.forms ||{};
                forms["'.$this->domId.'"] =   new FormEngine();
                forms["'.$this->domId.'"].sources = '.json_encode($this->sources) .';
                forms["'.$this->domId.'"].init("'.$this->domId.'", "'.$this->key.'",'.json_encode($this).'); 
                forms["'.$this->domId.'"].setValues('.json_encode($this->datas).'); 
                if(typeof window.customizeFormEngine  ==="function"){
                    window.customizeFormEngine.call(forms["'.$this->domId.'"]);
                }
                forms["'.$this->domId.'"].render(); 
            </script>';
    }

    /** Determine si le formulaire à été posté ou non  */
    public function isSubmited(){
        return  MibboFormManager::formIsSubmited($this);
    }

    /** Gére la soumission de la requete ( gestion erreur / sauvegarde )  */
    public function handleRequest(){
        // on vérifie que ce formulaure a été effectivement posté
        return  MibboFormManager::handleForm($this);
    }

    /** charge les données depui la base  (Id non obligatoire uniquement pour la table options )  */
    public function loadData($id=null, $lang=null){

        $id = MibboFormManager::processId($id);
        $this->domId = (empty($this->domId)) ? MibboFormManager::processId($this->key . '_').$id :$this->domId;

        $this->id= $id ;
        $this->lang = $lang;

        if (($this->getTableName()!=='options' || $this->getTableName()!=='sitepage' )&& empty($id)){
            throw new ErrorException("MibboForm ::loadData l'id doit être fourni pour un formulaire non basé sur la table options");
        }
        $this->datas =  MibboFormManager::getFormData($this);
    }

    /** charge les données depui la base  (Id non obligatoire uniquement pour la table options )  */
    public function deleteData($id=null, $lang=null){

        $id = MibboFormManager::processId($id);
        $this->domId = (empty($this->domId)) ? MibboFormManager::processId($this->key . '_').$id :$this->domId;

        $this->id= $id ;
        $this->lang = $lang;

        if ($this->getTableName()!=='options' && empty($id)){
            throw new ErrorException("MibboForm ::loadData l'id doit être fourni pour un formulaire non basé sur la table options");
        }
        $this->datas =  MibboFormManager::deleteFormData($this);
    }

    /** retour l'affichage pour un champ  */
    public function getFieldDisplay($fieldKey,$options=null){
        return $this->getDisplay($this->id,$this->datas,$fieldKey,$options);
    }

    /** retour l'affichage pour un champ  */
    public function getFieldRawDatas($fieldKey){
        $d =empty( $this->datas[$fieldKey])?null: $this->datas[$fieldKey] ;
        return $d;
    }

    /** retour l'affichage pour un champ  */
    public function getFieldDisplayLang($fieldKey,$options=null){
        global $MIB_PAGE;
        $fieldKey = $fieldKey.strtoupper($MIB_PAGE['lang']);
        return $this->getFieldDisplay($fieldKey,$options);

    }

    /** Permet d'ajouter des champs dynamique */
    public function addParts(array $partList)
    {

    }

}