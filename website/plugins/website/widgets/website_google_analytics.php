<?php

/*---

	Copyright (c) 2010-2015 Mibbo, http://mibbo.net

	This file is part of the Mibbo package which is subject to an opensource
	GPL license. You are allowed to customize the code for your own needs,
	but must keep your changes under GPL

	Author: 2BO&CO SAS, RCS 518 123 807, France, http://2boandco.com

---*/

defined('MIB') or exit; // Assurons nous que le script n'est pas executé "directement"

// uniquement si on est en prod et que ce n'est pas l'éditeur WYSIWYG qui charge la page
if ( defined('MIB_PROD_SERVER') && !isset($_GET['WYSIWYG']) ) {
?>
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', 'UA-XXXXXXXXX', 'auto');
	ga('send', 'pageview');
</script>
<?php
}
