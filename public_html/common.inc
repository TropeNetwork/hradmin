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
 *   $Id: common.inc,v 1.9 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'config.inc';
include_once 'i18n.inc';
require_once 'LiveUser.php';
require_once 'LiveUser/Admin.php';
require_once 'HTML/TreeMenu.php';

error_reporting(E_ALL);

define('HRADMIN',4);
define('HRADMIN_ADMIN',3);

// define the right constants
$admin->perm->outputRightsConstants('constant',array(
    'prefix'       =>'HRADMIN_RIGHT',
    'area'         => HRADMIN_ADMIN,
    'application'  => HRADMIN
),'php');

if (isset($_GET['app_id']) && !$_GET['app_id']=='') {
    $current_application_id = $_GET['app_id'];
} elseif (isset($_POST['app_id']) && !$_POST['app_id']=='') {
    $current_application_id = $_POST['app_id'];
} 

if (isset($_GET['area_id']) && !$_GET['area_id']=='') {
    $current_area_id = $_GET['area_id'];
} elseif (isset($_POST['area_id']) && !$_POST['area_id']=='') {
    $current_area_id = $_POST['area_id'];
} 

if (isset($_GET['right_id']) && !$_GET['right_id']=='') {
    $current_right_id = $_GET['right_id'];
} elseif (isset($_POST['right_id']) && !$_POST['right_id']=='') {
    $current_right_id = $_POST['right_id'];
} 

if (isset($_GET['user_id']) && !$_GET['user_id']=='') {
    $current_user_id = $_GET['user_id'];
} elseif (isset($_POST['user_id']) && !$_POST['user_id']=='') {
    $current_user_id = $_POST['user_id'];
} 

if (isset($_GET['group_id']) && !$_GET['group_id']=='') {
    $current_group_id = $_GET['group_id'];
} elseif (isset($_POST['group_id']) && !$_POST['group_id']=='') {
    $current_group_id = $_POST['group_id'];
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

require_once 'skin.inc';

$tpl->setVariable('path',getPath());
if (!$usr->isLoggedIn()) {
    $tpl->loadTemplateFile('login_form.html');
    $tpl->show();
    exit;
} 

$level = 0;

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
    return 'app_id='.$current_application_id.'&';
}

function getAreaIdParameter() {
    global $current_area_id;
    return 'area_id='.$current_area_id.'&';
}

function getRightIdParameter() {
    global $current_right_id;
    return 'right_id='.$current_right_id.'&';
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
    global $admin;
    $apps = $admin->perm->getApplications(array('filters'=>array('application_id'=>$app_id)));
    return '<a href="application.php?edit='.$app_id.'">'.$apps[0]['application_define_name'].'</a>';
}

function getAreaName($area_id) {
    global $admin;
    $areas = $admin->perm->getAreas(array('filters' => array( 'area_id'=>$area_id)));
    return '<a href="area.php?edit='.$area_id.'&'.getAppIdParameter().'">'.$areas[0]['area_define_name'].'</a>';
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