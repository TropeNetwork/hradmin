<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';

include_once 'common.inc';

if (!checkRights(HRADMIN_RIGHT_AREAS)) {
    header("Location: noright.php");
    exit;
}

checkApplication();

$areas = $objRightsAdminPerm->getAreas(array('where_application_id'=>$current_application_id ));
$tpl->addBlockfile('contentmain', 'areas', 'arealist.html');
$tpl->setCurrentBlock('arealist');
foreach($areas as $area) {
    $tpl->setVariable(array('name'        => '<a href="area.php?edit='.$area['area_id'].'&app_id='.$current_application_id.'">'.$area['name'].'</a>',
                            'description' => $area['description'],
                            'id'          => $area['area_id'],
                            'define'      => $area['define_name']));
    $tpl->parseCurrentBlock();
}
if ($level>2) {
    $right = '<a href="area.php?app_id='.$current_application_id.'" title="Neuer Bereich"><img src="/images/new.png" alt="Neuer Bereich" /></a>';
}
$tpl->setVariable('contentright',$right);
$tpl->setVariable('title',"Bereiche");
$tpl->show();
?>