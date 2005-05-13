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
 *   $Id: area.php,v 1.7 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_AREAS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();

$form = new HTML_QuickForm('edit','POST');

$form->addElement('hidden', 'app_id', _($current_application_id));
$form->addElement('hidden', 'area_id', _($current_area_id));

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
    
    $areas = $admin->perm->getAreas(array(
        'filters' => array(
            'application_id' => (int)$current_application_id,
            'area_id'        => (int)$current_area_id),
        'fields' => array(
            'area_id', 
            'name', 
            'description', 
            'area_define_name')
    ));
    $defaultValues['name']          = $areas[0]['name'];
    $defaultValues['description']   = $areas[0]['description'];
    $defaultValues['define']        = $areas[0]['area_define_name'];
    $form->addElement('hidden', 'id', $current_area_id);
    $form->setDefaults($defaultValues);
} else {
    $form->addElement('submit', 'submit', _("Create"));
}

$form->addRule('name', _("Name is required!"), 'required');
if ($level<2) {
    $form->freeze();   
}
if ($form->validate()) {
    if ($delete && $level>2) {
        $admin->perm->removeArea(array(
            'area_id'   => $current_area_id
        ));
        header("Location: areas.php?".getAppIdParameter());
    } elseif ($edit && $level>1) {
        $admin->perm->updateArea(array(
            'area_id'=>$current_area_id,
            'application_id' => $current_application_id,
            'area_define_name' => $form->exportValue('define')
        ), array('area_id'        => $current_area_id));
        $filters   = array(
            'section_id'    => $current_area_id,
            'section_type'  => LIVEUSER_SECTION_AREA, 
            'language_id'   => 0
        );
        $data      = array(
            'name' => $form->exportValue('name'), 
            'description'  => $form->exportValue('description')
        );                
        $admin->perm->updateTranslation($data,$filters);
        header("Location: areas.php?".getAppIdParameter());        
    } elseif ($level>2) {
        $data = array(
            'application_id' => $current_application_id,
            'area_define_name' => $form->exportValue('define')
        );
        $current_area_id = $admin->perm->addArea($data);
        if (DB::isError($current_area_id)) {
            var_dump($area_id);
            exit;
        } 
        $data      = array(
            'section_id'=>$current_area_id,
            'section_type' => LIVEUSER_SECTION_AREA, 
            'language_id'=>0,
            'name' => $form->exportValue('name'), 
            'description'  => $form->exportValue('description')
        );                
        $admin->perm->addTranslation($data);
        header("Location: areas.php?".getAppIdParameter());
        
    }    
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITStatic($tpl);
$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            
$tpl->addBlockfile('contentmain', 'area', 'editarea.html');
$form->accept($renderer);

if ($edit) {
    $rightcontent = '';
    $tpl->setVariable('contentright',$rightcontent);
}
$tpl->setVariable('title',_("Area"));

$tpl->show();

?>