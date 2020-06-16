<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$accountManager = new AccountPlugin();

$info = $MIB_PAGE['info'];
$info = (empty($_POST['action'])) ? $info : $_POST['action'];

switch ($info){
    case "home":
    case "":
        $accountManager->redirectIfNotLogged();
        require_once MIB_ACCOUNT_HTMLPARTS.'account-home.php';
        return;
    case "login":
        $accountManager->processLogin();
        return;
    case "logout":
        $accountManager->redirectIfNotLogged();
        $accountManager->logout();
        mib_header($accountManager->getLoginPage());
        return;
    case "register":
        $accountManager->processRegister();
        return;
    case "changePassword":
        $accountManager->redirectIfNotLogged();
        $accountManager->processChangePassword();
        mib_header('/'.$MIB_PLUGIN['name']);
        return;
    case "changeEmails":
        $accountManager->redirectIfNotLogged();
        $accountManager->processChangeEmails();
        mib_header('/'.$MIB_PLUGIN['name']);
        return;
    case "deleteAccount":
        $accountManager->redirectIfNotLogged();
        $accountManager->processDeleteAccount();
        $accountManager->logout();
        mib_header($accountManager->getLoginPage());
        return;
    case "sendForgotPassword":
        $info = '';
        $ok = $accountManager->processSendForgotPassword($info);
        return;
    case "forgot-password":
        $accountManager->processForgotPassword();
        return;
}




