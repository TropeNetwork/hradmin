<?php

require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

define ('HRADMIN_LEVEL_0',"");
define ('HRADMIN_LEVEL_1',"Lesen");
define ('HRADMIN_LEVEL_2',"Schreiben");
define ('HRADMIN_LEVEL_3',"Löschen");

if (!checkRights(HRADMIN_RIGHT_GROUPS)) {
    header("Location: noright.php");
    exit;
}

$form = new HTML_QuickForm('edit','POST');

                  
$form->addElement('text', 'name', _("Name"));
$tpl->setVariable(array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('text', 'description', _("Beschreibung"));
$tpl->setVariable(array('maxlength'=>'100',
                  'class'=>'formFieldLong'));

$form->addElement('text', 'define', _("define"));
$tpl->setVariable(array('maxlength'=>'15',
                  'class'=>'formFieldLong'));

if ($edit) {
    if ($level>1) {
        $form->addElement('submit', 'submit', _("Speichern"));
    }
    if ($level>2) {
        $form->addElement('submit', 'delete', _("Löschen"));
    }
    
    $groups = $objRightsAdminPerm->getGroups(array('where_group_id'=>$_GET['edit']));
    $defaultValues['name']          = $groups[$_GET['edit']]['name'];
    $defaultValues['define']        = $groups[$_GET['edit']]['group_define_name'];
    $defaultValues['description']   = $groups[$_GET['edit']]['description'];
    $form->addElement('hidden', 'id', $_GET['edit']);
    
    $form->setDefaults($defaultValues);
} else {
    $form->addElement('submit', 'submit', _("Anlegen"));
}
$tpl->addBlockfile('contentmain', 'group', 'editgroup.html');


// right stuff
$tpl->setCurrentBlock('rightlist');
$apps = $objRightsAdminPerm->getApplications();
$groupRights = getGroupRight($_GET['edit']);
foreach($apps as $app) {
    $areas = $objRightsAdminPerm->getAreas(array('where_application_id' => $app['application_id'] ));
    foreach($areas as $area) {
        $rights = $objRightsAdminPerm->getRights(array('where_application_id' => $app['application_id'],
                                                       'where_area_id'        => $area['area_id'] ));
        foreach($rights as $right) {
            $tpl->setVariable(array('application'   => $app['name'],
                                    'area'          => $area['name'],
                                    'right'         => $right['name'],
                                    'right_box'     => getRightLevelBox($right['right_id'],$groupRights)));
            $tpl->parseCurrentBlock();
        }
    }
}

$form->addRule('name', "Name darf nicht leer sein", 'required');
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
    global $objRightsPerm;    
    $objRightsPerm->groupIds = array ((int)$group_id); 
    $objRightsPerm->readGroupRights();
    return $objRightsPerm->groupRights;    
}

function setGroupRights($group_id,$newRights = array()) {
    global $objRightsAdminPerm;
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