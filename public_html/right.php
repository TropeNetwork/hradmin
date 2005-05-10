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
 *   $Id: right.php,v 1.6 2005/05/10 07:07:02 cbleek Exp $
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
    
    $rights = $admin->perm->getRights(array('filters'=> array('right_id'       => $_GET['edit'],
                                                              'area_id'        => $_GET['area_id'],
                                                              'application_id' => $_GET['app_id']),
                                            'fields' => array('right_define_name','name','description')));
                                            
    $defaultValues['name']          = $rights[0]['name'];
    $defaultValues['description']   = $rights[0]['description'];
    $defaultValues['define']        = $rights[0]['right_define_name'];
    $form->addElement('hidden', 'id', $_GET['edit']);
    
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
        $objRightsAdminPerm->removeRight($_POST['id']);
        header("Location: rights.php?".getAppIdParameter().'&'.getAreaIdParameter());
    } elseif (isset($_POST['id']) && $_POST['id']>0 && $level>1) {
        $admin->perm->updateRight(
            $form->exportValue('id'),
            $current_area_id,
            $form->exportValue("define"),
            $form->exportValue("name"),
            $form->exportValue("description")
        );
        
        header("Location: rights.php?".getAppIdParameter().'&'.getAreaIdParameter());
    } elseif ($level>2) {
        $right_id = $objRightsAdminPerm->addRight(
            $current_area_id,
            $form->exportValue("define"),
            $form->exportValue("name"),
            $form->exportValue("description")
        );
        if (DB::isError($group_id)) {
            var_dump($group_id);
        } else {
            header("Location: rights.php?".getAppIdParameter().'&'.getAreaIdParameter());
        }
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