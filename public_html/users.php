<?php

require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_USERS)) {
    header("Location: noright.php");
    exit;
}

$tpl->addBlockfile('contentmain', 'users', 'userlist.html');
$tpl->setCurrentBlock('userlist');
$users = $objRightsAdminAuth->getUsers(null,'name');
foreach($users as $user) {
    $tpl->setVariable(array('login'   => '<a href="user.php?edit='.$user['auth_user_id'].'">'.$user['handle'].'</a>',
                            'name'     => $user['name'],
                            'email'    => $user['email']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<br><a href="user.php">Neuer Benutzer</a>';
}
$tpl->setVariable('contentright',$right);

$tpl->show();
?>