<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';


if (!checkRights(HRADMIN_RIGHT_GROUPS)) {
    header("Location: noright.php");
    exit;
}


$groups = $objRightsAdminPerm->getGroups(array('with_rights'=>true ));

$tpl->addBlockfile('contentmain', 'groups', 'grouplist.html');
$tpl->setCurrentBlock('grouplist');
foreach($groups as $group) {
    $tpl->setVariable(array('name'          => '<a href="group.php?edit='.$group['group_id'].'">'.$group['name'].'</a>',
                            'description'   => $group['description'],
                            'define'        => $group['group_define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<a href="group.php" title="Neue Gruppe"><img src="/images/new.png" alt="Neue Gruppe" /></a>';
}
$tpl->setVariable('contentright',$right);
$tpl->setVariable('title',"Gruppen");
$tpl->show();
?>