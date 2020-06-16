<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net
	
	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL
	
	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB_ROOT') or exit;

/**
 * Fonction de Dump
 */
function mib_dump()
{
    $num_args = func_num_args();

    for ($i = 0; $i < $num_args; ++$i) {
        echo '<pre>';
        print_r(func_get_arg($i));
        echo '</pre>';
        echo '<script>console.log(' . json_encode(func_get_arg($i)) . ');</script>';
    }
}


/**
 * Envoi des headers (no-cache)
 */
function mib_headers_no_cache()
{
    header('Expires: Sun, 02 Oct 1983 06:30:00 GMT'); // quand tout a commencé ;)
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // pour la compatibilité HTTP/1.0
}

/**
 * Supprime toutes les variables instantiés
 * en raison de register_globals étant actifs
 *
 * @return
 *    {null} si register_globals PHP est désactivé
 */
function mib_unregister_GLOBALS()
{
    $register_globals = @ini_get('register_globals');
    if ($register_globals === '' || $register_globals === '0' || strtolower($register_globals) === 'off')
        return;

    // empèche script.php?GLOBALS[foo]=bar
    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
        exit('Je voudrais un sandwich au jambon et... un sandwich au jambon. Le jambon c\'est BON !');

    // les variables qui ne devraient pas être supprimées.
    $no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

    // enleve les éléments dans $GLOBALS qui sont présents dans n'importe lequel de ces superglobals.
    $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
    foreach ($input as $k => $v) {
        if (!in_array($k, $no_unset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
            unset($GLOBALS[$k]);    // Double le unset pour prévenir de la faille zend_hash_del_key_or_index dans PHP <4.4.3 and <5.1.4
        }
    }
}

/**
 * Tente de déterminer si on utilise http ou https
 */
function mib_get_current_protocol()
{
    $protocol = 'http';

    // vérifie si le serveur prétent utiliser HTTPS
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
        $protocol = 'https';

    // si nous sommes derière un "reverse proxy" (proxy inverse) pour décider quel protocole on utilise
    if (defined('MIB_IS_BEHIND_REVERSE_PROXY')) {
        // est-on derrière un "reverse proxy" Microsoft
        if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) != 'off')
            $protocol = 'https';

        // est-on derrière un "reverse proxy" Normal, si oui, quel protocole est utilisé
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
            $protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    return $protocol;
}

/**
 * Tente de déterminer l'url de base du site
 */
function mib_get_base_url($support_https = false)
{
    $base_url = false;

    if (defined('MIB_BASE_URL'))
        $base_url = MIB_BASE_URL;
    else {
        $scheme = mib_get_current_protocol();
        $port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $scheme == 'http') || ($_SERVER['SERVER_PORT'] != '443' && $scheme == 'https')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':' . $_SERVER['SERVER_PORT'] : '';
        $base_url = urldecode($scheme . '://' . preg_replace('/:' . $port . '$/', '', $_SERVER['HTTP_HOST']) . str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
    }

    $base_url = mib_trim($base_url, '/');

    // assurrons nous d'utiliser le bon protocole
    if ($support_https)
        $base_url = str_replace(array('http://', 'https://'), mib_get_current_protocol() . '://', $base_url);

    return $base_url;
}

/**
 * Trim les espaces en tenant compte des saut de ligne
 *
 * @param {string} $str
 *    Message à "trimer"
 *
 * @return {string} $str
 *    Message "trimé"
 */
function mib_trim($str, $charlist = " \t\n\r\x0b\xc2\xa0")
{
    return utf8_trim($str, $charlist);
}

/**
 * Converti \r\n et \r en \n
 *
 * @param {string} $str
 *
 * @return {string} $str
 */
function mib_linebreaks($str)
{
    return str_replace(array("\r\n", "\r"), "\n", $str);
}

/**
 * Convertion de \r\n , \r et \n en <br>
 *
 * @param string $str
 *
 * @return string $str
 */
function mib_htmllinebreaks($str)
{
    return str_replace(array("\r\n", "\r", "\n"), "<br>", $str);
}

/**
 * Format un affichage de valeur monétaire
 */
function mib_format_money($int, $round = 2, $money = '€')
{
    return number_format($int, $round, ',', ' ') . (!empty($money) ? ' ' . $money : '');
}

/**
 * Clean un string de ses sauts de ligne, espace en double
 * tout en renvoyant le tout sur en une seule ligne
 *
 * @param {string} $str
 * @param {bool} $all
 *    Si actif, supprime les saut de ligne et les tabulations
 *
 * @return {string} $str
 */
function mib_clean($str, $all = true)
{
    $str = mib_trim($str);
    $str = mib_linebreaks($str);
    if ($all) $str = str_replace(array("\t", "\n"), " ", $str);
    // convertit les caractères blancs multiples en un seul
    $str = preg_replace('%\s+%s', ' ', $str);

    return $str;
}

/**
 * Génération d'une clef aléatoire d'une longueur $len
 *
 * @param {int} $len
 * @param {bool} $readable
 * @param {bool} $hash
 *
 * @return {string} $key
 */
function mib_random_key($len, $readable = false, $hash = false)
{
    $key = '';

    if ($hash)
        $key = substr(sha1(uniqid(rand(), true)), 0, $len);
    else if ($readable) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for ($i = 0; $i < $len; ++$i)
            $key .= substr($chars, (mt_rand() % strlen($chars)), 1);
    } else {
        for ($i = 0; $i < $len; ++$i)
            $key .= chr(mt_rand(33, 126));
    }

    return $key;
}

/**
 * Génère un SHA-1 hash de $str
 *
 * @param {string} $str
 *
 * @return {string} $str
 */
function mib_hash($str)
{
    return sha1($str);
}

/**
 * Génère un hash de $str "salt" de $key
 *
 * @param {string} $str
 * @param {string} $key
 * @param {string} $raw_output
 */
function mib_hmac($data, $key, $raw_output = false)
{
    return sha1($key . sha1($data)); // Garde la compatibiliité avec la V1
    // return hash_hmac('sha1', $data, $key, $raw_output); // V2
}

/**
 * Remplace les caractères accentués et spéciaux (les plus courant) par leur plus proche
 * corespondance ASCII (sans accent)
 *
 * @param {string} $str
 *
 * @return {string} $str
 */
function mib_strtoascii($str)
{

    // tableau des corespondance
    $str_ascii = array(
        'A' => 'ÀÁÂÃÄÅĀĂǍẠẢẤẦẨẪẬẮẰẲẴẶǺĄ',
        'a' => 'àáâãäåāăǎạảấầẩẫậắằẳẵặǻą',
        'B' => 'Ḃ',
        'b' => 'ḃ',
        'C' => 'ÇĆĈĊČ',
        'c' => 'çćĉċč',
        'D' => 'ÐĎĐḊ',
        'd' => 'ďđḋ',
        'E' => 'ÈÉÊËĒĔĖĘĚẸẺẼẾỀỂỄỆ',
        'e' => 'èéêëēĕėęěẹẻẽếềểễệ',
        'F' => 'ḞƑ',
        'f' => 'ḟƒ',
        'G' => 'ĜĞĠĢ',
        'g' => 'ĝğġģ',
        'H' => 'ĤĦ',
        'h' => 'ĥħ',
        'i' => 'ìíî',
        'I' => 'ÌÍÎÏĨĪĬĮİǏỈỊ',
        'i' => 'ìíîïĩīĭįi̇ǐỉị',
        'J' => 'Ĵ',
        'j' => 'ĵ',
        'K' => 'Ķ',
        'k' => 'ķ',
        'L' => 'ĹĻĽĿŁ',
        'l' => 'ĺļľŀł',
        'M' => 'Ṁ',
        'm' => 'ṁ',
        'N' => 'ÑŃŅŇ',
        'n' => 'ñńņňŉ',
        'O' => 'ÒÓÔÕÖØŌŎŐƠǑǾỌỎỐỒỔỖỘỚỜỞỠỢ',
        'o' => 'òóôõöøōŏőơǒǿọỏốồổỗộớờởỡợð',
        'P' => 'Ṗ',
        'p' => 'p',
        'R' => 'ŔŖŘ',
        'r' => 'ŕŗř',
        'S' => 'ŚŜŞŠṠȘ',
        's' => 'śŝşšṡș',
        'T' => 'ŢŤŦȚṪ',
        't' => 'ţťŧțṫ',
        'U' => 'ÙÚÛÜŨŪŬŮŰŲƯǓǕǗǙǛỤỦỨỪỬỮỰ',
        'u' => 'ùúûüũūŭůűųưǔǖǘǚǜụủứừửữựµ',
        'W' => 'ŴẀẂẄ',
        'w' => 'ŵẁẃẅ',
        'Y' => 'ÝŶŸỲỸỶỴ',
        'y' => 'ýŷÿỹỵỷỳ',
        'Z' => 'ŹŻŽ',
        'z' => 'źżž',
        // ligatures
        'AE' => 'Æ',
        'ae' => 'æ',
        'OE' => 'Œ',
        'oe' => 'œ',
        'ss' => 'ß',
    );

    // convertion
    foreach ($str_ascii as $k => $v) {
        $str = mb_ereg_replace('[' . $v . ']', $k, $str);
    }

    return $str;
}


/**
 * Encode un string au format URL. Génère de belles URL pour optimiser le
 * référencement
 *
 * @param string $str
 *
 * @return string $str
 */
function mib_strtourl($str, $charlist = '0-9A-Za-z_\s\-', $replace_by = '-')
{
    // Remplacement des espaces blancs
    $str = str_replace(' ', $replace_by, mib_trim($str));

    // Remplace certains caractère connu avant de les remplacer par un "-" pour avoir la meilleur url
    $str = mib_strtoascii($str);

    // Remplace tout ce qui n'est pas Alphabetique par un tiret
    $str = preg_replace('![^' . $charlist . ']!', $replace_by, $str);

    // Convertit les multiples tiret en 1 seul
    $str = preg_replace('#\-+#s', $replace_by, $str);
    $str = preg_replace('#' . $replace_by . '+#s', $replace_by, $str);

    // Supprime les "-" en début/fin de ligne
    $str = mib_trim($str, $replace_by);

    return $str;
}

/**
 * Valide une adresse e-mail
 *
 * @param {string} $email
 *    email à valider
 *
 * @return {bool}
 */
function mib_valid_email($email)
{
    if (strlen($email) > 80)
        return false;

    return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email);
}

/**
 * Est-ce que c'est un email jetable ?
 *
 * @param {string} $email
 */
function mib_is_jetable_email($email)
{
    $email_jetable_services = array('jetable.org', 'yopmail.com', 'meltmail.com', 'anonymbox.com', 'prtnx.com', 'mail-temporaire.fr', 'trashmail.', 'kurzepost.de', 'objectmail.com', 'proxymail.eu', 'rcpt.at', 'trash-mail.at', 'wegwerfmail.', 'dodgit.com', 'whyspam.me', 'guerrillamailblock.com', 'thankyou2010.com', 'mailinator.com', 'filzmail.com', 'mailexpire.com', 'mailcatch.com', 'dispostable.com', 'spamavert.com', 'yxzx.net', 'tempemail.net', 'tilien.com', 'baxomale.ht.cx', 'tempomail.fr', 'spamfree24.', 'spamfree.eu', 'spam.la', 'spamspot.com', 'mintemail.com', 'mailnull.com', 'spamgourmet.com', 'spamcero.com', 'mytempemail.com', 'incognitomail.org', 'spamobox.com', 'deadaddress.com', 'tempail.com', 'deagot.com', 'mailscrap.com', 'privy-mail.com', 'makemetheking.com', 'onewaymail.com', 'ag.us.to', 'nospamfor.us', 'guerillamail.org');

    foreach ($email_jetable_services as $service)
        if (strpos($email, '@' . $service) !== false)
            return true;

    return false;
}

/**
 * Encode une chaine de caractère pour qu'elle s'affiche correctement en HTML
 *    "&" (et commercial) devient "&amp;"
 *    """ (guillemets doubles) devient "&quot;"
 *    "'" (guillemet simple) devient &#039
 *    "<" (inférieur à) devient "&lt;"
 *    ">" (supérieur à) devient "&gt;"
 *
 * @param {string} $str
 *    Chaine à encoder
 *
 * @return {string} $str
 *    Chaine encodé
 */
function mib_html($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Racourcis pour mib_html() qui affiche directement la chaine de caractère encodée
 *
 * @param {string} $str
 *    Chaine à encoder
 * @param {bool} $replace_empty_by_nbsp
 *    Une chaine vide sera remplacée par &nbsp;
 */
function e_html($str, $replace_empty_by_nbsp = false)
{
    if ($replace_empty_by_nbsp && empty($str))
        echo '&nbsp;';
    else
        echo mib_html($str);
}

/**
 * Remplace les caractères spéciaux pour les chaines de caractères en javascript
 *
 * @param {string} $str
 *    Message à encoder
 *
 * @return {string} $str
 *    Message encodé
 */
function mib_jsspecialchars($str)
{
    return str_replace('\'', '\\\'', $str);
}

/**
 * Racourcis d'affichage pour une chaine de caractère encodée
 *
 * @param {string} $str
 *    Message à encoder
 */
function e_js($str)
{
    echo mib_jsspecialchars($str);
}

/**
 * Remplace les valeurs de template d'une chaine
 *
 * @param {string} $str
 * @param {array} $vars
 *
 * @return {string} $str
 *
 * @example
 *    mib_sprintftpl('Bonjour [[%your_name%]], mon nom est [[%my_name%]] !', array('
 *        'your_name'    => 'Jo',
 *        'my_name'    => 'Mibbo',
 *    ));
 *
 *    mib_sprintftpl(__('Le plugin [[%plugin_name%]] est manquant.'), array('plugin_name'=>$plugin));
 */
function mib_sprintftpl($str, $vars)
{
    $tmp = array();
    foreach ($vars as $k => $v)
        $tmp['[[%' . $k . '%]]'] = $v;

    return str_replace(array_keys($tmp), array_values($tmp), $str);
}

/**
 * On essait de déterminer l'adresse IP
 *
 * @return {string} $_SERVER['REMOTE_ADDR']
 *    IP
 */
function mib_remote_address()
{
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Détermine toutes les infos d'une adresse IP
 *
 * @param {string} $remote_address
 *    adresse IP à examiner. Si aucune adresse IP n'est donnée, l'adresse IP du serveur sera utilisée.
 *
 * @return {array}
 */
function mib_remote_address_infos($remote_address = false)
{
    $req = @unserialize(file_get_contents('http://ip-api.com/php' . ($remote_address ? '/' . $remote_address : '')));
    if ($req && $req['status'] == 'success') {
        return $req;
    }

    return null;
}

/**
 * Renvois l'info de la requette en cours
 *
 * @param {int} $info
 *    profondeur de la requette
 *
 * @return {string} $info
 */
function mib_get_request_infos($info = 1)
{
    global $MIB_PAGE;

    $info = intval($info);
    $req = explode('/', $MIB_PAGE['info']);

    if ($info >= 0)
        $info = $info == 0 ? 1 : $info - 1;
    else
        $info = count($req) + $info;

    return !empty($req[$info]) ? mib_trim($req[$info]) : false;
}

/**
 * Retourne le contenu d'un dossier
 *
 * @param {string} $dir_path
 * @param {string} $return (dir|file)
 * @param {string} $extension (.php|.jpg|...)
 *
 * @return {array} $dir_contents
 */
function mib_dir_get_contents($dir_path, $return = false, $extension = false)
{
    $dir_contents = array();

    $dir_path = mib_trim($dir_path, '/');
    $extension = !empty($extension) ? strtolower(mib_trim($extension, '.')) : false;

    if (is_dir($dir_path)) {
        if ($dh = opendir($dir_path)) {
            while (($item = readdir($dh)) !== false) {
                // tous les contenus
                if (!$return) {
                    if (!is_dir($dir_path . '/' . $item) || (is_dir($dir_path . '/' . $item) && substr($item, 0, 1) != '.')) // ce n'est pas un dossier raçine
                        $dir_contents[$item] = $dir_path . '/' . $item;
                } // dossiers
                else if ($return == 'dir' && is_dir($dir_path . '/' . $item)) {
                    if (substr($item, 0, 1) != '.') // ce n'est pas un dossier raçine
                        $dir_contents[$item] = $dir_path . '/' . $item;
                } // fichiers
                else if ($return == 'file' && is_file($dir_path . '/' . $item)) {
                    if (!$extension)
                        $dir_contents[$item] = $dir_path . '/' . $item;
                    else if (strtolower(strrchr($item, '.')) == '.' . $extension) {
                        $name = substr($item, 0, strlen($item) - (strlen($extension) + 1));
                        $dir_contents[$name] = $dir_path . '/' . $item;
                    }
                }
            }
            closedir($dh);

            ksort($dir_contents);
        }
    }

    return $dir_contents;
}

/**
 * Fonction de trie d'un tableau multidimensionnel
 *
 * @param {array} $array | tableau multidimensionnel à trier
 * @param {string} $order_by | requette de tri (SQL like)
 *
 * @example
 *    mib_sort($array, 'date DESC, title ASC');
 *
 * @return {bool}
 */
function mib_sort(&$array, $order_by)
{
    if (!is_array($array) || !is_array(reset($array)))
        trigger_error(__('Le tableau doit être multidimensionnel.'), E_USER_ERROR);

    $ob = explode(',', $order_by);
    $s = array();
    foreach ($ob as $o) {
        $o = mib_trim($o);

        // tri descendant
        if (strtoupper(substr($o, -5)) == ' DESC') {
            $c = substr($o, 0, -5);
            $o = 'DESC';
        } // trie ascendant (par défaut)
        else {
            if (strtoupper(substr($o, -4)) == ' ASC')
                $c = substr($o, 0, -4);
            else
                $c = $o;

            $o = 'ASC';
        }

        if (isset($s[$c]))
            trigger_error(mib_sprintftpl(__('Doublon de tri sur le nom de la colonne [[%column_name%]]'), array('column_name' => $c)), E_USER_WARNING);

        $s[$c] = $o;
    }

    if (!empty($s)) {
        $f = '$r = 0;' . "\n";
        foreach ($s as $c => $o)
            $f .= 'if(!$r) $r = ' . ($o == 'DESC' ? '-' : '') . 'strnatcmp(strtolower(mib_strtoascii($a[\'' . $c . '\'])), strtolower(mib_strtoascii($b[\'' . $c . '\'])));' . "\n";

        $f .= 'return $r;';
        $sortFunc = function ($a, $b) use ($s) {
            $r = 0;
            foreach ($s as $c => $o) {
                if (!$r)
                    $r = ($o == 'DESC' ? '-' : '') . strnatcmp(strtolower(mib_strtoascii($a[$c])), strtolower(mib_strtoascii($b[$c])));
            }
            return $r;
        };
        return usort($array, $sortFunc);
    } else
        return false;
}

/**
 * Retourne le représentation JSON d'une valeur
 * fonction ajouté pour PHP <5.2
 *
 * @param {string} $value
 */
//if (!function_exists('json_encode')) {
//    function json_encode($value)
//    {
//        switch ($type = gettype($value)) {
//            case 'NULL':
//                return 'null';
//            case 'boolean':
//                return ($value ? 'true' : 'false');
//            case 'integer':
//            case 'double':
//            case 'float':
//                return $value;
//            case 'string':
//                return '"' . addslashes($value) . '"';
//            case 'object':
//                $value = get_object_vars($value);
//            case 'array':
//                $output_index_count = 0;
//                $output_indexed = array();
//                $output_associative = array();
//                foreach ($value as $k => $v) {
//                    $output_indexed[] = json_encode($v);
//                    $output_associative[] = json_encode($k) . ':' . json_encode($v);
//                    if ($output_index_count !== NULL && $output_index_count++ !== $k) {
//                        $output_index_count = NULL;
//                    }
//                }
//                if ($output_index_count !== NULL) {
//                    return '[' . implode(',', $output_indexed) . ']';
//                } else {
//                    return '{' . implode(',', $output_associative) . '}';
//                }
//            default:
//                return false; // Not supported
//        }
//    }
//}

/**
 * Décode une chaîne JSON
 * fonction ajouté pour PHP <5.2
 *
 * @param {string} $json
 */
if (!function_exists('json_decode')) {
    function json_decode($json)
    {
        $comment = false;
        $out = '$x=';
        for ($i = 0; $i < strlen($json); $i++) {
            if (!$comment) {
                if ($json[$i] == '{' || $json[$i] == '[')
                    $out .= 'array(';
                else if ($json[$i] == '}' || $json[$i] == ']')
                    $out .= ')';
                else if ($json[$i] == ':')
                    $out .= '=>';
                else if ($json[$i] == ',')
                    $out .= ',';
                else if ($json[$i] == '"')
                    $out .= '"';
                //else if ( !preg_match('/\s/', $json[$i]) )
                //	return null;
            } else
                $out .= $json[$i] == '$' ? '\$' : $json[$i];

            if ($json[$i] == '"' && $json[($i - 1)] != '\\')
                $comment = !$comment;
        }

        eval($out . ';');

        return $x;
    }
}

/**
 * Charge une fonction si elle existe
 *
 * @param string $function Function
 *
 * @return function Le résultat de la fonction
 */
$mib_function = array();
function mib_core_function($function)
{
    global $mib_function;
    $function_args = func_get_args();
    $function = array_shift($function_args);
    $mib_function[$function] = array();
    $mib_function[$function]['name'] = $function;

    // On load la fonction
    if (file_exists(MIB_PATH_VAR . 'core/function.' . $function . '.php')) {
        $mib_function[$function]['path'] = MIB_PATH_VAR . 'core/';
        require_once MIB_PATH_VAR . 'core/function.' . $function . '.php';
    } else if (file_exists(MIB_PATH_SYS . 'core/function.' . $function . '.php')) {
        $mib_function[$function]['path'] = MIB_PATH_SYS . 'core/';
        require_once MIB_PATH_SYS . 'core/function.' . $function . '.php';
    } else
        return false;

    // On appel la fonction
    return call_user_func_array($function, $function_args);
}

/**
 * Charge une class si elle existe
 *
 * @param string $class Class
 *
 * @return bool La classe a été chargée ou non
 */
$mib_class = array();
function mib_core_class($class)
{
    global $mib_class;
    $class_args = func_get_args();
    $class = array_shift($class_args);
    $mib_class[$class] = array();
    $mib_class[$class]['name'] = $class;

    if (file_exists(MIB_PATH_VAR . 'core/class.' . $class . '.php')) {
        $mib_class[$class]['path'] = MIB_PATH_VAR . 'core/';
        require_once MIB_PATH_VAR . 'core/class.' . $class . '.php';
    } else if (file_exists(MIB_PATH_VAR . 'core/class.' . $class . '/' . $class . '.php')) {
        $mib_class[$class]['path'] = MIB_PATH_VAR . 'core/class.' . $class . '/';
        require_once MIB_PATH_VAR . 'core/class.' . $class . '/' . $class . '.php';
    } else if (file_exists(MIB_PATH_SYS . 'core/class.' . $class . '.php')) {
        $mib_class[$class]['path'] = MIB_PATH_SYS . 'core/';
        require_once MIB_PATH_SYS . 'core/class.' . $class . '.php';
    } else if (file_exists(MIB_PATH_SYS . 'core/class.' . $class . '/' . $class . '.php')) {
        $mib_class[$class]['path'] = MIB_PATH_SYS . 'core/class.' . $class . '/';
        require_once MIB_PATH_SYS . 'core/class.' . $class . '/' . $class . '.php';
    } else
        return false;

    return true;
}

/**
 * Vérifie si un fichier existe
 *
 * @param string $file fichier
 *
 * @return bool
 */
function mib_file_exists($file)
{
    if (file_exists(MIB_PATH_VAR . $file))
        return true;
    else if (file_exists(MIB_PATH_SYS . $file))
        return true;

    return false;
}

/**
 * Vérifie si un fichier de theme existe
 *
 * @param string $file fichier
 *
 * @return bool
 */
function mib_theme_exists($file)
{
    if (file_exists(MIB_THEME_DIR . $file))
        return true;
    else if (file_exists(MIB_THEME_DEFAULT_DIR . $file))
        return true;

    return false;
}

/**
 * Charge le contenu d'un fichier
 *
 * @param string $file fichier
 *
 * @return string le contenu du fichier
 */
function mib_file_get_contents($file)
{
    global $MIB_PAGE;

    if (file_exists(MIB_PATH_VAR . $file)) {
        $MIB_PAGE['cur_location'] = 'website';
        return file_get_contents(MIB_PATH_VAR . $file);
    } else if (file_exists(MIB_PATH_SYS . $file)) {
        $MIB_PAGE['cur_location'] = 'system';
        return file_get_contents(MIB_PATH_SYS . $file);
    }

    return false;
}

/**
 * Charge le contenu d'un theme
 *
 * @param string $file fichier
 *
 * @return string le contenu du fichier
 */
function mib_theme_get_contents($file)
{
    global $MIB_PAGE;

    if (file_exists(MIB_THEME_DIR . $file)) {
        $MIB_PAGE['cur_theme'] = MIB_THEME;
        return file_get_contents(MIB_THEME_DIR . $file);
    } else if (file_exists(MIB_THEME_DEFAULT_DIR . $file)) {
        $MIB_PAGE['cur_theme'] = MIB_THEME_DEFAULT;
        return file_get_contents(MIB_THEME_DEFAULT_DIR . $file);
    }

    return false;
}

/**
 * Charge un plugin
 *
 * @uses $MIB_CONFIG
 * @uses $MIB_PAGE
 *
 * @param string $plugin Nom du plugin à charger
 * @param bool $admin Administration du plugin
 *
 * @return array Plugin chargé
 */
function mib_load_plugin($plugin)
{
    global $MIB_DB, $MIB_CONFIG, $MIB_PLUGINS, $MIB_PAGE, $MIB_USER, $MIB_URL, $MIB_URL_REWRITED;

    $MIB_PLUGIN = array('css_location' => null);

    // Requette ajax de l'interface d'administration
    if (defined('MIB_PLUGIN_MANAGE'))
        $MIB_PLUGIN['filename'] = $plugin . '_manage.php';
    else
        $MIB_PLUGIN['filename'] = $plugin . '.php';

    if (file_exists(MIB_PATH_VAR . 'plugins/' . $plugin . '/' . $MIB_PLUGIN['filename'])) {
        $MIB_PLUGIN['path'] = MIB_PATH_VAR . 'plugins/' . $plugin . '/';
        $MIB_PLUGIN['location'] = MIB_URL_VAR . '/plugins/' . $plugin;
        $MIB_PLUGIN['css_location'] = '../plugin/' . MIB_URL_VAR . '/' . $plugin;
    } else if (file_exists(MIB_PATH_SYS . 'plugins/' . $plugin . '/' . $MIB_PLUGIN['filename'])) {
        $MIB_PLUGIN['path'] = MIB_PATH_SYS . 'plugins/' . $plugin . '/';
        $MIB_PLUGIN['location'] = MIB_URL_SYS . '/plugins/' . $plugin;
        $MIB_PLUGIN['css_location'] = '../plugin/' . MIB_URL_SYS . '/' . $plugin;
    }

    // exit();
    if (defined('MIB_MANAGE'))
        $MIB_PLUGIN['css_location'] = '../' . $MIB_PLUGIN['css_location'];


    // Un plugin à été trouvé
    if (isset($MIB_PLUGIN['path'])) {
        $MIB_PLUGIN['name'] = $plugin;

        // Attribue les permissions du plugin si on est dans le BO
        if (defined('MIB_MANAGE') && $MIB_PLUGIN['name'] != 'bo') {
            $MIB_USER['can_read_plugin'] = get_plugin_bo_perms($MIB_PLUGIN['name'], 'read');
            $MIB_USER['can_write_plugin'] = get_plugin_bo_perms($MIB_PLUGIN['name'], 'write');

            if (defined('MIB_PLUGIN_MANAGE') && !$MIB_USER['can_read_plugin'])
                error(__('Vous n\'avez pas la permission d\'accéder à cette extension.'));
        }

        // Ajoute les configs de la DB
        $MIB_PLUGIN['configs'] = array();
        if (!empty($MIB_PLUGINS[$MIB_PLUGIN['name']]))
            $MIB_PLUGIN['configs'] = $MIB_PLUGINS[$MIB_PLUGIN['name']];


        // Charge la langue du plugin
        mib_load_locale('plugins/' . $MIB_PLUGIN['name']);
        if (defined('MIB_PLUGIN_MANAGE'))
            mib_load_locale('plugins/' . $MIB_PLUGIN['name'] . '_manage');

        // Charge le plugin
        ob_start();

        if (defined('MIB_DEBUG')) { // Calcul le tps d'execution d'un plugin
            list($usec, $sec) = explode(' ', microtime());
            $MIB_PLUGIN['time']['start'] = ((float)$usec + (float)$sec);
        }

        // Chargement du plugin
        require $MIB_PLUGIN['path'] . $MIB_PLUGIN['filename'];

        if (defined('MIB_DEBUG')) { // Calcul le tps d'execution d'un plugin
            list($usec, $sec) = explode(' ', microtime());
            $MIB_PLUGIN['time']['finish'] = ((float)$usec + (float)$sec);
            $MIB_PLUGIN['time']['duration'] = ($MIB_PLUGIN['time']['finish'] - $MIB_PLUGIN['time']['start']);
        }

        $MIB_PLUGIN['tpl'] = ob_get_contents();
        ob_end_clean();
    }


    if (!empty($MIB_PLUGIN['tpl'])) {
        // Remplace {{tpl:MIB_PLUGIN}} par le répertoire du plugin en cours
        $MIB_PLUGIN['tpl'] = str_replace('{{tpl:MIB_PLUGIN}}', $MIB_PLUGIN['css_location'], $MIB_PLUGIN['tpl']);
    }

    // le plugin à bien été chargé
    if (!empty($MIB_PLUGIN['tpl']) || !empty($MIB_PLUGIN['json'])) define('MIB_PLUGIN', 1);

    return $MIB_PLUGIN;
}

/**
 * Charge un widget
 *
 * @uses $MIB_CONFIG
 * @uses $MIB_PAGE
 * @uses $MIB_WIDGETS
 *
 * @param string $plugin Nom du plugin à charger
 * @param string $widget Nom du widget à charger
 *
 * @return array Plugin chargé
 */
function mib_load_widget($plugin, $widget)
{
    global $MIB_DB, $MIB_CONFIG, $MIB_PAGE, $MIB_USER, $MIB_URL, $MIB_URL_REWRITED, $MIB_PLUGIN, $MIB_WIDGETS;

    $cur_widget = array();

    if (file_exists(MIB_PATH_VAR . 'plugins/' . $plugin . '/widgets/' . $plugin . '_' . $widget . '.php')) {
        $cur_widget['path'] = MIB_PATH_VAR . 'plugins/' . $plugin . '/widgets/';
        $cur_widget['location'] = MIB_URL_VAR . '/plugins/' . $plugin . '/widgets';
    } else if (file_exists(MIB_PATH_SYS . 'plugins/' . $plugin . '/widgets/' . $plugin . '_' . $widget . '.php')) {
        $cur_widget['path'] = MIB_PATH_SYS . 'plugins/' . $plugin . '/widgets/';
        $cur_widget['location'] = MIB_URL_SYS . '/plugins/' . $plugin . '/widgets';
    }

    // Un widget a été trouvé
    if (isset($cur_widget['path'])) {
        $cur_widget['plugin'] = $plugin;
        $cur_widget['name'] = $widget;

        // Charge la langue du plugin
        mib_load_locale('plugins/' . $cur_widget['plugin']);

        // Charge le plugin
        ob_start();

        if (defined('MIB_DEBUG')) { // Calcul le tps d'execution d'un plugin
            list($usec, $sec) = explode(' ', microtime());
            $cur_widget['time']['start'] = ((float)$usec + (float)$sec);
        }

        // Chargement du widget
        require $cur_widget['path'] . $cur_widget['plugin'] . '_' . $cur_widget['name'] . '.php';

        if (defined('MIB_DEBUG')) { // Calcul le tps d'execution d'un plugin
            list($usec, $sec) = explode(' ', microtime());
            $cur_widget['time']['finish'] = ((float)$usec + (float)$sec);
            $cur_widget['time']['duration'] = ($cur_widget['time']['finish'] - $cur_widget['time']['start']);
        }

        $cur_widget['tpl'] = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($cur_widget['tpl']))
        $MIB_WIDGETS[$cur_widget['plugin'] . '_' . $cur_widget['name']] = $cur_widget;

    return $cur_widget;
}

/**
 * On essaye de déterminer l'URL actuelle
 *
 * @param int $max_length
 */
function mib_get_current_url($max_length = 0)
{

    $protocol = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http://' : 'https://';
    $port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':' . $_SERVER['SERVER_PORT'] : '';

    $url = urldecode($protocol . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI']);

    if (strlen($url) <= $max_length || $max_length == 0)
        return $url;

    // On n'a pas trouvé une URL assez courte
    return null;
}

function mib_addParams_to_url($url, $params)
{
    if (!empty($params) && !empty($url)) {
        $urlHasParams = strpos($url, '?');
        if (!$urlHasParams) {
            $separator = '?';
            foreach ($params as $key => $val) {
                $url .= $separator . htmlentities($key) . '=' . htmlentities($val);
                $separator = '&';
            }
        } else {
            $separator = '&';
            foreach ($params as $key => $val) {
                $paramsPos = strpos($url, htmlentities($key) . '=');
                if ($paramsPos !== false) {
                    $re = '/' . htmlentities($key) . '=([a-zA-Z0-9\-]*)/m';
                    $url = preg_replace($re, htmlentities($key) . '=' . htmlentities($val), $url);
                } else {
                    $url .= $separator . htmlentities($key) . '=' . htmlentities($val);
                }
            }
        }
    }
    return empty($url) ? '' : $url;
}

function mib_get_current_url_with_params($params = [])
{

    $protocol = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http://' : 'https://';
    $port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':' . $_SERVER['SERVER_PORT'] : '';

    $url = urldecode($protocol . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI']);

    return mib_addParams_to_url($url,$params);

}

function mib_get_current_params($key){
    return !empty($_GET[$key])? $_GET[$key]: null;
}

/**
 * On vérifie l'URL demandé, et si elle n'est pas bonne,
 * on redirige vers la bonne URL
 *
 * @param string $url URL à vérifier
 */
function mib_confirm_current_url($url)
{

    // Clean l'URL pour qu'elle coresponde aux règles qu'on utilise
    $url = str_replace('&amp;', '&', rawurldecode($url));

    $hash = strpos($url, '#');
    if ($hash !== false)
        $url = substr($url, 0, $hash);

    $current_url = mib_get_current_url();
    $pos = strrpos($current_url,$url);
    if ($pos !== 0) {
        if ((defined('MIB_JSON') || defined('MIB_AJAX')) && defined('MIB_MANAGE'))
            error(sprintf(__('Impossible de confirmer l\'url : %s'), '<br><code>' . $url .' / '.$current_url.  '</code>'), __FILE__, __LINE__);
        else {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $url);
            exit;
        }
    }
}

/**
 * Découpe une chaine de caractère et ajoute ...
 *
 * @param string $str
 * @param int $split
 *
 * @return string $str
 */
function mib_split($str, $split = 50)
{
    if (intval($split) == 0) return $str;

    if (utf8_strlen($str) > $split) {
        return utf8_substr(mib_trim($str), 0, $split) . '...';
    } else
        return $str;
}

/**
 * Convertion de taille
 *
 * @param int $bytes
 * @param int $precision
 *
 * @return string $str
 */
function mib_bytestohuman($bytes, $precision = 2)
{
    $units = array('bytes', 'Ko', 'Mo', 'Go', 'To');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Authentifie un utilisateur en le confrontant à la database
 * $user peut être un ID (integer) ou un email (string)
 *
 * @uses $MIB_DB
 * @uses $MIB_USER
 *
 * @param (int|string) $user Utilisateur
 * @param string $password Mot de passe
 */
function authenticate_user($user, $password, $password_is_hash = false)
{
    global $MIB_DB, $MIB_USER;

    // On vérifit si il y a un utilisateur avec le user ID et password hash du cookie
    $query = array(
        'SELECT' => 'u.*, g.*, o.logged, o.idle',
        'FROM' => 'users AS u',
        'JOINS' => array(
            array(
                'INNER JOIN' => 'groups AS g',
                'ON' => 'g.g_id=u.group_id'
            ),
            array(
                'LEFT JOIN' => 'online AS o',
                'ON' => 'o.user_id=u.id'
            )
        )
    );

    // Est-ce qu'on a un ID ou un email?
    $query['WHERE'] = is_int($user) ? 'u.id=' . intval($user) : 'u.email=\'' . $MIB_DB->escape($user) . '\'';

    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    $MIB_USER = $MIB_DB->fetch_assoc($result);

    // Si l'autorisation échoue
    if (!isset($MIB_USER['id']) || ($password_is_hash && $password != $MIB_USER['password']) || (!$password_is_hash && mib_hmac($password, $MIB_USER['salt']) != $MIB_USER['password']))
        set_default_user();
}

/**
 * On essaye de se loguer avec le cookie contenant l'id utilisateur et le mot de passe
 * crypté
 *
 * @uses $MIB_DB
 * @uses $MIB_CONFIG
 *
 * @param array $MIB_USER Utilisateur
 */
function cookie_login(&$MIB_USER)
{
    global $MIB_DB, $MIB_CONFIG;

    $now = time();
    $expire = $now + 1209600; // Le cookie expire après 14 jours

    // Nous supposons que c'est un invité
    $cookie = array('user_id' => 1, 'password_hash' => 'Invité', 'expiration_time' => 0, 'expire_hash' => 'Invité');

    // Si le cookie est définit, nous obtenons le user_id et password hash de celui-ci
    if (isset($_COOKIE[COOKIE_NAME]))
        @list($cookie['user_id'], $cookie['password_hash'], $cookie['expiration_time'], $cookie['expire_hash']) = @explode('|', base64_decode($_COOKIE[COOKIE_NAME]));

    // Si c'est un cookie pour un utilisateur logué qui n'a pas déjà expiré
    if (intval($cookie['user_id']) > 1 && intval($cookie['expiration_time']) > $now) {
        authenticate_user(intval($cookie['user_id']), $cookie['password_hash'], true);

        // Maintenant on valide le hash du cookie
        if ($cookie['expire_hash'] !== sha1($MIB_USER['salt'] . $MIB_USER['password'] . mib_hmac(intval($cookie['expiration_time']), $MIB_USER['salt'])))
            set_default_user();

        // Si on a en retour l'utilisateur par défaut, le login a échoué
        if ($MIB_USER['id'] == '1') {
            mib_setcookie(COOKIE_NAME, base64_encode('1|' . mib_random_key(8, false, true) . '|' . $expire . '|' . mib_random_key(8, false, true)), $expire);
            return;
        }

        // On envois un nouveau cookie mis à jour avec le nouveau timestamp d'expiration
        $expire = (intval($cookie['expiration_time']) > $now + $MIB_CONFIG['timeout_visit']) ? $now + 1209600 : $now + $MIB_CONFIG['timeout_visit'];
        mib_setcookie(COOKIE_NAME, base64_encode($MIB_USER['id'] . '|' . $MIB_USER['password'] . '|' . $expire . '|' . sha1($MIB_USER['salt'] . $MIB_USER['password'] . mib_hmac($expire, $MIB_USER['salt']))), $expire);

        // A définir si vous ne voulez pas que cette visite affecte la liste des utilisateur en ligne, et $MIB_USER['last_visit']
        if (!defined('MIB_QUIET_VISIT')) {
            // Met à jour la liste des utilisateurs en ligne
            if (!$MIB_USER['logged']) {
                $MIB_USER['logged'] = $now;

                // REPLACE INTO avoids a user having two rows in the online table
                $query = array(
                    'REPLACE' => 'user_id, ident, logged',
                    'INTO' => 'online',
                    'VALUES' => $MIB_USER['id'] . ', \'' . $MIB_DB->escape($MIB_USER['username']) . '\', ' . $MIB_USER['logged'] . '',
                    'UNIQUE' => 'user_id=' . $MIB_USER['id']
                );

                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
            } else {
                // Cas special : On a eu un timed out, mais aucun des autres utilisateurs n'a été sur le site depuis le timed out
                if ($MIB_USER['logged'] < ($now - $MIB_CONFIG['timeout_visit'])) {
                    $query = array(
                        'UPDATE' => 'users',
                        'SET' => 'last_visit=' . $MIB_USER['logged'],
                        'WHERE' => 'id=' . $MIB_USER['id']
                    );
                    $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

                    $MIB_USER['last_visit'] = $MIB_USER['logged'];
                }

                // Met à jour la date de connection dans la liste des utimlisateurs en ligne
                $query = array(
                    'UPDATE' => 'online',
                    'SET' => 'logged=' . $now,
                    'WHERE' => 'user_id=' . $MIB_USER['id']
                );

                if ($MIB_USER['idle'] == '1')
                    $query['SET'] .= ', idle=0';

                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
            }
        }

        if (empty($MIB_USER['timezone'])) $MIB_USER['timezone'] = $MIB_CONFIG['server_timezone'];
        $MIB_USER['is_guest'] = false;
    } else
        set_default_user();
}

/**
 * On construit $MIB_USER avec les valeurs par défauts (pour les invités)
 *
 * @uses $MIB_DB
 * @uses $MIB_USER
 * @uses $MIB_CONFIG
 */
function set_default_user()
{
    global $MIB_DB, $MIB_USER, $MIB_CONFIG;

    $remote_addr = mib_remote_address();

    // Sélectionne l'utilisateur invité
    $query = array(
        'SELECT' => 'u.*, g.*, o.logged',
        'FROM' => 'users AS u',
        'JOINS' => array(
            array(
                'INNER JOIN' => 'groups AS g',
                'ON' => 'g.g_id=u.group_id'
            ),
            array(
                'LEFT JOIN' => 'online AS o',
                'ON' => 'o.ident=\'' . $MIB_DB->escape($remote_addr) . '\''
            )
        ),
        'WHERE' => 'u.id=1'
    );

    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    if (!$MIB_DB->num_rows($result))
        mib_error(sprintf(__('Impossible de sélectionner les informations invité. La table %s doit contenir une entrée avec un id = 1 qui représente les utilisateurs anonymes.'), '<code>' . $MIB_DB->prefix . 'users</code>'));

    $MIB_USER = $MIB_DB->fetch_assoc($result);

    // A définir si vous ne voulez pas que cette visite affecte la liste des utilisateur en ligne, et $MIB_USER['last_visit']
    if (!defined('MIB_QUIET_VISIT')) {

        // On actualise la liste des utilisateurs en ligne
        if (!$MIB_USER['logged']) {
            $MIB_USER['logged'] = time();

            // REPLACE INTO avoids a user having two rows in the online table
            $query = array(
                'REPLACE' => 'user_id, ident, logged',
                'INTO' => 'online',
                'VALUES' => '1, \'' . $MIB_DB->escape($remote_addr) . '\', ' . $MIB_USER['logged'] . '',
                'UNIQUE' => 'user_id=1 AND ident=\'' . $MIB_DB->escape($remote_addr) . '\''
            );
            $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
        } else {
            $query = array(
                'UPDATE' => 'online',
                'SET' => 'logged=' . time(),
                'WHERE' => 'ident=\'' . $MIB_DB->escape($remote_addr) . '\''
            );
            $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
        }
    }

    $MIB_USER['timezone'] = $MIB_CONFIG['server_timezone'];
    $MIB_USER['is_guest'] = true;
}

function mib_isAdminLogged(){

    global  $MIB_CONFIG;
    $cookie = array('user_id' => 0, 'password_hash' => 'Invité', 'expiration_time' => 0, 'expire_hash' => 'Invité');
    // Si le cookie est définit, nous obtenons le user_id et password hash de celui-ci
    if (isset($_COOKIE[COOKIE_NAME]))
        @list($cookie['user_id'], $cookie['password_hash'], $cookie['expiration_time'], $cookie['expire_hash']) = @explode('|', base64_decode($_COOKIE[COOKIE_NAME]));//$now = time();
//    //$expire = $now + 1209600; // Le cookie expire après 14 jours
//    $expire = intval($cookie['expiration_time']) < $now ;
//    var_dump($cookie, $expire,intval($cookie['expiration_time']),$now,intval($cookie['expiration_time'])- $now,  $MIB_CONFIG['timeout_visit'],1209600);
//    exit();


    return   $cookie['user_id'] && $cookie['user_id']!==0 ;



}

/**
 * Validation d'un nom d'utilisateur avant de l'ajouter en DB
 *
 * @param {string} $username
 * @param {int} $exclude_id
 */
function validate_username($username, $exclude_id = null)
{
    global $MIB_DB;

    $errors = array();

    // Convertit les caractères blancs multiples en un seul (pour prévenir des personnes qui s'inscrive avec des nom d'utilisateur indistingable)
    $username = preg_replace('#\s+#s', ' ', $username);

    // Validate username
    if (utf8_strlen($username) < 4)
        $errors[] = __('Le nom d\'utilisateur doit être constitué d\'au moins 4 caractères.');
    else if (utf8_strlen($username) > 50)
        $errors[] = __('Le nom d\'utilisateur ne peut contenir plus de 50 caractères.');
    else if (utf8_strtolower($username) == utf8_strtolower('Invité') || utf8_strtolower($username) == utf8_strtolower(__('Invité')))
        $errors[] = __('Ce nom d\'utilisateur est réservé.');
    else if (is_numeric($username))
        $errors[] = __('Le nom d\'utilisateur ne peut pas être un nombre.');
    else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username) || preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username))
        $errors[] = __('Le nom d\'utilisateur ne peut pas être au format d\'une adresse IP.');
    else if (strpos($username, '<') !== false || strpos($username, '>') !== false || strpos($username, '[') !== false || strpos($username, ']') !== false || strpos($username, '\'') !== false || strpos($username, '"') !== false || strpos($username, '@') !== false)
        $errors[] = __('Le nom d\'utilisateur ne peut pas contenir les caractères @, \', ", &lt; ou  &gt; et [ ou ].');

    // Vérifie si un username est en double
    $query = array(
        'SELECT' => 'username',
        'FROM' => 'users',
        'WHERE' => '(UPPER(username)=UPPER(\'' . $MIB_DB->escape($username) . '\') OR UPPER(username)=UPPER(\'' . $MIB_DB->escape(preg_replace('/[^\w]/u', '', $username)) . '\')) AND id>1'
    );
    if ($exclude_id)
        $query['WHERE'] .= ' AND id!=' . $exclude_id;
    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    if ($MIB_DB->num_rows($result))
        $errors[] = __('Quelqu\'un est déjà inscrit avec le nom d\'utilisateur' . ' ' . mib_html($MIB_DB->result($result)) . '. Le nom d\'utilisateur est trop ressemblant. Il doit différer par au moins un caractère alpha-numérique (a-z ou 0-9)');

    return $errors;
}

/**
 * Validation d'une adresse e-mail avant de l'ajouter en DB
 *
 * @param {string} $email
 * @param {int} $exclude_id
 */
function validate_email($email, $exclude_id = null)
{
    global $MIB_DB;

    $errors = array();

    $email = utf8_strtolower($email);

    if (!mib_valid_email($email))
        $errors[] = __('Adresse e-mail invalide.');
    else if (utf8_strlen($email) > 80)
        $errors[] = __('L\'adresse e-mail ne peut contenir plus de 80 caractères.');
    else if (mib_is_jetable_email($email))
        $errors[] = __('Les adresses e-mail jetables ne sont pas autorisées.');

    // Vérifie si un email est en double
    $query = array(
        'SELECT' => 'id, username',
        'FROM' => 'users',
        'WHERE' => 'LOWER(email)=LOWER(\'' . $MIB_DB->escape($email) . '\') AND id>1'
    );
    if ($exclude_id)
        $query['WHERE'] .= ' AND id!=' . $exclude_id;
    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    if ($MIB_DB->num_rows($result))
        $errors[] = __('Un utilisateur est déjà enregistré avec cette adresse e-mail.');

    return $errors;
}


/**
 * Construction d'un cookie, MIB style!
 *
 * @param name $name
 * @param string $value
 * @param int $expire
 */
function mib_setcookie($name, $value, $expire)
{

    // Active l'envoi d'un P3P header en supprimant // devant la ligne suivante (a essayer si le login échoue avec IE6)
    @header('P3P: CP="CUR ADM"');

    if (version_compare(PHP_VERSION, '5.2.0', '>='))
        setcookie($name, $value, $expire, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, true);
    else
        setcookie($name, $value, $expire, COOKIE_PATH . '; HttpOnly', COOKIE_DOMAIN, COOKIE_SECURE);
}

/**
 * Actualisation "Liste des utilisateurs en ligne"
 *
 * @uses $MIB_DB
 * @uses $MIB_CONFIG
 * @uses $MIB_USER
 */
function update_users_online()
{
    global $MIB_DB, $MIB_CONFIG, $MIB_USER;

    $now = time();

    // Sélectionne les anciennes entrées de la liste des utilisateurs qui sont plus vieilles que "timeout_online"
    $query = array(
        'SELECT' => 'o.*',
        'FROM' => 'online AS o',
        'WHERE' => 'o.logged<' . ($now - $MIB_CONFIG['timeout_online'])
    );
    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    while ($cur_user = $MIB_DB->fetch_assoc($result)) {
        // Si l'entrée est un invité, on le supprime
        if ($cur_user['user_id'] == '1') {
            $query = array(
                'DELETE' => 'online',
                'WHERE' => 'ident=\'' . $MIB_DB->escape($cur_user['ident']) . '\''
            );
            $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
        } else {
            // Si l'entrée est plus vieille que "timeout_visit", on actualise last_visit pour l'utilisateur en question, puis on le supprime de la liste
            if ($cur_user['logged'] < ($now - $MIB_CONFIG['timeout_visit'])) {
                $query = array(
                    'UPDATE' => 'users',
                    'SET' => 'last_visit=' . $cur_user['logged'],
                    'WHERE' => 'id=' . $cur_user['user_id']
                );
                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

                $query = array(
                    'DELETE' => 'online',
                    'WHERE' => 'user_id=' . $cur_user['user_id']
                );
                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
            } else if ($cur_user['idle'] == '0') {
                $query = array(
                    'UPDATE' => 'online',
                    'SET' => 'idle=1',
                    'WHERE' => 'user_id=' . $cur_user['user_id']
                );
                $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
            }
        }
    }
}

/**
 * Charge un fichier/page si il exite
 *
 * @uses $MIB_PAGE
 *
 * @return string Fichier chargé
 */
function load_file()
{
    global $MIB_PAGE, $MIB_CONFIG;

    if (file_exists(MIB_PATH_SYS . 'files.php')) {
        ob_start();
        include MIB_PATH_SYS . 'files.php';
        $cur_file = ob_get_contents();
        ob_end_clean();
    }

    if (empty($cur_file))
        error(__('Impossible de charger le gestionnaire de fichiers/pages. Vérifiez si il est bien installé.'), __FILE__, __LINE__);
    else
        return $cur_file;
}

/**
 * Redirection de header optimisée pour Mibbo
 *
 * @param string $url URL de redirection
 *
 * @uses $MIB_CONFIG
 * @uses $MIB_PAGE
 * @uses $MIB_DB
 */
function mib_header($url = false, $code = 301)
{
    global $MIB_CONFIG, $MIB_PAGE, $MIB_DB;

    // Vide tous les buffers et stop le buffering
    while (@ob_end_clean()) ;

    if (empty($url)) {
        if ($MIB_PAGE['lang'] && $MIB_PAGE['base_url'] != $MIB_CONFIG['base_url'])
            $url = $MIB_CONFIG['base_url'] . '/' . $MIB_PAGE['lang'] . '/';
        else
            $url = $MIB_CONFIG['base_url'];
    }

    // Mise en conformité de l'url
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0 && strpos($url, '/') !== 0) {
        if (!empty($MIB_PAGE['lang']) && $MIB_PAGE['lang'] && $MIB_PAGE['base_url'] != $MIB_CONFIG['base_url'])
            $url = $MIB_CONFIG['base_url'] . '/' . $MIB_PAGE['lang'] . '/' . $url;
        else
            $url = $MIB_CONFIG['base_url'] . '/' . $url;
    }

    // var_dump($url);
    // Réécriture d'url
    //$full_url = mib_tpl_replace($full_url);

    // Fin de la transaction
    $MIB_DB->end_transaction();

    // Ferme la conection à la DB
    $MIB_DB->close();

    // Redirige
    header('Location: ' . $url, true, 301);
    exit;
}

/**
 * Remplace les balise de tempate, ainsi que les variables dynamiques
 *
 * @param string $str chaine de caractère à traiter
 * @param array $vars variables dynamique
 *
 * @return string $str
 */
function mib_tpl_replace($str, $vars = false)
{
    global $MIB_CONFIG, $MIB_PAGE, $MIB_USER, $MIB_URL, $MIB_URL_REWRITED;

    // Remplace {{tpl:MIB_THEME}} par le répertoire du theme en cours
    if (defined('MIB_MANAGE'))
        $str = str_replace('{{tpl:MIB_THEME}}', '../../theme/' . $MIB_PAGE['cur_theme'], $str);
    else if ($MIB_PAGE['base_url'] != $MIB_CONFIG['base_url'])
        $str = str_replace('{{tpl:MIB_THEME}}', '../theme/' . $MIB_PAGE['cur_theme'], $str);
    else
        $str = str_replace('{{tpl:MIB_THEME}}', 'theme/' . $MIB_PAGE['cur_theme'], $str);

    $MIB_PAGE['pattern']['tpl'] = '#{{tpl:(.*?)(\s(.*?))?\}}#is';
    while (preg_match($MIB_PAGE['pattern']['tpl'], $str, $cur_match)) {
        $cur_match_type = isset($cur_match[1]) ? mib_trim($cur_match[1]) : '';
        $cur_match_var = isset($cur_match[3]) ? mib_trim($cur_match[3]) : '';

        if ($cur_match_type == 'MIBconfig' && isset($MIB_CONFIG[$cur_match_var]))
            $str = str_replace($cur_match[0], $MIB_CONFIG[$cur_match_var], $str);
        else if ($cur_match_type == 'MIBpage' && isset($MIB_PAGE[$cur_match_var]))
            $str = str_replace($cur_match[0], $MIB_PAGE[$cur_match_var], $str);
        else if ($cur_match_type == 'MIBuser' && isset($MIB_USER[$cur_match_var]))
            $str = str_replace($cur_match[0], $MIB_USER[$cur_match_var], $str);
        else if ($vars && is_array($vars)) {
            if ($cur_match_type == 'MIBupper' && isset($vars[$cur_match_var]))
                $str = str_replace($cur_match[0], utf8_strtoupper($vars[$cur_match_var]), $str);
            else if ($cur_match_type == 'MIBlower' && isset($vars[$cur_match_var]))
                $str = str_replace($cur_match[0], utf8_strtolower($vars[$cur_match_var]), $str);
            else if ($cur_match_type == 'MIBucfirst' && isset($vars[$cur_match_var]))
                $str = str_replace($cur_match[0], utf8_ucfirst(utf8_strtolower($vars[$cur_match_var])), $str);
            else if (empty($cur_match_var) && isset($vars[$cur_match_type]))
                $str = str_replace($cur_match[0], $vars[$cur_match_type], $str);
        } else if (empty($cur_match_var) && defined($cur_match_type))
            $str = str_replace($cur_match[0], constant($cur_match_type), $str);

        $str = str_replace($cur_match[0], '', $str);
    }

    // Pseudo URL rewriting pour optimiser le référencement
    if ($MIB_URL_REWRITED && is_array($MIB_URL_REWRITED) && !defined('MIB_MANAGE')) {
        foreach ($MIB_URL_REWRITED as $cur_rewrited => $cur_url) {
            // Ne prend pas en compte les URL de base
            if ($cur_url != '/' && !array_key_exists($cur_url, $MIB_CONFIG['languages'])) {
                // Remplace uniquement les URLs pour la langue en cours
                if (current(explode('/', $cur_url)) == $MIB_PAGE['lang']) {
                    // enleve le début de la chaine avec son slash (Ex. 'fr/' ou 'en/')
                    $cur_url = substr_replace($cur_url, '', 0, (strlen($MIB_PAGE['lang']) + 1));
                    $cur_rewrited = substr_replace($cur_rewrited, '', 0, (strlen($MIB_PAGE['lang']) + 1));

                    // Remplace les liens href et action (utilise MIB_(*)_TEMP pour ne pas les remplacer 2 fois)
                    $str = str_replace('href="' . $cur_url, 'MIB_HREF_TEMP="' . $cur_rewrited, $str);
                    $str = str_replace('action="' . $cur_url, 'MIB_ACTION_TEMP="' . $cur_rewrited, $str);
                } // remplace pour les autres langues.
                else {
                    $str = str_replace('href="../' . $cur_url, 'MIB_HREF_TEMP="../' . $cur_rewrited, $str);
                    $str = str_replace('action="../' . $cur_url, 'MIB_ACTION_TEMP="../' . $cur_rewrited, $str);
                }
            }
        }

        // Remet en place les href et action
        $str = str_replace('MIB_HREF_TEMP', 'href', $str);
        $str = str_replace('MIB_ACTION_TEMP', 'action', $str);
    }

    return $str;
}

/**
 * Wrapper de PHP's mail()
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $reply_to_email
 * @param string $reply_to_name
 *
 */
function mib_mail($to, $subject, $message, $from_email = '', $from_name = '', $reply_to_email = '', $reply_to_name = '')
{
    global $MIB_CONFIG;

    // Default sender address
    $from_name = !empty($from_name) ? mib_trim(preg_replace('#[\n\r]+#s', '', $from_name)) : mib_trim(preg_replace('#[\n\r]+#s', '', $MIB_CONFIG['site_title']));
    $from_email = !empty($from_email) ? mib_trim(preg_replace('#[\n\r]+#s', '', $from_email)) : mib_trim(preg_replace('#[\n\r]+#s', '', $MIB_CONFIG['site_email']));

    // Do a little spring cleaning
    $to = mib_trim(preg_replace('#[\n\r]+#s', '', $to));
    $subject = mib_trim(preg_replace('#[\n\r]+#s', '', $subject));
    $from_email = mib_trim(preg_replace('#[\n\r:]+#s', '', $from_email));
    $from_name = mib_trim(preg_replace('#[\n\r:]+#s', '', str_replace('"', '', $from_name)));
    $reply_to_email = mib_trim(preg_replace('#[\n\r:]+#s', '', $reply_to_email));
    $reply_to_name = mib_trim(preg_replace('#[\n\r:]+#s', '', str_replace('"', '', $reply_to_name)));

    // Set up some headers to take advantage of UTF-8
    $from = "=?UTF-8?B?" . base64_encode($from_name) . "?=" . ' <' . $from_email . '>';
    $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

    $headers = 'From: ' . $from . "\r\n" . 'Date: ' . gmdate('r') . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-transfer-encoding: 8bit' . "\r\n" . 'Content-type: text/plain; charset=utf-8' . "\r\n" . 'X-Mailer: ' . $MIB_CONFIG['site_title'] . ' Mailer';

    // If we specified a reply-to email, we deal with it here
    if (!empty($reply_to_email)) {
        $reply_to = "=?UTF-8?B?" . base64_encode($reply_to_name) . "?=" . ' <' . $reply_to_email . '>';

        $headers .= "\r\n" . 'Reply-To: ' . $reply_to;
    }

    // Make sure all linebreaks are CRLF in message (and strip out any NULL bytes)
    $message = str_replace(array("\n", "\0"), array("\r\n", ''), mib_linebreaks($message));

    // Change the linebreaks used in the headers according to OS
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'MAC')
        $headers = str_replace("\r\n", "\r", $headers);
    else if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
        $headers = str_replace("\r\n", "\n", $headers);

    mail($to, $subject, $message, $headers);
}

/**
 * Supprime un fichier, ou un dossier et tout son contenu (algo récursif)
 *
 * @param {string} $path
 *
 * @return {bool}
 */
function mib_rmdirr($path)
{
    if (!file_exists($path))
        return false;

    // fichier
    if (is_file($path) || is_link($path))
        return unlink($path);

    // dossier
    $dir = dir($path);
    while (false !== $entry = $dir->read()) {
        // continue
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // recursion
        mib_rmdirr($path . DIRECTORY_SEPARATOR . $entry);
    }

    // ferme le dossier
    $dir->close();
    return rmdir($path);
}


/** Renvoi la liset des fichiers d'un répertoire ou false si le répeertoire n'existe pas  */
function mib_readdir($absPath)
{
    $list = [];

    if (empty($absPath) || !is_dir($absPath))
        return false;

    $d = dir($absPath);
    while (false !== ($filename = $d->read())) {
        if ($filename == '.' || $filename == '..')
            continue;
        $list[] = $filename;
    }

    $d->close();
    return $list;
}

/**
 * Supprime tous les tags HTML
 *
 * @return {string}
 */
function mib_strip_tags($str, $keep_BR = true)
{

    if ($keep_BR) $str = preg_replace('/<br\s*\/?>/i', '[BR]', $str);

    // ----- remove HTML TAGs -----
    $str = preg_replace('/<[^>]*>/', ' ', $str);

    // ----- remove control characters -----
    $str = str_replace("\r", '', $str);    // --- replace with empty space
    $str = str_replace("\n", ' ', $str);   // --- replace with space
    $str = str_replace("\t", ' ', $str);   // --- replace with space

    // ----- remove multiple spaces -----
    $str = str_replace('&nbsp;', ' ', $str);
    $str = mib_clean($str);

    if ($keep_BR) $str = str_replace('[BR]', '<br>', $str);

    return $str;
}

/**
 * Requette générique pour sélectionner une ligne d'une table
 *
 * @param string $table
 * @param string $row
 * @param string $key
 *
 * @return $row
 */
function mib_db_get_row_from_table($table, $row, $key = 'id')
{
    global $MIB_DB;

    if (empty($table) || empty($row)) error();

    // Requete pour obtenir les informations des résutats
    $query = array(
        'SELECT' => '*',
        'FROM' => $table,
        'LIMIT' => 1,
    );

    if (is_int($row))
        $query['WHERE'] = $key . '=' . $row;
    else
        $query['WHERE'] = $key . '=\'' . $MIB_DB->escape($row) . '\'';

    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
    if ($MIB_DB->num_rows($result))
        return $MIB_DB->fetch_assoc($result);
    else
        return false;
}

/**
 * Requette générique pour obtenir une liste d'une table renvoyé sous forme de tableau
 *
 * @param string $table
 * @param string $col
 * @param string $key
 *
 * @return {array}
 */
function mib_db_get_list_from_table($table, $col = false, $key = 'id')
{
    global $MIB_DB, $MIB_PAGE;

    if (empty($table)) error();

    $list = isset($MIB_PAGE['cache']['list-' . $table . $col . $key]) ? $MIB_PAGE['cache']['list-' . $table . $col . $key] : array();

    if (empty($list)) {
        // Requete pour obtenir les informations des résutats
        $query = array(
            'SELECT' => $key,
            'FROM' => $table,
            'ORDER BY' => $key . ' ASC'
        );
        if (!empty($col)) {
            $query['SELECT'] .= ',' . $col;
            $query['ORDER BY'] = $col . ' ASC';
        }
        $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);
        while ($cur_result = $MIB_DB->fetch_assoc($result)) {
            if (!empty($col))
                $list[$cur_result[$key]] = $cur_result[$col];
            else
                $list[] = $cur_result[$key];
        }
        $MIB_PAGE['cache']['list-' . $table . $col . $key] = $list; // mise en cache
    }

    return $list;
}


/**
 * Requette générique pour tester la présence de doublons
 *
 * @param string $table
 * @param string $col
 * @param string $key
 *
 * @return {bool}
 */
function mib_db_is_duplicate($table, $col, $value, $id = false)
{
    global $MIB_DB;

    if (empty($table) || empty($col) || empty($value)) error();

    // vérifie les doublons
    $query = array(
        'SELECT' => $col,
        'FROM' => $table,
        'WHERE' => 'UPPER(' . $col . ')=UPPER(\'' . $MIB_DB->escape($value) . '\')'
    );

    // version agressive qui prend en compte uniquement les caractères
    //$query['WHERE'] = '('.$query['WHERE'].' OR UPPER('.$col.')=UPPER(\''.$MIB_DB->escape(preg_replace('/[^\w]/u', '', $value)).'\'))';

    if ($id) $query['WHERE'] .= ' AND id!=' . intval($id);

    $result = $MIB_DB->query_build($query) or error(__FILE__, __LINE__);

    return $MIB_DB->num_rows($result) ? true : false;
}


/**
 * Id unique par page
 *
 * @param string $id
 *
 * @return {string}
 */
function mib_uid($id)
{
    global $MIB_PAGE;

    return $MIB_PAGE['uniqid'] . mib_trim($id);
}

/**
 * Test si une fonction particulière est bien disponible et activée
 *
 * @param string $function
 *
 * @return {bool}
 */
function mib_function_is_enabled($function)
{
    if (!is_callable($function)) return false;

    return !in_array($function, explode(',', ini_get('disable_functions')));
}

/**
 * Convertie une couleur Hexa en RGB
 *
 * @param string $color (couleur au format hexadecimal)
 * @param string $return (array|string)
 * @param string $separator
 *
 * @return {mixed}
 */
function mib_hex2RGB($color, $return = 'array', $separator = ',')
{
    $color = preg_replace("/[^0-9A-Fa-f]/", '', $color);
    $rgb = array();

    if (strlen($color) == 3)
        $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    else if (strlen($color) != 6)
        $color = '000000';

    $hexdec = hexdec($color);
    $rgb[] = 0xFF & ($hexdec >> 0x10); // red
    $rgb[] = 0xFF & ($hexdec >> 0x8); // green
    $rgb[] = 0xFF & $hexdec; // blue

    return $return == 'array' ? $rgb : implode($separator, $rgb);
}

/**
 * Inverse une couleur
 *
 * @param string $color (couleur au format hexadecimal)
 *
 * @return {string}
 */
function mib_hexinverse($color)
{
    $color = preg_replace("/[^0-9A-Fa-f]/", '', $color);
    $rgb = array();

    if (strlen($color) == 3)
        $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    else if (strlen($color) != 6)
        return '#000000';

    $hex = '';
    for ($x = 0; $x < 3; $x++) {
        $c = 255 - hexdec(substr($color, (2 * $x), 2));
        $c = ($c < 0) ? 0 : dechex($c);
        $hex .= (strlen($c) < 2) ? '0' . $c : $c;
    }
    return '#' . $hex;
}

/**
 * Détermine le code d'une réponse http
 *
 * @param {string} $http_response
 *
 * @return {int}
 */
function mib_get_http_response_code($http_response)
{
    return intval(substr($http_response, 0, 3));
}

/**
 * Renvois l'url absolu d'une url
 *
 * @param {string} $url
 * @param {bool} $lang
 *    Ajoute ou non la langue dans l'url
 *
 * @return {string} $absolute_url
 */
function mib_get_absolute_url($url = false, $lang = true)
{
    global $MIB_PAGE;

    // prefix avec MIB_BASE_URL (à moins qu'il n'y soit déjà)
    if (!empty($url) && strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0 && strpos($url, '//') !== 0) {
        if (!defined('MIB_INSTALL') && $lang && mib_locale_is_multilingual())
            $absolute_url = MIB_BASE_URL . '/' . $MIB_PAGE['lang'] . '/' . $url;
        else
            $absolute_url = MIB_BASE_URL . '/' . $url;
    } else if (empty($url)) {
        if (!defined('MIB_INSTALL') && $lang && mib_locale_is_multilingual())
            $absolute_url = MIB_BASE_URL . '/' . $MIB_PAGE['lang'] . '/';
        else
            $absolute_url = MIB_BASE_URL;
    } else
        $absolute_url = $url;

    return $absolute_url;
}

/**
 * Vérification du mime type réel car on ne peut pas se baser sur l'extension
 *
 * @param {array} $file
 */
function mib_mime_content_type($file)
{
    // PHP 5.3+
    if (function_exists('finfo_file') && function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE'))
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
    else
        return mime_content_type($file);
}

/**
 * Envois un fichier envoyé depuis un formulaire
 *
 * @param {array} $file ($_FILES)
 * @param {string} $where
 * @param {array} $options
 */
function mib_upload_file($file, $where, $options = array())
{
    $options = array_merge(array(
        'type_allowed' => '*', // type mime autorisés séparés par des virgules
        'max_filesize_ko' => false, // poids max en kilo-octet (ko)
    ), $options);

    $errors = mib_core_function('MIB_isUploaded', $file);
    if (empty($errors)) {

        // vérification du poids
        if ($options['max_filesize_ko'] && $file['size'] > $options['max_filesize_ko'] * 1000)
            mib_error_set(__('Le fichier est trop lourd') . ' (' . mib_bytestohuman($options['max_filesize_ko'] * 1024) . ' max).', 'max_filesize_ko');
        // vérification du mime type
        else if ($options['type_allowed'] != '*') {
            $type_allowed = explode(',', $options['type_allowed']);
            $type_file = mib_mime_content_type($file['tmp_name']);
            $type_match = false;
            foreach ($type_allowed as $t) {
                if ($t == $type_file) {
                    $type_match = true;
                    break;
                }
            }

            if (!$type_match)
                mib_error_set(__('Le format du fichier n\'est pas bon.'), 'type_allowed');
        }
    } else
        mib_error_set(current($errors), 'upload_file');

    // aucune erreur
    if (!mib_error_exists()) {
        // création automatique du rep de destination si il n'existe pas
        $dir = explode('/', $where);
        array_pop($dir);
        if ($dir[0] . '/' != MIB_ROOT) array_unshift($dir, mib_trim(MIB_ROOT, '/'));
        $dir = implode('/', $dir);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        // on essaye de déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $where))
            mib_error_set(__('L\'envoi a échoué. Déplacement du fichier impossible. Veuillez contacter un administrateur pour vérifier les droits en écriture.'), 'move_uploaded_file');
    }

    return !mib_error_exists();
}

/**
 * Détermine si un clef de tableau existe dans un tableau multi-dimensionnel
 *
 * @param mixed $needle
 * @param mixed $haystack
 *
 * @return {bool}
 */
function mib_multi_array_key_exists($needle, $haystack)
{
    foreach ($haystack as $k => $v) {
        if ($needle == $k)
            return true;

        if (is_array($v)) {
            if (mib_multi_array_key_exists($needle, $v) == true)
                return true;
        }
    }

    return false;
}

/**
 * Retourne l'extension du fichier pour le type d'image
 *
 * @param int $imagetype Une des constantes IMAGETYPE_XXX.
 * @param bool $include_dot Si l'on doit ajouter un point à l'extension ou non.
 *
 * @return {string}
 */
function mib_image_type_to_extension($imagetype, $include_dot = true)
{
    $dot = $include_dot ? '.' : '';

    switch ($imagetype) {
        case IMAGETYPE_GIF        :
            return $dot . 'gif';
        case IMAGETYPE_JPEG        :
            return $dot . 'jpg';
        case IMAGETYPE_PNG        :
            return $dot . 'png';
        case IMAGETYPE_SWF        :
            return $dot . 'swf';
        case IMAGETYPE_PSD        :
            return $dot . 'psd';
        case IMAGETYPE_BMP        :
            return $dot . 'bmp';
        case IMAGETYPE_TIFF_II    :
            return $dot . 'tiff';
        case IMAGETYPE_TIFF_MM    :
            return $dot . 'tiff';
        case IMAGETYPE_JPC        :
            return $dot . 'jpc';
        case IMAGETYPE_JP2        :
            return $dot . 'jp2';
        case IMAGETYPE_JPX        :
            return $dot . 'jpf';
        case IMAGETYPE_JB2        :
            return $dot . 'jb2';
        case IMAGETYPE_SWC        :
            return $dot . 'swc';
        case IMAGETYPE_IFF        :
            return $dot . 'aiff';
        case IMAGETYPE_WBMP        :
            return $dot . 'wbmp';
        case IMAGETYPE_XBM        :
            return $dot . 'xbm';
        case IMAGETYPE_ICO        :
            return $dot . 'ico';
        default                    :
            return null;
    }
}

/**
 * Traitement d'une image
 *
 * @param {string} $filename // image concerné (path ou url)
 * @param {array} $dest // options de l'image de destination
 *
 * @return {mixed}
 */
function mib_image($filename, $dest = array())
{

    $dest = array_merge(array(
        'type' => false,                // type de l'image de sortie (ex. IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, ...) cf http://php.net/manual/function.image-type-to-mime-type.php
        'width' => false,                // largeur
        'height' => false,                // hauteur
        'resize' => 'max',                // type de redimensionnement si necessaire (crop|max|min)
        'background' => '#FFF',                // couleur de fond si il y avait une transparence dans l'image source et pas dans l'image de destination (ex. PNG -> JPG)
        'position' => '50% 50%',            // position de la découpe si il y a crop (horizontal + vertical)
        'quality' => 100,                    // qualitée de compression de l'image
        'maxsize' => 2000,                // dimmension max d'une image en pixel pour éviter de surcharger le serveur
        'interlace' => 0,                    // active ou désactive l'entrelacement
        'lastmod' => false,                // date de dernière modification du fichier source $filename
        'return' => false,                // false par défaut qui affiche l'image directement avant un exit, si besoin peut retourner différent format d'image (image = ressource de l'image|jpg|png|gif|bmp)
    ), (array)$dest);

    $supported_type = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

    $source = array();

    $image_info = @getimagesize($filename);
    if (!$image_info) {
        if ($dest['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : impossible de lire les infos de l'image
    list($source['width'], $source['height'], $source['type']) = $image_info;
    if (!$dest['type']) $dest['type'] = $source['type'];
    if (!in_array($source['type'], $supported_type) || !in_array($dest['type'], $supported_type)) {
        if ($dest['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : format non supporté

    if (!$dest['lastmod']) $dest['lastmod'] = filemtime($filename);

    // id unique pour la mise en cache
    $uid = $filename . $dest['width'] . $dest['height'] . $dest['resize'] . $dest['background'] . $dest['position'] . $dest['quality'] . $dest['interlace'] . $dest['lastmod'] . mib_image_type_to_extension($dest['type']);
    if ($cache = mib_public_cache($uid, $dest['return'])) return $cache;

    $source['image'] = ImageCreateFromString(file_get_contents($filename));
    if (is_resource($source['image']) !== true) {
        if ($dest['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : impossible de charger l'image

    // pas de redimmentionnement précisé on attribut les dimmensions de l'image source
    if (!$dest['width']) $dest['width'] = $source['width'];
    if (!$dest['height']) $dest['height'] = $source['height'];

    // vérification des valeurs
    if ($dest['width'] > $dest['maxsize']) $dest['width'] = $dest['maxsize'];
    if ($dest['height'] > $dest['maxsize']) $dest['height'] = $dest['maxsize'];
    if ($dest['width'] < 1 || $dest['height'] < 1) {
        if ($dest['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : une image doit faire au minimum 1px de coté !

    // ratio de redimensionnement
    $dest['size']['ratio'] = array(
        'width' => $dest['width'] / $source['width'],
        'height' => $dest['height'] / $source['height'],
        'render' => 1,
    );
    $dest['size']['x'] = 0;
    $dest['size']['y'] = 0;

    // découpage
    if ($dest['resize'] == 'crop') {

        // position de la découpe
        $dest['position'] = mib_clean(str_replace('%', '', $dest['position']));
        if (strpos($dest['position'], ' ') === false) { // pas de " " pour séparer la position horizontal et vertical
            if (is_numeric($dest['position'])) {
                $dest['position'] = array(
                    'x' => intval($dest['position']),
                    'y' => intval($dest['position']),
                );
            } else
                $dest['position'] = array('x' => 50, 'y' => 50); // centrage par défaut
        } else {
            $positions = explode(' ', $dest['position']);
            if (count($positions) == 1 && is_numeric($positions[0])) {
                $dest['position'] = array(
                    'x' => intval($positions[0]),
                    'y' => intval($positions[0]),
                );
            } else if (count($positions) == 2 && is_numeric($positions[0]) && is_numeric($positions[1])) {
                $dest['position'] = array(
                    'x' => intval($positions[0]),
                    'y' => intval($positions[1]),
                );
            } else
                $dest['position'] = array('x' => 50, 'y' => 50); // centrage par défaut
        }
        if ($dest['position']['x'] > 100) $dest['position']['x'] = 100;
        if ($dest['position']['y'] > 100) $dest['position']['y'] = 100;

        // image plus haute que large
        if ($dest['size']['ratio']['width'] > $dest['size']['ratio']['height']) {
            $dest['size']['ratio']['render'] = $dest['size']['ratio']['width'];
            // positionne la découpe
            if ($dest['position']['y'] > 0)
                $dest['size']['y'] = (($source['height'] * $dest['size']['ratio']['render'] / (100 / $dest['position']['y'])) - ($dest['height'] / (100 / $dest['position']['y'])));
        } else {
            $dest['size']['ratio']['render'] = $dest['size']['ratio']['height'];

            // positionne la découpe
            if ($dest['position']['x'] > 0)
                $dest['size']['x'] = (($source['width'] * $dest['size']['ratio']['render'] / (100 / $dest['position']['x'])) - ($dest['width'] / (100 / $dest['position']['x'])));
        }
    } else if ($dest['resize'] == 'min') // garde le plus grand pour la taille minimum
        $dest['size']['ratio']['render'] = $dest['size']['ratio']['width'] > $dest['size']['ratio']['height'] ? $dest['size']['ratio']['width'] : $dest['size']['ratio']['height'];
    else { // garde par défaut le ratio le plus petit pour ne pas dépasser la taille demandée

        // on ne redimensionne pas si l'image d'origine est plus petite que demandée
        if ($dest['size']['ratio']['height'] > 1 && $dest['size']['ratio']['width'] < 1)
            $dest['size']['ratio']['render'] = $dest['size']['ratio']['width'];
        else if ($dest['size']['ratio']['width'] > 1 && $dest['size']['ratio']['height'] < 1)
            $dest['size']['ratio']['render'] = $dest['size']['ratio']['height'];
        else
            $dest['size']['ratio']['render'] = $dest['size']['ratio']['width'] <= $dest['size']['ratio']['height'] ? $dest['size']['ratio']['width'] : $dest['size']['ratio']['height'];
    }

    // création de l'image à la bonne dimmension
    $resize = array(
        'width' => round($source['width'] * $dest['size']['ratio']['render']),
        'height' => round($source['height'] * $dest['size']['ratio']['render']),
    );
    $resize['image'] = imagecreatetruecolor($resize['width'], $resize['height']);
    imageinterlace($resize['image'], $dest['interlace']);

    // prépare la couleur de fond
    $dest['background_rgb'] = array(
        'r' => 255,
        'g' => 255,
        'b' => 255,
    );
    list($dest['background_rgb']['r'], $dest['background_rgb']['g'], $dest['background_rgb']['b']) = mib_hex2RGB($dest['background']);

    switch ($dest['type']) {
        case IMAGETYPE_GIF:
            $background = imagecolorallocatealpha($resize['image'], $dest['background_rgb']['r'], $dest['background_rgb']['g'], $dest['background_rgb']['b'], 1);
            imagecolortransparent($resize['image'], $background);
            imagefill($resize['image'], 0, 0, $background);
            imagesavealpha($resize['image'], true);
            break;
        case IMAGETYPE_PNG:
            imagealphablending($resize['image'], false);
            imagesavealpha($resize['image'], true);
            break;
        case IMAGETYPE_JPEG:
            $background = imagecolorallocate($resize['image'], $dest['background_rgb']['r'], $dest['background_rgb']['g'], $dest['background_rgb']['b']);
            imagefilledrectangle($resize['image'], 0, 0, $resize['width'], $resize['height'], $background);
            break;
    }

    imagecopyresampled(
        $resize['image'],
        $source['image'],
        0,
        0,
        0,
        0,
        $resize['width'],
        $resize['height'],
        $source['width'],
        $source['height']
    );

    // découpage
    if ($dest['resize'] == 'crop') {
        $dest['image'] = imagecreatetruecolor($dest['width'], $dest['height']);
        imageinterlace($dest['image'], $dest['interlace']);

        switch ($dest['type']) {
            case IMAGETYPE_GIF:
                $background = imagecolorallocatealpha($dest['image'], $dest['background_rgb']['r'], $dest['background_rgb']['g'], $dest['background_rgb']['b'], 1);
                imagecolortransparent($dest['image'], $background);
                imagefill($dest['image'], 0, 0, $background);
                imagesavealpha($dest['image'], true);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($dest['image'], false);
                imagesavealpha($dest['image'], true);
                break;
            case IMAGETYPE_JPEG:
                $background = imagecolorallocate($dest['image'], $dest['background_rgb']['r'], $dest['background_rgb']['g'], $dest['background_rgb']['b']);
                imagefilledrectangle($dest['image'], 0, 0, $dest['width'], $dest['height'], $background);
                break;
        }

        imagecopy($dest['image'], $resize['image'], 0, 0, $dest['size']['x'], $dest['size']['y'], $resize['width'], $resize['height']);
    } // le redimmensionnement à déjà été fait si il n'y a pas de découpage
    else {
        $dest['image'] = $resize['image'];
    }

    @imagedestroy($source['image']); // libère de la mémoire

    ob_start(); // start le buffering

    switch ($dest['type']) {
        case IMAGETYPE_GIF:
            @imagegif($dest['image']);
            break;
        case IMAGETYPE_PNG:
            @imagepng($dest['image'], NULL, round(abs((9 * $dest['quality'] / 100) - 9))); // Convertit la qualité (0->100) en qualité png (9->0)
            break;
        case IMAGETYPE_JPEG:
            @imagejpeg($dest['image'], NULL, $dest['quality']);
            break;
    }

    @imagedestroy($resize['image']); // libère de la mémoire
    @imagedestroy($dest['image']); // libère de la mémoire

    $contents = ob_get_clean(); // stop le buffering

    // mise en cache
    mib_public_cache_save($uid, $contents);

    // renvois/affiche l'image
    if ($dest['return'])
        return $contents;
    else {
        // vide tous les buffers et stop le buffering
        while (@ob_end_clean()) ;

        // Si la connection à la DB avait été établie on la ferme
        if (isset($GLOBALS['MIB_DB'])) $GLOBALS['MIB_DB']->close();

        header('Content-Type: ' . image_type_to_mime_type($dest['type']));
        header('Content-Length: ' . strlen($contents));

        exit($contents);
    }
}

/**
 * Création d'une image placeholder
 *
 * @param {int} $width
 * @param {int} $height
 * @param {array} $placeholder // options du placeholder
 *
 * @return {mixed}
 */
function mib_image_placeholder($width, $height, $placeholder = array())
{

    $placeholder = array_merge(array(
        'type' => IMAGETYPE_JPEG,        // type de l'image de sortie (ex. IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF) cf http://php.net/manual/function.image-type-to-mime-type.php
        'background' => '#DDD',                // couleur de fond
        'text' => false,                // texte de remplacement
        'color' => '#333',                // couleur du texte
        'quality' => 100,                    // qualitée de compression de l'image
        'maxsize' => 2000,                // dimmension max d'une image en pixel pour éviter de surcharger le serveur
        'interlace' => 0,                    // active ou désactive l'entrelacement
        'return' => false,                // false par défaut qui affiche l'image directement avant un exit, si besoin peut retourner différent format d'image (image = ressource de l'image|jpg|png|gif|bmp)
    ), (array)$placeholder);

    $supported_type = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

    if (!in_array($placeholder['type'], $supported_type)) {
        if ($placeholder['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : format non supporté

    // vérification des valeurs
    if ($width > $placeholder['maxsize']) $width = $placeholder['maxsize'];
    if ($height > $placeholder['maxsize']) $height = $placeholder['maxsize'];
    if ($width < 1 || $height < 1) {
        if ($placeholder['return']) {
            return false;
        } else {
            error(__FILE__, __LINE__);
        }
    } // ERREUR : une image doit faire au minimum 1px de coté !

    // id unique pour la mise en cache
    $uid = 'placeholder' . $width . $height . $placeholder['background'] . $placeholder['text'] . $placeholder['color'] . $placeholder['quality'] . $placeholder['interlace'] . mib_image_type_to_extension($placeholder['type']);

    if ($cache = mib_public_cache($uid, $placeholder['return'])) return $cache;

    // création de l'image
    $image = imagecreate($width, $height);

    // ajoute la couleur de fond
    list($r, $g, $b) = mib_hex2RGB($placeholder['background']);
    imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, $r, $g, $b));

    $s = 4; // taille de la police max si on à la place
    $t = $placeholder['text'] ? mib_strtoascii($placeholder['text']) : $width . 'x' . $height; // texte
    while ($s > 0 && ($width - imagefontwidth($s) * strlen($t) < $s * 2 || $height - imagefontheight($s) < $s * 2))
        $s--;

    // écrit le text au centre seulement si c'est possible
    if ($s > 0) {
        list($r, $g, $b) = mib_hex2RGB($placeholder['color']); // couleur du texte
        imagestring($image, $s, ($width - strlen($t) * imagefontwidth($s)) / 2, ($height - imagefontheight($s)) / 2, $t, imagecolorallocate($image, $r, $g, $b));
    }

    ob_start(); // start le buffering

    switch ($placeholder['type']) {
        case IMAGETYPE_GIF:
            @imagegif($image);
            break;
        case IMAGETYPE_PNG:
            @imagepng($image, NULL, round(abs((9 * $placeholder['quality'] / 100) - 9))); // Convertit la qualité (0->100) en qualité png (9->0)
            break;
        case IMAGETYPE_JPEG:
            @imagejpeg($image, NULL, $placeholder['quality']);
            break;
    }

    @imagedestroy($image); // libère de la mémoire

    $contents = ob_get_clean(); // stop le buffering

    // mise en cache
    mib_public_cache_save($uid, $contents);

    // renvois/affiche l'image
    if ($placeholder['return'])
        return $contents;
    else {
        // vide tous les buffers et stop le buffering
        while (@ob_end_clean()) ;

        // Si la connection à la DB avait été établie on la ferme
        if (isset($GLOBALS['MIB_DB'])) $GLOBALS['MIB_DB']->close();

        header('Content-Type: ' . image_type_to_mime_type($placeholder['type']));
        header('Content-Length: ' . strlen($contents));

        exit($contents);
    }
}

/**
 * Redirige vers/renvois un fichier de cache
 *
 * @param {string} $uid (necessite l'extension du fichier)
 * @param {bool} $return
 *
 * @return {mixed}
 */
function mib_public_cache($uid, $return = false)
{
    global $MIB_CONFIG;

    $ext = strtolower(strrchr($uid, '.'));
    $hash = mib_hash($uid);
    $path = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . $ext;

    if (file_exists(MIB_PUBLIC_CACHE_DIR . $path)) {
        if ($return)
            return file_get_contents(MIB_PUBLIC_CACHE_DIR . $path);
        else {
            $last_modified_time = filemtime(MIB_PUBLIC_CACHE_DIR . $path);

            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified_time) . ' GMT');
            header('Etag: ' . $hash);
            header('Cache-Control: public');

            if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && mib_trim($_SERVER['HTTP_IF_NONE_MATCH']) == $hash )) {
                header('HTTP/1.1 304 Not Modified');
                exit;
            }

            $url = $MIB_CONFIG['base_url'] . '/' . mib_trim(MIB_PUBLIC_CACHE_DIR, './') . '/' . $path;
            header('location: ' . $url);
            exit;
        }
    }
}

/**
 * Mise en cache du contenu d'un fichier
 *
 * @param {string} $uid (necessite l'extension du fichier)
 * @param {mixed} $data
 */
function mib_public_cache_save($uid, $data)
{

    $ext = strtolower(strrchr($uid, '.'));
    $hash = mib_hash($uid);
    $path = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . $ext;

    // création automatique du rep de destination si il n'existe pas
    $dir = explode('/', $path);
    array_pop($dir);
    array_unshift($dir, mib_trim(MIB_PUBLIC_CACHE_DIR, './'));
    $dir = implode('/', $dir);
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    file_put_contents(MIB_PUBLIC_CACHE_DIR . $path, $data);
}

/**
 * Anti-spam pour les formulaires, necessite mootools
 *
 * @param {string} $id du formulaire
 */
function mib_form_nospam($id)
{
    ?>
    <input type="hidden" id="<?php echo $id . mib_hash(mib_remote_address()); ?>"
           name="ip-<?php echo mib_hash(mib_remote_address()); ?>" value="1">
    <script>
        window.addEvent('domready', function () {
            $('<?php echo $id; ?>').addEvent('submit', function (e) {
                $('<?php echo $id . mib_hash(mib_remote_address()); ?>').destroy();
                new Element('input', {
                    'type': 'hidden',
                    'name': 'ua-<?php echo mib_hash(mib_remote_address() . $_SERVER['HTTP_USER_AGENT']); ?>',
                    'value': navigator.userAgent,
                }).inject('<?php echo $id; ?>');
            });
        });
    </script>
    <?php
}

/**
 * Vérification du formualire anti-spam
 *
 * @return {bool}
 */
function mib_form_is_spam()
{
    if (isset($_POST['ip-' . mib_hash(mib_remote_address())])) // champ anti-spam en trop
        return 1;
    else if (empty($_POST['ua-' . mib_hash(mib_remote_address() . $_SERVER['HTTP_USER_AGENT'])])) // champ anti-spam manquant
        return 2;
    else if ($_POST['ua-' . mib_hash(mib_remote_address() . $_SERVER['HTTP_USER_AGENT'])] != $_SERVER['HTTP_USER_AGENT']) // champ anti-spam invalide
        return 3;
    else
        return false;
}

function mib_ajax_dump($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}