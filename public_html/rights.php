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
 *   $Id: rights.php,v 1.7 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_RIGHTS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();
checkArea();

$rights = $admin->perm->getRights(array(
    'fields'  => array(
        'right_define_name',
        'right_id',),
    'filters' => array(
        'application_id'=> $current_application_id,
        'area_id'       => $current_area_id))
);
$tpl->addBlockfile('contentmain', 'rights', 'rightlist.html');
$tpl->setCurrentBlock('rightlist');

foreach($rights as $right) {
    $current_right_id = $right['right_id'];
    $trans = $admin->perm->getTranslations(array('filters'=>array(
        'section_id'    => $current_right_id,
        'section_type'  => LIVEUSER_SECTION_RIGHT, 
        'language_id'   => 0
    )));
    $tpl->setVariable(array(
        'name'          => '<a href="right.php?'.getAppIdParameter().getAreaIdParameter().getRightIdParameter().'edit=1"><img src="/images/edit.png" alt="'._("Edit").'" /> '.$trans[0]['name'].'</a>',
        'description'   => $trans[0]['description'],
        'define'        => $right['right_define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $rightcontent = '<a href="right.php?'.getAppIdParameter().getAreaIdParameter().'" title="'._("New right").'"><img src="/images/new.png" alt="'._("New right").'" /></a>';
}
$tpl->setVariable('contentright',$rightcontent);
$tpl->setVariable('title',_("Right"));
$tpl->show();
?>