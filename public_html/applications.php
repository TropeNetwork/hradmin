<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_APPLICATIONS)) {
    header("Location: noright.php");
    exit;
}

$apps = $objRightsAdminPerm->getApplications();
  

$tpl->addBlockfile('contentmain', 'applications', 'applicationlist.html');
$tpl->setCurrentBlock('applicationlist');
foreach($apps as $app) {
    $tpl->setVariable(array('name'          => '<a href="application.php?edit='.$app['application_id'].'">'.$app['name'].'</a>',
                            'id'            => $app['application_id'],
                            'description'   => $app['description'],
                            'define'        => $app['define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $rightcontent = '<br><a href="application.php">Neue Anwendung</a>';
}
$tpl->setVariable('contentright',$rightcontent);

$tpl->show();
?>