<?php
// Assurons nous que le script n'est pas executé "directement"
defined('MIB') or exit;

if(!empty($_POST)) {
    require_once 'contact-send.php';
    return ;
}

require_once 'contact-form.php';

?>


