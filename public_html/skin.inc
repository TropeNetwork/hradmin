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
 *   $Id: skin.inc,v 1.7 2005/05/13 08:32:10 goetsch Exp $
 */
require_once 'HTML/Template/Sigma.php';
require_once 'layersmenu/lib/PHPLIB.php';
require_once 'layersmenu/lib/layersmenu-common.inc.php';
require_once 'layersmenu/lib/layersmenu.inc.php';
header('Content-Type: text/html; charset='.CHARSET);
$tpl =& new HTML_Template_Sigma(dirname(__FILE__).'/templates/');
$tpl->loadTemplateFile('template.html');
$tpl->setCallbackFunction('gettext','gettext');
$layersmenu = '<script language="JavaScript" type="text/javascript" src="layersmenu/libjs/layersmenu-library.js"></script>'."\n";
$layersmenu .= '<script language="JavaScript" type="text/javascript" src="layersmenu/libjs/layersmenu.js"></script>'."\n";
$mid = new LayersMenu(6, 7, 2, 1);

$mid->setMenuStructureString(getMenuString());
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');
$layersmenu .= $mid->getHeader();
$tpl->setVariable('layersmenu_head',$layersmenu);
$tpl->setVariable('layersmenu',$mid->getMenu('hormenu1'));
$tpl->setVariable('layersmenu_footer',$mid->getFooter());

function getMenuString() {
    global $admin;
    $menu = ".|".gettext("Home")."|index.php|".gettext("Home")."|home.png\n";
    $menu .= ".|".gettext("Applications")."|applications.php|".gettext("Applications")."|\n";
    $apps = $admin->perm->getApplications();
    foreach($apps as $app) {
        $menu .= "..|".$app['application_define_name']."|application.php?edit=1&amp;app_id=".$app['application_id']."||\n";
        $areas = $admin->perm->getAreas(array('filters'=>array('application_id'=>$app['application_id'] )));
        $menu .= "...|".gettext("Areas")."|areas.php?app_id=".$app['application_id']."|".gettext("Areas")."|\n";
        $menu .= "...|---|||\n";
        foreach($areas as $area) {
            $menu .= "...|".$area['area_define_name']."|area.php?edit=1&amp;area_id=".$area['area_id']."&amp;app_id=".$app['application_id']."||\n";
            $menu .= "....|".gettext("Rights")."|rights.php?area_id=".$area['area_id']."&amp;app_id=".$app['application_id']."|".gettext("Rights")."|\n";
            $menu .= "....|---|||\n";
            $rights = $admin->perm->getRights(array('filters'=>array('area_id'=> $area['area_id'],
                                                                    'application_id' => $app['application_id'])));
            foreach($rights as $right) {
                $menu .= "....|".$right['right_define_name']."|right.php?edit=1&amp;right_id=".$right['right_id']."&amp;app_id=".$app['application_id']."&amp;area_id=".$area['area_id']."||\n";
            }
        }
    }
    $menu .= ".|".gettext("Users")."|users.php|".gettext("Users")."|users.png\n";
    $menu .= ".|".gettext("Groups")."|groups.php|".gettext("Groups")."|groups.png\n";
    $menu .= ".|".gettext("Logout")."|?logout=1|".gettext("Logout")."|\n";
    $menu .= ".|".gettext("Help")."||".gettext("Help")."|help.png\n";
    return $menu;
}
?>