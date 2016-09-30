#!/opt/php5-6/bin/php
<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
$opts = getopt('a::', array('app::','debug::'));
if (!empty($opts['a'])) {
	$applicationName = $opts['a'];
} elseif(!empty($opts['app'])) {
	$applicationName = $opts['app'];
}

if (empty($applicationName)) {
	die('You must specify an application name');
}

$className = 'Applications\\' . $applicationName . '\\' . $applicationName;

CUIF::StartApplication($className, !empty($opts['debug']));
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

