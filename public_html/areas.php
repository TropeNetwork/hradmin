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
 *   $Id: areas.php,v 1.6 2005/04/21 14:11:37 cbleek Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_AREAS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();

$areas = $admin->perm->getAreas(array('fields' => array('area_id', 'name', 'description','area_define_name'),
                                      'filter' => array('application_id' => $_GET['app_id'] )));

$tpl->addBlockfile('contentmain', 'areas', 'arealist.html');
$tpl->setCurrentBlock('arealist');
foreach($areas as $area) {
    $tpl->setVariable(array('name'        => '<a href="area.php?edit='.$area['area_id'].'&app_id='.$current_application_id.'">'.$area['name'].'</a>',
                            'description' => $area['description'],
                            'id'          => $area['area_id'],
                            'define'      => $area['area_define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<a href="area.php?app_id='.$current_application_id.'" title="'._("New area").'"><img src="/images/new.png" alt="'._("New Area").'" /></a>';
}
$tpl->setVariable('contentright',$right);
$tpl->setVariable('title',_("Areas"));
$tpl->show();
?>