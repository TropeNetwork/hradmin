<?php
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
    
    $rights = $objRightsAdminPerm->getRights(array('where_right_id'=>$_GET['edit']));
    $defaultValues['name']          = $rights[$_GET['edit']]['name'];
    $defaultValues['description']   = $rights[$_GET['edit']]['description'];
    $defaultValues['define']        = $rights[$_GET['edit']]['define_name'];
    $form->addElement('hidden', 'id', $_GET['edit']);
    
    $form->setDefaults($defaultValues);
} else {
    $form->addElement('submit', 'submit', _("Anlegen"));
}

$form->addRule('name', "Name darf nicht leer sein", 'required');
if ($level<2) {
    $form->freeze();
}

if ($form->validate()) {
    if ($delete && $level>2) {
        $objRightsAdminPerm->removeRight($_POST['id']);
        header("Location: rights.php?".getAppIdParameter().'&'.getAreaIdParameter());
    } elseif ($edit && $level>1) {
        $objRightsAdminPerm->updateRight(
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
$tpl->setVariable('title',"Recht");
$tpl->show();

?>