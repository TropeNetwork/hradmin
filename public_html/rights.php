<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_RIGHTS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();
checkArea();

$rights = $objRightsAdminPerm->getRights(array('where_application_id' => $current_application_id,
                                               'where_area_id'        => $current_area_id));
  
$tpl->addBlockfile('contentmain', 'rights', 'rightlist.html');
$tpl->setCurrentBlock('rightlist');

foreach($rights as $right) {
    $tpl->setVariable(array('name'          => '<a href="right.php?'.getAppIdParameter().'&'.getAreaIdParameter().'&edit='.$right['right_id'].'">'.$right['name'].'</a>',
                            'description'   => $right['description'],
                            'define'        => $right['define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $rightcontent = '<a href="right.php?'.getAppIdParameter().'&'.getAreaIdParameter().'" title="'._("New right").'"><img src="/images/new.png" alt="'._("New right").'" /></a>';
}
$tpl->setVariable('contentright',$rightcontent);
$tpl->setVariable('title',_("Right"));
$tpl->show();
?>