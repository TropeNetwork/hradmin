<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

$tpl->setVariable('contentright','<br>');
$tpl->setVariable('contentmain','<b>'._("You have no right for this site!").'</b>
    <br>'._("Contact your administrator!"));
$tpl->setVariable('title',_("Error"));
$tpl->show();
?>