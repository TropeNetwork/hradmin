<?php
require_once 'HTML/Template/Sigma.php';
$tpl =& new HTML_Template_Sigma(dirname(__FILE__).'/../templates/');
$tpl->loadTemplateFile('template.html');
?>