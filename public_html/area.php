<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_AREAS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();


$form = new HTML_QuickForm('edit','POST');

//$form->addElement('hidden', 'app_id', _($current_application_id));

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
    
    $areas = $objRightsAdminPerm->getAreas(array('where_application_id' => $_GET['app_id'],
                                                 'where_area_id'        => $_GET['edit']));
    $defaultValues['name']          = $areas[$_GET['edit']]['name'];
    $defaultValues['description']   = $areas[$_GET['edit']]['description'];
    $defaultValues['define']        = $areas[$_GET['edit']]['define_name'];
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
        $objRightsAdminPerm->removeArea($_POST['id']);
        header("Location: areas.php?".getAppIdParameter());
    } elseif ($edit && $level>1) {
        $objRightsAdminPerm->updateArea($_POST['id'],$current_application_id,$_POST["define"],$_POST["name"], $_POST["description"]);
        header("Location: areas.php?".getAppIdParameter());        
    } elseif ($level>2) {
        $area_id = $objRightsAdminPerm->addArea($current_application_id,$_POST["define"],$_POST["name"], $_POST["description"]);
        if (DB::isError($area_id)) {
            var_dump($area_id);
        } else {
            header("Location: areas.php?".getAppIdParameter());
        }
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