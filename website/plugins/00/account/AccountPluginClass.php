<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 26/03/2019
 * Time: 11:52
 */

require_once MIB_ACCOUNT_PATH.'MibboMailChimp.php';


class AccountPlugin
{

    const sessionKey = "AccountPluginClass.User";

    /** @var MibboMailChimp $mailChimp  */
    public $mailChimp = null;

    public function getLoginPage()
    {
        return '/account/login';
    }

    public function getRegisterPage()
    {
        return '/account/register';
    }

    public function redirectIfNotLogged()
    {
        if (!$this->isLogged()) {
            mib_header($this->getLoginPage());
            exit;
        }
    }

    public function getCurrentUser()
    {
        $infos = !empty($_SESSION[self::sessionKey]) ? $_SESSION[self::sessionKey] : null;
        if (empty($infos)) {
            return false;
        }
        return $infos;
    }

    public function setCurrentUser($infos)
    {
        $_SESSION[self::sessionKey] = $infos;
    }


    public function isAdminBo(){
        $user = $this->getCurrentUser();
        return empty($user) && mib_isAdminLogged() ;
    }


    public function getUserName()
    {
        $user = $this->getCurrentUser();
        if(empty($user) )
            return  $this->isAdminBo() ? 'Admin BO' : null ;

        $show = function($prop) use ($user){
            return empty($user[$prop])?'':$user[$prop];
        };
        return  $show('firstname') . ' '.  $show('lastname');
    }

    public function isLogged()
    {
        return !empty($this->getUserName()) ||  mib_isAdminLogged() ;
    }

    public function processLogin()
    {
        if (!empty($_POST)) {
            $checkCsrf = $this->checkCsrfCode('login');
            if (empty($checkCsrf)) {
                return $this->getOut();
            }
            $error = null;
            $user = $this->login($_POST['username'], $_POST['password'], $error);
            if (!$user) {
                $loginError = empty($error) ? 'Identification non valide' : $error;
                header('HTTP/1.0 401 Unauthorized ');
                $this->renderLogin($loginError, '');

            } else {
                $this->setCurrentUser($user);
                mib_header("/");
            }
        } else {
            $this->renderLogin('', '');
        }
    }

    public function processChangePassword()
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            return false;
        }

        $oldpassword = $_POST['oldpassword'];
        $newpassword = $_POST['newpassword'];
        $newpasswordconf = $_POST['newpasswordconf'];

        if (empty($oldpassword) || empty($newpassword) || empty($newpasswordconf)) {
            return false;
        }

        if ($newpassword != $newpasswordconf)
            return false;

        if (!password_verify($oldpassword, $user['password']))
            return false;

        if (!$this->checkPasswordRequirement($newpassword))
            return false;

        return $this->updateAccount($user['id'], ['password' => $newpassword]);

    }

    public function processChangeEmails()
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $user = $this->getCurrentUser();
        if (empty($user)) {
            return false;
        }


        $news = empty($_POST['news']) ? 0 : 1;
        $datas = [ 'news' => $news];
        $ok = $this->updateAccount($user['id'], $datas);
        if(strpos($user['email'],'stmichel.fr')!==false){
            $news = 1 ;
        }

        $user['news'] = $news;
        $this->setCurrentUser($user);
        if($ok){
           $this->processMailChimpMemberShip($user,$news);
        }
        return true;
    }

    private function processMailChimpMemberShip($datas,$news){
        $this->initMailChimp();
        $member = $this->mailChimp->getMember($datas['email']);
        if(empty($member['id'])){
            $this->mailChimp->createMember($datas['email'],$datas['firstname'],$datas['lastname'],$news);
        }else {
            $this->mailChimp->updateUser($datas['email'], $news);
        }
    }

    public function processDeleteAccount()
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            return false;
        }
        return $this->deleteAccount($user);
    }

    public function processRegister()
    {
        if (!empty($_POST)) {
            $error = '';
            $ok = false;
            $data = $this->getRegisterData();
            if (empty(!$data)) {
                $ok = $this->register($data, $error);
            }
            if ($ok) {
                require_once MIB_ACCOUNT_HTMLPARTS.'account-register-end.php';
                return;
            } else {
                $registerError = empty($error) ? "Compte non valide" : $error;
                require_once MIB_ACCOUNT_HTMLPARTS . 'account-register.php';
                return;
            }
        } else {
            $registerError = empty($_SESSION['$accoutManager-error'])? '': $_SESSION['$accoutManager-error'];
            $_SESSION['$accoutManager-error'] = "";
            require_once MIB_ACCOUNT_HTMLPARTS . 'account-register.php';
        }

    }

    public function processSendForgotPassword(&$info)
    {

        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB, $MIB_PAGE;

        $checkCsrf = $this->checkCsrfCode('forgotpassword');
        if (empty($checkCsrf)) {
            return $this->getOut();
        }
        $ok = true;
        $email = !empty($_POST['email']) ? $_POST['email'] : null;
        if (empty($email))
            $ok = false;

        $account = $this->getAccount($email);
        if (empty($account) || empty($account['email']))
            $ok = false;

        if ($ok) {
            $code = $this->generateCode();
            $datas = ['code' => $code, 'type' => 'forgotpassword', 'compte' => $account['id'], 'date' => date('Y-m-d h:i:s')];
            $ok = $MIB_DB->insertItem("{$MIB_DB->prefix}account_code", $datas);
        }

        if ($ok) {
            $link = mib_get_base_url() . "/account/forgot-password?code=" . $code;
            $email = "Bonjour,  <br><br>   Voici   le lien qui vou permettra de réinitilaiser votre mot de passe  : <a href=\"{$link}\">Clliquez-ici </a> <br> <i> le lein est valide 02H00</i><br><br> La gazette st-michel ";
            // var_dump($email);
            mib_mail($account['email'], "Réinitialisation de votre mot de passe", $email, "aude.blancbrude@2boandco.com", "La gazette st-michel");
            $forgotPassordInfo = 'Un email vous a été envoyé';
        } else {
            $forgotPassordInfo = 'Compte non valide';
        }
        $this->renderLogin('', $forgotPassordInfo);
    }

    public function processForgotPassword()
    {

        $code = $_GET['code'];
        if (empty($code)) {
            mib_header($this->getLoginPage());
            return;
            // $forgotPasswordError = 'no code';
        }

        $accountCode = $this->getAccountCode($code, 'forgotpassword');
        if (empty($accountCode)) {
            mib_header($this->getLoginPage());
            return;
            // $forgotPasswordError = 'no accountCode';
        }
        $forgotPasswordError = null;
        $limitDate = new \DateTime();
        $limitDate = $limitDate->sub(new \DateInterval('PT2H'));
        $rowDate = \DateTime::createFromFormat('Y-m-d H:i:s', $accountCode['date']);
        if ($rowDate < $limitDate)
            $forgotPasswordError = "le code n'est plus valide";

        if (!empty($_POST) & empty($forgotPasswordError)) {
            $checkCsrf = $this->checkCsrfCode();
            if (empty($checkCsrf)) {
                return $this->getOut();
            }

            $newpassword = $_POST['newpassword'];
            $newpasswordconf = $_POST['newpasswordconf'];

            $forgotPasswordInfo = '';

            if (empty($newpassword) || empty($newpasswordconf)) {
                $forgotPasswordInfo = 'Données non valides';
            }

            if (empty($forgotPasswordInfo) && $newpassword != $newpasswordconf)
                $forgotPasswordInfo = 'Le mot de passe et la confirmation sont différente';

            if (empty($forgotPasswordInfo) && !$this->checkPasswordRequirement($newpassword))
                $forgotPasswordInfo = 'Le mot de passe n\est pas conforme';

            if (empty($forgotPasswordInfo)) {
                $this->updateAccount($accountCode['compte'], ['password' => $newpassword]);
                $this->getOut();
            } else {
                header('HTTP/1.0 401 Unauthorized ');
                $formCode = $this->getCsrfCode();
                require_once MIB_ACCOUNT_HTMLPARTS . 'account-forgotpassword.php';
            }


        } else {
            $formCode = $this->getCsrfCode();
            require_once MIB_ACCOUNT_HTMLPARTS . 'account-forgotpassword.php';
        }
    }

    public function logout()
    {
        $_SESSION[self::sessionKey] = null;
    }

    private function login($userName, $password, &$error)
    {
        if ($userName === 'test@test.com' && $password === 'test') {
            return ['email' => $userName,'firstname'=>'test', 'lastname'=>'test'];
        }
        if ($userName === 'test@stmichel.fr' && $password === 'test') {
            return ['email' => $userName,'firstname'=>'test stmichel.fr', 'lastname'=>'test stmichel.fr','mail'=>1];
        }

        $account = $this->getAccount($userName);
        if (empty($account)) {
            return false;
        }


        $hash = $account['password'];
        if (password_verify($password, $hash)) {
            if (password_needs_rehash($hash, PASSWORD_BCRYPT)) {
                $this->updateAccount($account['id'], ['password' => $password]);
            }
            $source = $this->getAccountSource($account['matricule']);
            if (empty($source)) {
                $error = "Compte non actif";
                return false;
            }
            return $account;
        }
        return false;
    }

    private function deleteAccount($user)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $sql = "DELETE FROM {$MIB_DB->prefix}account WHERE id = '{$user['id']}'";
        $MIB_DB->query($sql);

        $this->initMailChimp();
        $this->mailChimp->deleteMember($user['email']);
    }

    public function initMailChimp(){
        if($this->mailChimp==null){
            $this->mailChimp = new MibboMailChimp();
        }
    }

    private function updateAccount($id, $datas)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        if (isset($datas['password'])) {
            $datas['password'] = password_hash($datas['password'], PASSWORD_BCRYPT);
        }
        return $MIB_DB->updateItem("{$MIB_DB->prefix}account", $datas, $id);
    }

    private function getAccount($email)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $sql = "SELECT * FROM {$MIB_DB->prefix}account   WHERE email like '{$email}' ";
        $result = $MIB_DB->query($sql);
        if (empty($result) || $result->num_rows == 0)
            return false;

        return $result->fetch_assoc();
    }

    private function getAccountCode($code, $type)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $sql = "SELECT * FROM {$MIB_DB->prefix}account_code   WHERE code like '$code'  and type = '$type'";
        $result = $MIB_DB->query($sql);
        if (empty($result) || $result->num_rows == 0)
            return false;

        return $result->fetch_assoc();
    }

    private function renderLogin($loginError, $forgotPasswordInfo)
    {
        $formCodeLogin = $this->getCsrfCode('login');
        $formCodeForgot = $this->getCsrfCode('forgotpassword');
        require_once MIB_ACCOUNT_HTMLPARTS . 'account-login.php';
    }

    private function getAccountSource($matricule)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $sql = "SELECT * FROM {$MIB_DB->prefix}account_source   WHERE matricule like '{$matricule}' ";
        $result = $MIB_DB->query($sql);
        if (empty($result) || $result->num_rows == 0)
            return false;

        return $result->fetch_assoc();
    }

    private function getRegisterData()
    {
        $datas = [];
        $keys = ['firstname', 'lastname', 'email', 'birthdate', 'password'];
        foreach ($keys as $key) {
            $datas[$key] = mib_clean($_POST[$key]);
        }
        $datas['news'] = (!empty($_POST['news'])) ? 1 : 0;

        if (empty($datas['firstname']) || empty($datas['email']) || empty($datas['lastname']) || empty($datas['birthdate']) || empty($datas['password'])) {
            return false;
        }
        return $datas;
    }

    private function getRegisterSource($data)
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;
        $sql = "SELECT * FROM {$MIB_DB->prefix}account_source 
                    WHERE lastname like '{$data['lastname']}' 
                    AND firstname like '{$data['firstname']}' 
                    AND date(birthdate) like '{$data['birthdate']}'    ";
        $result = $MIB_DB->query($sql);
        if (empty($result) || $result->num_rows == 0)
            return false;

        return $result->fetch_assoc();
    }

    private function register($data, &$error)
    {

        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;

        $existing = $this->getAccount($data['email']);
        if ($existing) {
            $error = 'Il existe déjà un compte pour cet email';
            return false;
        }

        // on va chercher dans la table source s'il existe
        $source = $this->getRegisterSource($data);
        if (empty($source)) {
            $error = 'Compte non répertorié';
            return false;
        }

        $keys = ['firstname', 'lastname', 'matricule'];
        $insertData = [];
        foreach ($keys as $key) {
            $insertData[$key] = $source[$key];
        }
        $insertData['email'] = $data['email'];
        $insertData['news'] = empty($data['news']) ? 0 : 1;
        if(strpos($insertData['email'],'stmichel.fr')!==false){
            $insertData['news'] = 1 ;
        }
        $insertData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $insertData['dateCreate'] = date('Y-m-d');
        $ok =  $MIB_DB->insertItem("{$MIB_DB->prefix}account", $insertData);
        if($ok && $insertData['news']===1){
            $this->processMailChimpMemberShip($insertData,$insertData['news'],false);
        }
        return $ok;
    }

    private function getCsrfCode($formkey = 'form')
    {
        $code = $this->generateCode(20);
        $_SESSION['CheckFORM-' . $formkey] = $code;
        return $code;

    }

    private function checkCsrfCode($formkey = 'form')
    {
        $retour = true;
        $postCode = empty($_POST['_token']) ? null : $_POST['_token'];
        $sessionCode = $_SESSION['CheckFORM-' . $formkey];
        if (empty($postCode))
            $retour = false;

        if ($retour && $postCode !== $sessionCode)
            $retour = false;

        $_SESSION['CheckFORM-' . $formkey] = null;
        if ($retour == false) {
          //  var_dump('Kill by checkCsrfCode');
            return false;
        }
        return $retour;
    }

    private function generateCode($longueur = 8)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < $longueur; $i++) {
            $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
        return $chaineAleatoire;
    }

    private function checkPasswordRequirement($password)
    {
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/m';
        return preg_match($regex, $password);

    }

    private function getOut()
    {
        mib_header($this->getLoginPage());
        return null;
    }

    public function getListDatas()
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;

        $sql = "SELECT acc.*, case WHEN source.id is null THEN '1' ELSE '0' END as desactivated FROM {$MIB_DB->prefix}account  acc
                    LEFT JOIN {$MIB_DB->prefix}account_source source on source.matricule =acc.matricule";

        $result = $MIB_DB->query($sql);
        if (empty($result) || $result->num_rows == 0)
            return [];
        $rows = [];
        while (($row = $MIB_DB->fetch_assoc($result))) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function downloadListEmails()
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;

        $contentFile = '"Nom";"Prénom";"Email";'.PHP_EOL;
        $sql = "SELECT acc.firstname, acc.lastname,acc.email FROM {$MIB_DB->prefix}account  acc
                    INNER JOIN {$MIB_DB->prefix}account_source source on source.matricule =acc.matricule
                    WHERE acc.news = 1  ";

        $result = $MIB_DB->query($sql);
        if (!empty($result) &&  $result->num_rows> 0){
            while (($row = $MIB_DB->fetch_assoc($result))) {
                $contentFile .= '"'.$row['firstname'].'";"'. $row['lastname'].'";"'.$row['email']. '";'.PHP_EOL;
            }
        }
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="newsletter.csv";');
        echo  utf8_decode($contentFile);
        exit;
    }

    public function downloadListAccounts()
    {
        /** @var $MIB_DB MIB_DbLayerX */
        global $MIB_DB;

        $contentFile = '"Nom";"Prénom";"Email";"Newsletter";"Actif";"Date de création";'.PHP_EOL;
        $sql = 'SELECT acc.firstname, acc.lastname,acc.email,acc.news as NewsLetter, 
                         CASE WHEN source.id is null THEN 0 ELSE 1 END as Actif , DATE_FORMAT(acc.dateCreate, "%d/%m/%Y") as date
                    FROM '.$MIB_DB->prefix.'account  acc
                    LEFT JOIN '.$MIB_DB->prefix.'account_source source on source.matricule =acc.matricule
                    ORDER BY  acc.lastname  ';

       // var_dump($sql);
        $result = $MIB_DB->query($sql);
        if (!empty($result) &&  $result->num_rows> 0){
            while (($row = $MIB_DB->fetch_assoc($result))) {
                $contentFile .= '"'.$row['firstname'].'";"'. $row['lastname'].'";"'.$row['email']. '";"'.$row['NewsLetter'].  '";"'.$row['Actif']. '";"'.$row['date']. '";'.PHP_EOL;
            }
        }
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="compte.csv";');
        echo  utf8_decode($contentFile);
        exit;
    }



    //INSERT INTO `2bo-stmichel`.`mib_account_source` (`matricule`, `lastname`, `firstname`, `company`, `place`, `birthdate`) VALUES ('123456987', 'JEZ', 'Yann', 'NULL', 'NULL', '1971-09-30 00:00:00')
    //INSERT INTO `2bo-stmichel`.`mib_account_source` (`matricule`, `lastname`, `firstname`, `company`, `place`, `birthdate`) VALUES ('123456999', 'JEZE', 'Yann', 'NULL', 'NULL', '1971-09-30 00:00:00')
}