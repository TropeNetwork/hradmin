<?php
require_once 'HTML/Template/Sigma.php';
require_once 'layersmenu/lib/PHPLIB.php';
require_once 'layersmenu/lib/layersmenu-common.inc.php';
require_once 'layersmenu/lib/layersmenu.inc.php';
$tpl =& new HTML_Template_Sigma(dirname(__FILE__).'/templates/');
$tpl->loadTemplateFile('template.html');
$layersmenu = '<script language="JavaScript" type="text/javascript" src="layersmenu/libjs/layersmenu-library.js"></script>'."\n";
$layersmenu .= '<script language="JavaScript" type="text/javascript" src="layersmenu/libjs/layersmenu.js"></script>'."\n";
$mid = new LayersMenu(6, 7, 2, 1);
//$mid->setMenuStructureFile('menu.txt');
$mid->setMenuStructureString(getMenuString());
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');
$layersmenu .= $mid->getHeader();
$tpl->setVariable('layersmenu_head',$layersmenu);
$tpl->setVariable('layersmenu',$mid->getMenu('hormenu1'));
$tpl->setVariable('layersmenu_footer',$mid->getFooter());

function getMenuString() {
    global $objRightsAdminPerm;
    $menu = ".|".gettext("Home")."|index.php|".gettext("Home")."|home.png\n";
    $menu .= ".|".gettext("Applications")."|applications.php|".gettext("Applications")."|\n";
    $apps = $objRightsAdminPerm->getApplications();
    foreach($apps as $app) {
        $menu .= "..|".$app['name']."|application.php?edit=".$app['application_id']."||\n";
        $areas = $objRightsAdminPerm->getAreas(array('where_application_id'=>$app['application_id'] ));
        $menu .= "...|".gettext("Areas")."|areas.php?app_id=".$app['application_id']."|".gettext("Areas")."|\n";
        $menu .= "...|---|||\n";
        foreach($areas as $area) {
            $menu .= "...|".$area['name']."|area.php?edit=".$area['area_id']."&amp;app_id=".$app['application_id']."||\n";
            $menu .= "....|".gettext("Rights")."|rights.php?area_id=".$area['area_id']."&amp;app_id=".$app['application_id']."|".gettext("Rights")."|\n";
            $menu .= "....|---|||\n";
            $rights = $objRightsAdminPerm->getRights(array('where_area_id'=> $area['area_id']));
            foreach($rights as $right) {
                $menu .= "....|".$right['name']."|right.php?edit=".$right['right_id']."&amp;app_id=".$app['application_id']."&amp;area_id=".$area['area_id']."||\n";
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