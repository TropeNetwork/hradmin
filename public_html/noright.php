<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

$tpl->setVariable('contentright','<br>');
$tpl->setVariable('contentmain','<b>Sie haben keine ausreichenden Rechte auf diese Seite!</b>
    <br>Wenden Sie sich an den Administrator!');
$tpl->setVariable('title',"Fehler");
$tpl->show();
?>