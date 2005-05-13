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
 *   $Id: user.php,v 1.8 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';


if (!checkRights(HRADMIN_RIGHT_USERS)) {
    header("Location: noright.php");
    exit;
}

$tpl->addBlockfile('contentmain', 'user', 'edit.html');

$form = new HTML_QuickForm('edit','POST');

$form->addElement('text', 'login', _("Login"));
$tpl->setVariable(array('maxlength'=>'10',
                  'class'=>'formFieldLong'));
                                    
$form->addElement('text', 'name', _("Name"));
$tpl->setVariable(array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('text','email', _("Email"),
            array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('password', 'password', _("Password"));
$tpl->setVariable(array('maxlength'=>'10',
                        'class'=>'formFieldLong'));
                  
$form->addElement('password', 'password2', _("Repeat password"));
$tpl->setVariable(array('maxlength'=>'10',
                         'class'=>'formFieldLong'));  

if ($edit || $delete) {
    if ($level>1) {
        $form->addElement('submit', 'submit', _("Save"));
    }
    if ($level>2) {
        $form->addElement('submit', 'delete', _("Delete"));
    }
    
    $filter = array(array(
        'cond' => '', 
        'name' => 'auth_user_id', 
        'op' => '=', 
        'value' => $current_user_id, 
        'type' => 'text')
    );
        
    $users  = $admin->auth->getUsers($filter);
    
    $defaultValues['login']      = $users[0]['handle'];
    $defaultValues['name']       = $users[0]['name'];
    $defaultValues['email']      = $users[0]['email'];
    $form->addElement('hidden', 'user_id', $current_user_id);
    
    $form->setDefaults($defaultValues);
} else {

    $form->addElement('submit', 'submit', _("Create"));
}

// groupstuff
$tpl->setCurrentBlock('grouplist');
$groups = $admin->perm->getGroups();

foreach($groups as $group) {
    $tpl->setVariable(array('group'     => $group['group_define_name'],
                            'group_box' => getGroupCheckBox($group['group_id'],getPermUserId(isset($current_user_id)?$current_user_id:''))));
    $tpl->parseCurrentBlock();
}


// form rules
$form->addRule('login', _("Username is required!"), 'required');
if (!$delete) {
    $form->addRule('name', _("Name is required!"), 'required');
    $form->addRule('email', _("Email is required!"), 'required');
    if (!$edit) {
        $form->addRule('password', _("Password is required!"), 'required');
        $form->addRule('password2', _("Password is required!"), 'required');
    }
    $form->addRule(array('password', 'password2'), _("Passwords does not match!"), 'compare', null, 'client');
}
if ($level < 2) {
    $form->freeze();
}

// validation
if ($form->validate()) {   
    $custom = array(
        'name'  => $form->exportValue("name"),
        'email' => $form->exportValue("email")
    ); 
    if ($delete) {
        $res = $admin->removeUser(getPermUserId($current_user_id));
        header("Location: users.php");
        exit;
    } elseif (isset($current_user_id) && $level>1) {
        $pass = null;
        if ($form->exportValue("password")) {
            $pass = $form->exportValue("password");
        }
        $perm_id = getPermUserId($current_user_id);
        $admin->updateUser(
            $perm_id, 
            $form->exportValue("login"), 
            $pass, 
            array(
                'is_active' => true
            ), 
            $custom);
        setGroups($perm_id,$_POST['groups']);
        header("Location: users.php");
    } elseif ($level>2) {
        $current_user_id = $admin->addUser(
            $form->exportValue("login"),
            $form->exportValue("password"),
            true, 
            $custom, 
            null, 
            null
        );        
        if (DB::isError($current_user_id)) {
            var_dump::display($current_user_id);
            exit;
        }
        setGroups(getPermUserId($current_user_id),isset($_POST['groups'])?$_POST['groups']:array());
        header("Location: users.php");        
    }    
    exit;
}

$renderer =& new HTML_QuickForm_Renderer_ITStatic($tpl);
$renderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="orange" size="1">{error}</font><br/>{html}');            

$form->accept($renderer);
$tpl->setVariable('title',_("User"));
$tpl->show();


function getGroupCheckBox($group_id, $user_id) 
{
    $checked = '';
    $inGroup =isInGroup($group_id, $user_id);
    if (!empty($inGroup)) {
        $checked = 'checked="checked"';
    }
    return '<input name="groups[]" type="checkbox" value="'.$group_id.'" '.$checked.' />';
}

function isInGroup($group_id, $user_id)
{
    global $admin;
    return $admin->perm->getGroups(array(
        'filters'=>array(
            'group_id'=>$group_id,
            'perm_user_id'=>$user_id))
    );
}

function setGroups($user_id, $newGroups = array())
{
    global $admin;
    $groups = $admin->perm->getGroups();
    foreach ($groups as $group) {
        $filters = array(
            'group_id'     => $group,
            'perm_user_id' => $user_id
        );
        $removed = $admin->perm->removeUserFromGroup($filters);             
    }
    if (!empty($newGroups)) {
        foreach ($newGroups as $newGroup) {
            $result = $admin->perm->addUserToGroup(array('perm_user_id' => $user_id, 'group_id' => $newGroup));
        }
    }
}

function getPermUserId($user_id) {
    global $admin;
    $users = $admin->getUsers();
    foreach ($users as $user) {
        if ($user['auth_user_id']==$user_id) {
            return $user['perm_user_id'];
        }
    }
    return 0;
}

?>