<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;


$errorsMessage = [
    'lastname' => __('le nom est obligatoire')
    , 'firstname' => __('le prénom est obligatoire')
    , 'subject' => __('le sujet est obligatoire')
    , 'message' => __('le message est obligatoire')
    , 'email' => __(" l'email est obligatoire")
];


$fields = ['lastname', 'firstname', 'function', 'company', 'email', 'tel', 'subject', 'message'];
$errors = [];
$datas = [];
foreach ($fields as $field) {
    $datas[$field] = (!empty($_POST[$field])) ? mib_clean($_POST[$field]) : '';
}

$mandatarories = ['lastname', 'firstname', 'subject', 'message', 'email'];
foreach ($mandatarories as $mandatarory) {
    if (empty($datas[$mandatarory])) {
        $errors[] = $errorsMessage[$mandatarory];
    }
}
if (empty($errors)) {
    /* ----------------------------------------------*/
    $recipient = 'rui.chen1996@gmail.com';
    $fromEmail = 'rui.chen1996@gmail.com';
    /* ----------------------------------------------*/
    $mail_message = '';
    foreach ($fields as $field) {
        if (!empty($datas[$field])) {
            $mail_message .= $field . ' : ' . $datas[$field] . ' <br>';
        }
    }

    foreach ($_FILES as $file) {
        $ferr = mib_core_function('MIB_isUploaded', $file);
        if (empty($ferr)) {

            $info = pathinfo($file['name']);
            $ext = $info['extension'];
            $uniqName = generateRandomFileName();
            $ok = move_uploaded_file($file['tmp_name'], MIB_PUBLIC_DIR . 'contact/' . $uniqName . '.' . $ext);
            if ($ok) {
                $mail_message .= 'file  :  <a href="' . $MIB_CONFIG['base_url'] . '/public/contact/' . $uniqName . '.' . $ext . '">  ' . $file['name'] . '</a>' . ' <br>';
            }
        }
    }

    // on envoie le mail et on redirige vers le formulaire avec l'indication de succès
    $name = $datas['firstname'] . ' ' . $datas['lastname'];
    mib_mail($recipient, $datas['subject'], $mail_message, $fromEmail, $name);
    header('location:' . mib_get_current_url() . '?success=success');
    exit;
}

// affcihage des erreurs.
require_once 'contact-form.php';


//echo $mail_message;

function generateRandomFileName()
{
    return md5(uniqid('file-', true));
}





