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
 *   $Id: config.inc,v 1.6 2005/04/21 14:11:37 cbleek Exp $
 */
require_once 'Config.php';
require_once 'DB.php';
require_once 'Log.php';
require_once 'Var_Dump.php';
require_once 'LiveUser.php';
require_once 'LiveUser/Admin.php';

$conf = new Config;
$root =& $conf->parseConfig(dirname(__FILE__).'/../config/config.xml', 'XML');
if (PEAR::isError($root)) {
    die('Error while reading configuration: ' . $root->getMessage());
}
$hradmin_settings = $root->toArray();
$hradmin_settings = $hradmin_settings['root']['conf'];
$dsn = 'mysql://'.$hradmin_settings['database']['user'].':'.
                  $hradmin_settings['database']['pass'].'@'.
                  $hradmin_settings['database']['host'].'/'.
                  $hradmin_settings['database']['name'];     

// i18n                   
define('LOCALE',$hradmin_settings['i18n']['locale']);
define('CHARSET',$hradmin_settings['i18n']['charset']);

$db = DB::connect($dsn);

if (DB::isError($db)) {
    echo $db->getMessage() . ' ' . $db->getUserInfo();
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);


$conf =
    array(
        'autoInit' => true,
        'session'  => array(
            'name'     => 'PHPSESSION',
            'varname'  => 'ludata'
        ),
        'login' => array(
            'method'   => 'post',
            'username' => 'handle',
            'password' => 'passwd',
            'force'    => false,
            'function' => '',
            'remember' => 'rememberMe'
        ),
        'logout' => array(
            'trigger'  => 'logout',
            'redirect' => 'index.php',
            'destroy'  => true,
            'method'   => 'get',
            'function' => ''
        ),
        'authContainers' => array(
            array(
                'type'          => 'DB',
                'name'          => 'DB_Local',
                'loginTimeout'  => 0,
                'expireTime'    => 3600,
                'idleTime'      => 1800,
                'dsn'           => $dsn,
                'allowDuplicateHandles' => 0,
                'authTable'     => 'liveuser_users',
                'authTableCols' => array(
                     'required' => array(
                        'auth_user_id' => array('type' => 'text', 'name' => 'auth_user_id'),
                        'handle'       => array('type' => 'text', 'name' => 'handle'),
                        'passwd'       => array('type' => 'text', 'name' => 'passwd')
                     ),
                     'optional' => array(
                        'lastlogin'      => array('type' => 'timestamp', 'name' => 'lastlogin'),
                        'is_active'      => array('type' => 'boolean',   'name' => 'is_active'),
                        'owner_user_id'  => array('type' => 'integer',   'name' => 'owner_user_id'),
                        'owner_group_id' => array('type' => 'integer',   'name' => 'owner_group_id')
                     ),
                    'custom' => array (
                        'name'  => array('type' => 'text', 'name' => 'name'),
                        'email' => array('type' => 'text', 'name' => 'email'),
                     )
                )
            )
        ),
        'permContainer' => array(
            'type'       => 'Medium',
            'storage'    => array(
                   'DB' => array(
                      'connection' => $db,
                      'dsn' => $dsn,
                      'prefix' => 'liveuser_',
                      'tables' => array(),
                      'fields' => array(),
                      'alias' => array(),
                      // 'force_seq' => false
                  ),
            ),
            'groupTableCols' => array(
                     'required' => array(
                        'group_id'          => array('type' => 'integer', 'name' => 'group_id'),
                        'group_define_name' => array('type' => 'text',    'name' => 'group_define_name'),
                     ),
                     'optional' => array(
                        'is_active'      => array('type' => 'boolean',   'name' => 'is_active'),
                        'owner_user_id'  => array('type' => 'integer',   'name' => 'owner_user_id'),
                        'owner_group_id' => array('type' => 'integer',   'name' => 'owner_group_id')
                     ),
                    'custom' => array (
                     )
                )
        )
    );



$usr = LiveUser::factory($conf);
$handle = isset($_REQUEST['handle']) ? $_REQUEST['handle'] : null;
$password = isset($_REQUEST['passwd']) ? $_REQUEST['passwd'] : null;
$logout = isset($_REQUEST['logout']) ? $_REQUEST['logout'] : null;
$usr->init($handle, $password, $logout);

$admin =& LiveUser_Admin::factory($conf);
$logconf = array('mode' => 0666, 'timeFormat' => '%X %x');
$logger = &Log::factory('file', 'liveuser_test.log', 'ident', $logconf);
$admin->addErrorLog($logger);
$admin->setAdminContainers();

#var_dump::display($admin);

?>
