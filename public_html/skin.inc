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
    $menu = ".|Home|index.php|Home|home.png\n";
    $menu .= ".|Anwendungen|applications.php||\n";
    $apps = $objRightsAdminPerm->getApplications();
    foreach($apps as $app) {
        $menu .= "..|".$app['name']."|application.php?edit=".$app['application_id']."||\n";
        $areas = $objRightsAdminPerm->getAreas(array('where_application_id'=>$app['application_id'] ));
        $menu .= "...|Bereiche|areas.php?app_id=".$app['application_id']."||\n";
        $menu .= "...|---|||\n";
        foreach($areas as $area) {
            $menu .= "...|".$area['name']."|area.php?edit=".$area['area_id']."&amp;app_id=".$app['application_id']."||\n";
            $menu .= "....|Rechte|rights.php?area_id=".$area['area_id']."&amp;app_id=".$app['application_id']."||\n";
            $menu .= "....|---|||\n";
            $rights = $objRightsAdminPerm->getRights(array('where_area_id'=> $area['area_id']));
            foreach($rights as $right) {
                $menu .= "....|".$right['name']."|right.php?edit=".$right['right_id']."&amp;app_id=".$app['application_id']."&amp;area_id=".$area['area_id']."||\n";
            }
        }
    }
    $menu .= ".|Benutzer|users.php|Benutzer|users.png\n";
    $menu .= ".|Gruppen|groups.php|Gruppen|groups.png\n";
    $menu .= ".|Abmelden|?logout=1||\n";
    $menu .= ".|Hilfe|||help.png\n";
    return $menu;
}
?>