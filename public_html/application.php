<?php
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
    
    $apps = $objRightsAdminPerm->getApplications(array('where_application_id'=>$_GET['edit']));
    $current_application_id         = $apps[0]['application_id'];
    $defaultValues['name']          = $apps[0]['name'];
    $defaultValues['description']   = $apps[0]['description'];
    $defaultValues['define']        = $apps[0]['define_name'];
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
    if ($edit && $level>1) {
        $objRightsAdminPerm->updateApplication($_POST['id'],$_POST["define"],$_POST["name"], $_POST["description"]);
        header("Location: applications.php");        
    } elseif($level>2) {
        $app_id = $objRightsAdminPerm->addApplication($_POST["define"],$_POST["name"], $_POST["description"]);
        if (DB::isError($group_id)) {
            var_dump($group_id);
        } else {
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
$tpl->setVariable('title',"Anwendung");
$tpl->show();

?>