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
 *   $Id: group.php,v 1.12 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITDynamic.php';

include_once 'common.inc';

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
$form->addElement('hidden', 'group_id');
$form->addElement('text', 'define', _("Define name"));

$tpl->setVariable(array('maxlength' => '15',
                        'class'     => 'formFieldLong'));

if ($edit) {
    $groups = $admin->perm->getGroups(array('filters'  => array('group_id' => $current_group_id),
                                            'fields'   => array('group_id',
                                                                'description',
                                                                'name',
                                                                'group_define_name')));
                                       
    $defaultValues['name']          = $groups[0]['name'];
    $defaultValues['define']        = $groups[0]['group_define_name'];
    $defaultValues['description']   = $groups[0]['description'];
    $defaultValues['group_id']      = $current_group_id;


    $params = array('fields'  => array('group_id','right_id','right_level'),
                    'filters' => array('group_id' => $current_group_id ));
                  
    $group_rights = $admin->perm->getRights($params);
    $selectedRights = array();
    foreach ($group_rights as $right){
       $selectedRights[$right['right_id']]=$right['right_level'];      
    }
    $defaultValues['rights'] = $selectedRights;   
    $form->setDefaults($defaultValues);
    
    
    if ($level>1) {
        $form->addElement('submit', 'submit', _("Save"));
    }
    if ($level>2) {
        $form->addElement('submit', 'delete', _("Delete"));
    }
    
} else {
    $form->addElement('submit', 'submit', _("Create"));
}
$tpl->addBlockfile('contentmain', 'group', 'editgroup.html');


// right stuff
$apps = $admin->perm->getApplications(array('fields' => array('application_id', 
                                                              'name', 
                                                              'description',
                                                              'application_define_name')));
define('HRADMIN_LEVEL_0',_('none'));
define('HRADMIN_LEVEL_1',_('read'));
define('HRADMIN_LEVEL_2',_('write'));
define('HRADMIN_LEVEL_3',_('delete'));

foreach($apps as $app) {

    $areas = $admin->perm->getAreas(array('fields' => array('area_id', 
                                                            'name', 
                                                            'description',
                                                            'area_define_name'),
                                          'filters '=> array('application_id' => $app['application_id'] )));

    foreach($areas as $area) {
        $rights = $admin->perm->getRights(array('filters' => array('application_id' => $app['application_id'],
                                                                   'area_id'        => $area['area_id'] ),
                                                'fields'  => array('name','right_id')));
        foreach($rights as $right) {
            $trans = $admin->perm->getTranslations(array('filters'=>array(
                'section_id'    => $right['right_id'],
                'section_type'  => LIVEUSER_SECTION_RIGHT, 
                'language_id'   => 0
            )));
            $Cols[] = HTML_QuickForm::createElement('static','app'.$right['right_id'],null,$app['name']);
            $Cols[] = HTML_QuickForm::createElement('static','area'.$right['right_id'],null,$area['name']);
            $Cols[] = HTML_QuickForm::createElement('static','right'.$right['right_id'],null,$trans[0]['name']);
            $Cols[] = HTML_QuickForm::createElement('select',$right['right_id'],null,array('0'=>HRADMIN_LEVEL_0,
                                                                                                    '1'=>HRADMIN_LEVEL_1,
                                                                                                    '2'=>HRADMIN_LEVEL_2,
                                                                                                    '3'=>HRADMIN_LEVEL_3));
            $form->addGroup(@$Cols,'rights');
            unset($Cols);
 
        }

    }
}

$form->addRule('name', _("Name is required!"), 'required');
if ($level < 2) {
    $form->freeze();
}

if ($form->validate()) {
    if ($delete) {
        $admin->perm->removeGroup(array('group_id' => $form->exportValue('group_id')));
        header("Location: groups.php");
    } elseif ( isset($current_group_id) && $level>1) {
        $data   = array('group_define_name' => $form->exportValue("define") );
        $filter = array('group_id'=> $form->exportValue('group_id'));
        $admin->perm->updateGroup($data,$filter);
        
        
        $filter = array('section_id'   => $form->exportValue('group_id'),
                        'section_type' => LIVEUSER_SECTION_GROUP, 
                        'language_id'  => 0);
        $data   = array('name'         => $form->exportValue("name"), 
                        'description'  => $form->exportValue("description"));
                
        $admin->perm->updateTranslation($data,$filter);
        setGroupRights($form->exportValue('group_id'),$_POST['rights']);
        header("Location: groups.php");
    } elseif ($level>2) {
        $data   = array(
            'group_define_name' => $form->exportValue("define"),
            'is_active'         => 'Y',
        );  
        $current_group_id = $admin->perm->addGroup($data);
        
        if (DB::isError($current_group_id)) {
            var_dump($current_group_id);
            exit;
        }
        $data   = array('section_id'   => $current_group_id,
                        'section_type' => LIVEUSER_SECTION_GROUP, 
                        'language_id'  => 0,
                        'name'         => $form->exportValue("name"), 
                        'description'  => $form->exportValue("description"));
        $admin->perm->addTranslation($data);
        setGroupRights($current_group_id,$_POST['rights']);
        header("Location: groups.php");
        
    }
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITDynamic($tpl);
#$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
#$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            

$renderer->setElementBlock(array(
    'rights'  => 'qf_group_row'
));
        

$form->accept($renderer);


$tpl->setVariable('title',_("Group"));

$tpl->show();


        

function hasGroupRight($right_id,$rights = array())  
{
    return ; #$rights[$right_id];
}

function setGroupRights($group_id,$newRights = array()) {
    global $admin;
    
    
    $params = array('fields'  => array('right_id','right_level'),
                    'filters' => array('group_id' => $group_id));
                            
    $rights = $admin->perm->getRights($params);
    
    
    foreach ($rights as $right => $level) {
    
        $filters  = array('right_id' => $right,
                          'group_id' => $group_id);

        $removed = $admin->perm->revokeGroupRight($filters);
    
    
    }
    if (!empty($newRights)) {
        foreach ($newRights as $newRight => $level) {
            if ((int)$level > 0) {                
            
                $data = array('right_level' => $level);
                $filters = array('right_id' => $newRight,
                                 'group_id' => $group_id);
  
                if (!$admin->perm->updateGroupRight($data, $filters)) {
                    $admin->perm->grantGroupRight(array(
                        'right_level'   => $level,
                        'right_id'      => $newRight,
                        'group_id'      => $group_id
                    ));
                }
            }
        }    
    }
}


?>