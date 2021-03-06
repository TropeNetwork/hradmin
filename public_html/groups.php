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
 *   $Id: groups.php,v 1.6 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';


if (!checkRights(HRADMIN_RIGHT_GROUPS)) {
    header("Location: noright.php");
    exit;
}


$groups = $admin->perm->getGroups(array('with_rights'=>true,
                                        'fields'     => array("group_define_name", "description", "name", "group_id")));

$tpl->addBlockfile('contentmain', 'groups', 'grouplist.html');
$tpl->setCurrentBlock('grouplist');
foreach($groups as $group) {
    $tpl->setVariable(array('name'          => '<a href="group.php?edit=1&group_id='.$group['group_id'].'">'.$group['name'].'</a>',
                            'description'   => $group['description'],
                            'define'        => $group['group_define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<a href="group.php" title="'._("New Group").'"><img src="/images/new.png" alt="'._("New Group").'" /></a>';
}
$tpl->setVariable('contentright',$right);
$tpl->setVariable('title',_("Groups"));
$tpl->show();
?>