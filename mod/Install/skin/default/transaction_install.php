<?php
use Core\Util;
use Core\Goose;
use Core\Spawn;

if (!defined('__GOOSE__')) exit();

global $goose;


/**
 * Check $_POST
 *
 * @return Boolean : 이상이 없으면 true, 문제가 있으면 false값을 리턴한다.
 */
function checkPost()
{
	$errorValue = Util::checkExistValue($_POST, [ 'goose_url', 'dbId', 'dbName', 'email', 'name' ]);
	if ($errorValue)
	{
		Util::back("[$errorValue]값이 없습니다.");
		Goose::end();
	}
	if (!$_POST['dbPassword'] || ($_POST['dbPassword'] != $_POST['dbPassword2']))
	{
		Util::back("DB 비밀번호와 확인값이 다릅니다.");
		return false;
	}
	if (!$_POST['password'] || ($_POST['password'] != $_POST['password2']))
	{
		Util::back("관리자 비밀번호와 확인값이 다릅니다.");
		return false;
	}
	return true;
}


// check post data
if ( checkPost() == true )
{
	// change permission goose root
	$root_permission = Util::getPermission(__GOOSE_PWD__);
	if ($root_permission != '0777' && $root_permission != '0707')
	{
		if (!chmod(__GOOSE_PWD__, 0707))
		{
			echo '<p>Please change the permission of `'.__GOOSE_PWD__.'` to 707 folder.</p>';
			Goose::end();
		}
	}

	// check writable directory
	if (!is_writable(__GOOSE_PWD__))
	{
		Goose::error(101, 'Not a writable directory.');
	}

	// create directories
	Util::createDirectory(__GOOSE_PWD__."data", 0755);
	Util::createDirectory(__GOOSE_PWD__."data/settings", 0755);
	Util::createDirectory(__GOOSE_PWD__."data/cache", 0755);

	// create config.php
	$tpl_config = $this->tpl_config([
		'define' => [
			'url' =>$_POST['goose_url']
			,'root' => ($_POST['goose_root']) ? $_POST['goose_root'] : ''
		]
		,'db' => [
			'dbname' => $_POST['dbName']
			,'name' => $_POST['dbId']
			,'password' => $_POST['dbPassword']
			,'host' => $_POST['dbHost']
			,'port' => $_POST['dbPort']
			,'prefix' => $_POST['dbPrefix']
		]
		,'level' => [
			'login' => $_POST['loginLevel']
			,'admin' => $_POST['adminLevel']
		]
		,'apiKey' => $_POST['apiPrefix']
		,'timezone' => $_POST['timezone']
		,'basic_module' => 'Intro'
	]);
	if ($tpl_config != 'success')
	{
		Goose::error(101, 'Failed to create the file data/config.php');
	}

	// create modules.json
	if ($this->tpl_modules() != 'success')
	{
		Goose::error(101, 'Failed to create the file data/modules.json');
	}
}
else
{
	Goose::end();
}


// load config file
require_once(__GOOSE_PWD__.'data/config.php');


// create and connect database
$goose->createSpawn();
$goose->spawn->connect($dbConfig);
$goose->spawn->prefix = $table_prefix;


// set admin
$goose->isAdmin = true;


// install modules
$arr = [ 'User', 'Nest', 'App', 'JSON', 'File', 'Article', 'Category' ];
foreach($arr as $k=>$v)
{
	$result = $this->installModule($v);
	echo "<p>Create table - ".$result['message']."</p>";
}


// add admin user
$result = Spawn::insert([
	'table' => Spawn::getTableName('User'),
	'data' => [
		'srl' => null,
		'email' => $_POST['email'],
		'name' => $_POST['name'],
		'pw' => md5($_POST['password']),
		'level' => $_POST['adminLevel'],
		'regdate' => date("YmdHis")
	]
]);
echo "<p>Add admin user - ".(($result == 'success') ? 'Complete' : "<strong style='color: red; font-weight: bold;'>ERROR : $result</strong>")."</p>";


// add basic navigation on json table
$cnt = Spawn::count([
	'table' => Spawn::getTableName('JSON'),
	'where' => "name='Goose Navigation'"
]);
if (!$cnt)
{
	$data = Util::checkUserFile(__GOOSE_PWD__.'bootstrap/misc/navigationTree.json');
	$data = Util::openFile($data);
	$data = Util::jsonToArray($data, true, true);
	$data = Util::arrayToJson($data, true);
	$result = Spawn::insert([
		'table' => Spawn::getTableName('JSON'),
		'data' => [
			'srl' => null,
			'name' => 'Goose Navigation',
			'json' => $data,
			'regdate' => date("YmdHis")
		]
	]);
}
else
{
	$result = '"Goose Navigation" Data already exists.';
}
echo "<p>Add json data - ".(($result == 'success') ? 'Complete' : "<strong style='color: red; font-weight: bold;'>ERROR : $result</strong>")."</p>";


echo "<hr/>";
echo "<h1>END INSTALL</h1>";
echo "<nav><a href=\"".__GOOSE_ROOT__."\">Go to intro page</a></nav>";

Goose::end();