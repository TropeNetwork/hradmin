<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

$tpl->setVariable('contentright','<br>');
$tpl->setVariable('title',_("Mainsite"));
$tpl->show();
?>