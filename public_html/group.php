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
 *   $Id: group.php,v 1.5 2005/04/19 16:57:23 cbleek Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

define ('HRADMIN_LEVEL_0',_("None"));
define ('HRADMIN_LEVEL_1',_("Read"));
define ('HRADMIN_LEVEL_2',_("Write"));
define ('HRADMIN_LEVEL_3',_("Delete"));

if (!checkRights(HRADMIN_RIGHT_GROUPS)) {
    header("Location: noright.php");
    exit;
}

$form = new HTML_QuickForm('edit','POST');
                  
$form->addElement('text', 'name', _("Name"));
$tpl->setVariable(array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('text', 'description', _("Description"));
$tpl->setVariable(array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('text', 'define', _("Define name"));
$tpl->setVariable(array('maxlength'=>'15',
                  'class'=>'formFieldLong'));

if ($edit) {
    if ($level>1) {
        $form->addElement('submit', 'submit', _("Save"));
    }
    if ($level>2) {
        $form->addElement('submit', 'delete', _("Delete"));
    }
    
    $groups = $admin->perm->getGroups(array('group_id' => $_GET['edit'],
                                            'fields'   => array('group_id','description','name','group_define_name')));
                                            
                                            var_dump::display($groups);

    $defaultValues['name']          = $groups[$_GET['edit']]['name'];
    $defaultValues['define']        = $groups[$_GET['edit']]['group_define_name'];
    $defaultValues['description']   = $groups[$_GET['edit']]['description'];
    $form->addElement('hidden', 'id', $_GET['edit']);
    
    $form->setDefaults($defaultValues);
} else {
    $form->addElement('submit', 'submit', _("Create"));
}
$tpl->addBlockfile('contentmain', 'group', 'editgroup.html');


// right stuff
$tpl->setCurrentBlock('rightlist');
$apps = $admin->perm->getApplications();
$groupRights = getGroupRight($_GET['edit']);
foreach($apps as $app) {
    $areas = $admin->perm->getAreas(array('application_id' => $app['application_id'] ));
    foreach($areas as $area) {
        $rights = $admin->perm->getRights(array('application_id' => $app['application_id'],
                                                       'area_id' => $area['area_id'] ));
        foreach($rights as $right) {
            $tpl->setVariable(array('application'   => $app['name'],
                                    'area'          => $area['name'],
                                    'right'         => $right['name'],
                                    'right_box'     => getRightLevelBox($right['right_id'],$groupRights)));
            $tpl->parseCurrentBlock();
        }
    }
}

$form->addRule('name', _("Name is required!"), 'required');
if ($level < 2) {
    $form->freeze();
}

if ($form->validate()) {
    if ($delete) {
        $objRightsAdminPerm->removeGroup($form->exportValue('id'));
        header("Location: groups.php");
    } elseif ($edit && $level>1) {
        $objRightsAdminPerm->updateGroup(
            $form->exportValue('id'),
            $form->exportValue("name"),
            $form->exportValue("description"), 
            $form->exportValue("define"),
            array(
                'is_active' => 'Y'
            )
        );
        setGroupRights($form->exportValue('id'),$_POST['rights']);
        header("Location: groups.php");
    } elseif ($level>2) {  
        $group_id = $objRightsAdminPerm->addGroup(
            $form->exportValue("name"),
            $form->exportValue("description"),            
            $form->exportValue("define"),
            array(
                'is_active' => 'Y'
            )
        );
        if (DB::isError($group_id)) {
            var_dump($group_id);
        } else {
            setGroupRights($group_id,$_POST['rights']);
            header("Location: groups.php");
        }
    }
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITStatic($tpl);
$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            

$form->accept($renderer);


$tpl->setVariable('title',_("Group"));

$tpl->show();

function getRightLevelBox($right_id, $rights = array()) 
{
    global $level;
    $right = (int) hasGroupRight($right_id, $rights);
    if ($level>1){
        $select = '<select name="rights['.$right_id.']">';
        for($i=0;$i<4;$i++){
            if ($i==$right) {
                $select .= '<option value="'.$i.'" selected="selected">'.constant('HRADMIN_LEVEL_'.$i).'</option>';
            } else {
                $select .= '<option value="'.$i.'">'.constant('HRADMIN_LEVEL_'.$i).'</option>';
            }
        }
        $select .= '</select>';
        return $select;
    } else {
        return  constant('HRADMIN_LEVEL_'.$right);  
    }
    
}

function hasGroupRight($right_id,$rights = array())  
{
    return $rights[$right_id];
}

function getGroupRight($group_id) 
{
    global $admin;    
    $admin->groupIds = array ((int)$group_id); 
    $admin->perm->readGroupRights();
    return $objRightsPerm->groupRights;    
}

function setGroupRights($group_id,$newRights = array()) {
    global $admin;
    $rights = getGroupRight($group_id);
    foreach ($rights as $right => $level) {
        $objRightsAdminPerm->revokeGroupRight($group_id,$right);
    }
    if (!empty($newRights)) {
        foreach ($newRights as $newRight => $level) {
            if ((int)$level > 0) {                
                $objRightsAdminPerm->grantGroupRight($group_id,$newRight);
                $objRightsAdminPerm->updateGroupRight($group_id,$newRight,$level);
            }
        }    
    }
}


?>