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
 *   $Id: right.php,v 1.7 2005/05/13 08:32:10 goetsch Exp $
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

$form = new HTML_QuickForm('edit','POST');

$form->addElement('hidden', 'app_id', $current_application_id);
$form->addElement('hidden', 'area_id', $current_area_id);

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
    
    $rights = $admin->perm->getRights(array(
        'filters'=> array(
            'right_id'       => $current_right_id,
            'area_id'        => $current_area_id,
            'application_id' => $current_application_id),
        'fields' => array(
            'right_define_name')
    ));
    $trans = $admin->perm->getTranslations(array('filters'=>array(
        'section_id'    => $current_right_id,
        'section_type'  => LIVEUSER_SECTION_RIGHT, 
        'language_id'   => 0
    )));
    $defaultValues['name']          = $trans[0]['name'];
    $defaultValues['description']   = $trans[0]['description'];
    $defaultValues['define']        = $rights[0]['right_define_name'];
    $form->addElement('hidden', 'right_id', $current_right_id);
    
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
        $admin->perm->removeRight(array(
            'right_id'  => $current_right_id
        ));
        header("Location: rights.php?".getAppIdParameter().getAreaIdParameter());
    } elseif (isset($current_right_id) && $level>1) {
        $admin->perm->updateRight(array(
            'area_id'           => $current_area_id,
            'right_define_name' => $form->exportValue('define')
        ), array('right_id'     => $current_right_id));
        $filters   = array(
            'section_id'    => $current_right_id,
            'section_type'  => LIVEUSER_SECTION_RIGHT, 
            'language_id'   => 0
        );
        $data      = array(
            'name'          => $form->exportValue('name'), 
            'description'   => $form->exportValue('description')
        );   
        if (!$admin->perm->updateTranslation($data,$filters)) {
            $data      = array(
                'section_id'    => $current_right_id,
                'section_type'  => LIVEUSER_SECTION_RIGHT, 
                'language_id'   => 0,
                'name'          => $form->exportValue('name'), 
                'description'   => $form->exportValue('description')
            );                
            $admin->perm->addTranslation($data);
        }
        header("Location: rights.php?".getAppIdParameter().getAreaIdParameter());
    } elseif ($level>2) {
        $data = array(
            'area_id'           => $current_area_id,
            'right_define_name' => $form->exportValue('define')
        );
        $current_right_id = $admin->perm->addRight($data);
        if (DB::isError($current_right_id)) {
            var_dump($current_right_id);
            exit;
        } 
        $data      = array(
            'section_id'    => $current_right_id,
            'section_type'  => LIVEUSER_SECTION_RIGHT, 
            'language_id'   => 0,
            'name'          => $form->exportValue('name'), 
            'description'   => $form->exportValue('description')
        );                
        $admin->perm->addTranslation($data);
        header("Location: rights.php?".getAppIdParameter().getAreaIdParameter());
        
    }
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITStatic($tpl);
$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            
$tpl->addBlockfile('contentmain', 'right', 'editright.html');
$form->accept($renderer);
$tpl->setVariable('title',_("Right"));
$tpl->show();

?>