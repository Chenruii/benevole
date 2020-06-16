<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 26/03/2019
 * Time: 11:52
 */

class AccountSourcePlugin
{

    public function importAccount(&$error)
    {
        $ok = false;
        try {
            $datas = $this->getDatas();
            if ($datas !== false) {
                $ok = $this->processDatas($datas,$error);
            }else {
                $error = 'Erreur dans le fichier';
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            return false;
        }

        return $ok;
    }

    public function getDatas()
    {

        $file = $_FILES['file'];
        if (empty($file)) {
            return false;
        }


        $f = fopen($file['tmp_name'], 'r');


        if (empty($f))
            return;

        $endDatas = [];
        $first = true;
        $atLeastOne = false;
        while (($data = fgetcsv($f)) !== false) {
            if ($first) {
                $first = false;
                continue;
            }
            $atLeastOne = true;
            if (count($data) !== 6)
                return false;

            if (empty($data[0]))
                return false;

            if (!empty($data[5])) {
                $date = DateTime::createFromFormat('d/m/Y', $data[5]);
                if (empty($date))
                    return false;
            } else {
                $data[5] = null;
            }


            $data[5] = $date->format('Y-m-d');
            for($i=0; $i < 5 ; $i++){
                $data[$i] = str_replace("'","''",$data[$i]);
            }
            $endDatas[] = $data;

        }
        return $atLeastOne ? $endDatas : false;
    }

    public function processDatas($datas,&$error)
    {

        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;


        $MIB_DB->start_transaction();

        $sqlClean = "DELETE FROM  {$MIB_DB->prefix}account_source";
        $sqlInsertSource = "     INSERT INTO  {$MIB_DB->prefix}account_source(matricule,lastname,firstname,company,place,birthdate) VALUES  ";


        $ok = $MIB_DB->query($sqlClean);
        if ($ok) {
            $sqlInsert = $sqlInsertSource;
            foreach ($datas as $index => $data) {

                if ($index>0 && $index % 100 == 0) {
                    $ok = $MIB_DB->query(substr($sqlInsert, 0, -1));
                    $sqlInsert = $sqlInsertSource;
                }
                $sqlInsert .= "( '{$data[0]}', '{$data[1]}','{$data[2]}','{$data[3]}','{$data[4]}','{$data[5]}') ,";
            }

            if ($sqlInsert !== $sqlInsertSource) {
                $ok = $MIB_DB->query(substr($sqlInsert, 0, -1));
            }

            if(!$ok){
                $error = "Erreur dans l'insertion du fichier";
                return false ;
            }

            // on ajoute les employées de la table des récurents
            $liste = MibboFormManager::getList('recurentemployee', MIB_ACCOUNT_PATH);
            $recurentDatas = $liste->loadData();
            if (!empty($recurentDatas)) {
                $sqlInsert = $sqlInsertSource;
                foreach ($recurentDatas as $index => $data) {
                    $lastname = strtoupper($data['lastname']);
                    $firstname = strtoupper($data['firstname']);


                    if ($index > 0 && $index % 100 == 0) {
                        $ok = $MIB_DB->query(substr($sqlInsert, 0, -1));
                        $sqlInsert = $sqlInsertSource;
                    }
                    $sqlInsert .= "('{$data['matricule']}', '{$lastname }','{$firstname}','','','{$data['birthdate']}'),";
                }
                if ($sqlInsert !== $sqlInsertSource) {
                    $ok = $MIB_DB->query(substr($sqlInsert, 0, -1));
                }
            }

            if(!$ok){
                $error = "Erreur dans l'insertion des salariés récurent";
                return false ;
            }


            if ($ok) {
                $MIB_DB->end_transaction();
            }
        }
 
        return $ok !== false;

    }

}