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
 *   $Id: application.php,v 1.8 2005/04/21 15:44:01 cbleek Exp $
 */
 
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_APPLICATIONS)) {
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

    $apps = $admin->perm->getApplications(array('filters' => array('application_id' => $_GET['edit']),
                                                'fields'  => array('application_id','name','description','application_define_name')));
    $current_application_id         = $apps[0]['application_id'];
    $defaultValues['name']          = $apps[0]['name'];
    $defaultValues['description']   = $apps[0]['description'];
    $defaultValues['define']        = $apps[0]['application_define_name'];
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
    if (isset($_POST['id']) && $_POST['id']>0 && $level>1) {
        $data      = array('application_define_name' => $_POST['define'], 
                           'name'                    => $_POST['name'], 
                           'description'             => $_POST['description']);
        $filters   = array('application_id' => $_POST['id']);
        $updateApp = $admin->perm->updateApplication($data, $filters);

        $filters   = array('section_id'=>$_POST['id'],'section_type' => LIVEUSER_SECTION_APPLICATION, 'language_id'=>0);
        $data      = array('name' => $_POST['name'], 'description'  => $_POST['description']);
                
        $admin->perm->updateTranslation($data,$filters); 

        header("Location: applications.php");        
    } elseif($level>2) {
        $data = array('application_define_name' => $_POST['define']);
        $app_id = $admin->perm->addApplication($data);

        if (DB::isError($app_id)) {
            var_dump($app_id);
        } else {
            $data = array(
                'section_id'   => $app_id,
                'section_type' => LIVEUSER_SECTION_APPLICATION,
                'language_id'  => '0',
                'name'         => $_POST['name'],
                'description'  => $_POST['description']);
                
            $admin->perm->addTranslation($data); 

            header("Location: applications.php");
        }
    }
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITStatic($tpl);
$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            
$tpl->addBlockfile('contentmain', 'application', 'editapplication.html');
$form->accept($renderer);

if ($edit) {
    $rightcontent = '';
    $tpl->setVariable('contentright',$rightcontent);
}
$tpl->setVariable('title',_("Application"));
$tpl->show();

?>