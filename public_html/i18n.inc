<?php

setlocale(LC_MESSAGES, 'de_DE');
$domain = 'messages';
bindtextdomain($domain, dirname(__FILE__).'/i18n');
echo textdomain($domain);

?>