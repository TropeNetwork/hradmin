<?php
require_once 'config.inc';
require_once 'LiveUser/Admin/Perm/Container/DB_Medium.php';
require_once 'LiveUser/Admin/Auth/Container/DB.php';
require_once 'LiveUser/Perm/Container/DB/Medium.php';
require_once 'HTML/TreeMenu.php';
require_once 'class/Template.php';

$lu_dsn = array('dsn' => $dsn);

$objRightsAdminAuth = new
    LiveUser_Admin_Auth_Container_DB($conf['authContainers'][0]);

$objRightsAdminPerm = new
    LiveUser_Admin_Perm_Container_DB_Medium($conf['permContainer']);

   
if (!$objRightsAdminPerm->init_ok) {
    die('impossible to initialize' . $objRightsAdminPerm->getMessage());
}


define('HRADMIN',4);
define('HRADMIN_ADMIN',3);
$objRightsAdminPerm->setCurrentLanguage('DE');
$objRightsAdminPerm->outputRightsConstants(array('prefix'       =>'HRADMIN_RIGHT',
                                                 'area'         => HRADMIN_ADMIN,
                                                 'application'  => HRADMIN),'php');
//$objRightsAdminAuth->authTableCols= $conf['authContainers'][0]['authTableCols'];


if (isset($_GET['app_id']) && !$_GET['app_id']=='') {
    $current_application_id = $_GET['app_id'];
} elseif (isset($_POST['app_id']) && !$_POST['app_id']=='') {
    $current_application_id = $_POST['app_id'];
} elseif($_SERVER['PHP_SELF']=='/application.php' && isset($_GET['edit'])) {
    $current_application_id = $_GET['edit'];
}

if (isset($_GET['area_id']) && !$_GET['area_id']=='') {
    $current_area_id = $_GET['area_id'];
} elseif (isset($_POST['area_id']) && !$_POST['area_id']=='') {
    $current_area_id = $_POST['area_id'];
}  elseif($_SERVER['PHP_SELF']=='/area.php' && isset($_GET['edit'])) {
    $current_area_id = $_GET['edit'];
}

$edit   = false;
$delete = false;
if (isset($_POST['delete'])) {
    $delete = true;
} elseif (isset($_GET['edit']) && $_GET['edit']!='') {
    $edit=true;
} elseif (isset($_POST['submit']) && $_POST['submit']=='Speichern') {
    $edit=true;
}

$tpl->setVariable('title',"Admin");
$tpl->setVariable('path',getPath());

if (!$usr->isLoggedIn()) {
    $tpl->loadTemplateFile('login_form.html');
    $tpl->show();
    exit;
} 

$objRightsPerm = $usr->getPermContainer();
if (!$objRightsPerm->init_ok) {
    die('impossible to initialize' . $objRightsPerm->getMessage());
}

$login  = 'Benutzer: '.$usr->getProperty('handle').'<br> ';
$login .= '<a href="?logout=1">Abmelden</a>';

$tpl->setVariable('login_top',$login);
$level = 0;


// Menu begin
$icon         = 'folder.gif';
$expandedIcon = 'folder-expanded.gif';

$menu  = new HTML_TreeMenu();

$nodeApps   = new HTML_TreeNode(array('text' => "Anwendungen", 'link' => "applications.php", 'icon' => $icon, 'expandedIcon' => $expandedIcon));
if ($current_application_id) {
    $nodeAreas = &$nodeApps->addItem(new HTML_TreeNode(array('text' => "Bereiche", 'link' => "areas.php?".getAppIdParameter(), 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
    if ($current_area_id) {
        $nodeRights = &$nodeAreas->addItem(new HTML_TreeNode(array('text' => "Rechte", 'link' => "rights.php?".getAppIdParameter().'&'.getAreaIdParameter(), 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
    }
}
    
$menu->addItem($nodeApps);
$menu->addItem(new HTML_TreeNode(array('text' => "Benutzer", 'link' => "users.php", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
$menu->addItem(new HTML_TreeNode(array('text' => "Gruppen", 'link' => "groups.php", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
    
// Create the presentation class
$treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => '../images', 'defaultClass' => 'treeMenuDefault'));
$tpl->setVariable('menu',$treeMenu->toHtml());
// menu end

function checkApplication() {
    global $current_application_id;
    if (!isset($current_application_id)) {
        die('Keine Anwendung angegeben!');
    }
}

function checkArea() {
    global $current_area_id;
    if (!isset($current_area_id)) {
        die('Kein Bereich angegeben!');
    }
}

function getAppIdParameter() {
    global $current_application_id;
    return 'app_id='.$current_application_id;
}

function getAreaIdParameter() {
    global $current_area_id;
    return 'area_id='.$current_area_id;
}

function getPath() {
    global $current_application_id;
    global $current_area_id;
    $path = '';
    if (isset($current_application_id)) {
        $path .= '->'.getApplicationName($current_application_id);
    }
    if (isset($current_area_id)) {
        $path .= '->'.getAreaName($current_area_id);
    }
    return $path;
}

function getApplicationName($app_id) {
    global $objRightsAdminPerm;
    $apps = $objRightsAdminPerm->getApplications(array('where_application_id'=>$app_id));
    return '<a href="application.php?edit='.$app_id.'">'.$apps[0]['name'].'</a>';
}

function getAreaName($area_id) {
    global $objRightsAdminPerm;
    $areas = $objRightsAdminPerm->getAreas(array('where_area_id'=>$area_id));
    return '<a href="area.php?edit='.$area_id.'&'.getAppIdParameter().'">'.$areas[$area_id]['name'].'</a>';
}

function checkRights($right) {
    global $level;
    global $delete;
    global $usr;
    $level = $usr->checkRight($right);
    if (!$level) {
        return false;
    }
    if ($level < 3) {
        $delete = false;   
    }
    return true;
}

?>