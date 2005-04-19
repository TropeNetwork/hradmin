<?php
/*
 *   Copyright (C) 2004  Gerrit Goetsch
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *   Author: Gerrit Goetsch <goetsch@cross-solution.de>
 *   
 *   $Id: users.php,v 1.5 2005/04/19 16:57:24 cbleek Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_USERS)) {
    header("Location: noright.php");
    exit;
}

$tpl->addBlockfile('contentmain', 'users', 'userlist.html');
$tpl->setCurrentBlock('userlist');
$users = $admin->getUsers('auth');
foreach($users as $user) {
    $tpl->setVariable(array('login'   => '<a href="user.php?edit='.$user['auth_user_id'].'">'.$user['handle'].'</a>',
                            'name'     => $user['name'],
                            'email'    => $user['email']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<a href="user.php" title="'._("New user").'"><img src="/images/new.png" alt="'._("New user").'" /></a>';
}
$tpl->setVariable('contentright',$right);
$tpl->setVariable('title',_("User"));
$tpl->show();
?>