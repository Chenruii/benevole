<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or defined('MIB_MANAGE') or exit; // assurons nous que le script n'est pas executé "directement"

ob_end_clean();
mib_headers_no_cache();
header('Content-type: text/html; charset=utf-8'); // envoi du header Content-type au cas ou le serveur est configuré pour envoyer autre chose

phpinfo();

exit;