<?php
use core;
if (!defined('__GOOSE__')) exit();

global $goose;


/**
 * Check $_POST
 *
 * @return Boolean : 이상이 없으면 true, 문제가 있으면 false값을 리턴한다.
 */
function checkPost()
{
	$errorValue = core\Util::checkExistValue($_POST, [ 'goose_url', 'dbId', 'dbName', 'email', 'name' ]);
	if ($errorValue)
	{
		core\Util::back("[$errorValue]값이 없습니다.");
		core\Goose::end();
	}
	if (!$_POST['dbPassword'])
	{
		core\Util::back("DB 비밀번호값이 없습니다.");
		return false;
	}
	if (!$_POST['password'] || ($_POST['password'] != $_POST['password2']))
	{
		core\Util::back("관리자 비밀번호와 확인값이 다릅니다.");
		return false;
	}
	return true;
}


// check post data
if ( checkPost() == true )
{
	// change permission goose root
	$root_permission = core\Util::getPermission(__GOOSE_PWD__);
	if ($root_permission != '0777' && $root_permission != '0707')
	{
		if (!chmod(__GOOSE_PWD__, 0707))
		{
			echo '<p>Please change the permission of `' . __GOOSE_PWD__ . '` to 707 folder.</p>';
			core\Goose::end();
		}
	}

	// check writable directory
	if (!is_writable(__GOOSE_PWD__))
	{
		core\Goose::error(101, 'Not a writable directory.');
	}

	// create directories
	core\Util::createDirectory(__GOOSE_PWD__."data", 0707);
	core\Util::createDirectory(__GOOSE_PWD__."data/settings", 0707);
	core\Util::createDirectory(__GOOSE_PWD__."data/cache", 0707);

	// create config.php
	$tpl_config = core\Util::createConfig([
		'define' => [
			'url' =>$_POST['goose_url'],
			'root' => ($_POST['goose_root']) ? $_POST['goose_root'] : ''
		],
		'db' => [
			'dbname' => $_POST['dbName'],
			'name' => $_POST['dbId'],
			'password' => $_POST['dbPassword'],
			'host' => $_POST['dbHost'],
			'port' => $_POST['dbPort'],
			'prefix' => $_POST['dbPrefix']
		],
		'level' => [
			'login' => $_POST['loginLevel'],
			'admin' => $_POST['adminLevel']
		],
		'apiKey' => $_POST['apiPrefix'],
		'timezone' => $_POST['timezone'],
		'basic_module' => 'Intro'
	]);
	if ($tpl_config != 'success')
	{
		core\Goose::error(101, 'Failed to create the file data/config.php');
	}

	// create modules.json
	if ($this->tpl_modules() != 'success')
	{
		core\Goose::error(101, 'Failed to create the file data/modules.json');
	}
}
else
{
	core\Goose::end();
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
$result = core\Spawn::insert([
	'table' => core\Spawn::getTableName('User'),
	'data' => [
		'srl' => null,
		'email' => $_POST['email'],
		'name' => $_POST['name'],
		'pw' => password_hash($_POST['password'], PASSWORD_DEFAULT),
		'level' => $_POST['adminLevel'],
		'regdate' => date("YmdHis")
	]
]);
echo "<p>Add admin user - ".(($result == 'success') ? 'Complete' : "<strong style='color: red; font-weight: bold;'>ERROR : $result</strong>")."</p>";


// add basic navigation on json table
$cnt = core\Spawn::count([
	'table' => core\Spawn::getTableName('JSON'),
	'where' => "name='Goose Navigation'"
]);
if (!$cnt)
{
	$data = core\Util::checkUserFile(__GOOSE_PWD__ . $this->skinPath . 'misc/navigationTree.json');
	$data = core\Util::openFile($data);
	$data = core\Util::jsonToArray($data, true, true);
	$data = core\Util::arrayToJson($data, true);
	$result = core\Spawn::insert([
		'table' => core\Spawn::getTableName('JSON'),
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
	$result = '`Goose Navigation` Data already exists.';
}

echo '<p>Add json data - ' . (($result == 'success') ? 'Complete' : '<strong style="color: red; font-weight: bold;">ERROR : ' . $result . '</strong>') . '</p>';


echo "<hr/>";
echo "<h1>END INSTALL</h1>";
echo '<nav><a href="' . __GOOSE_ROOT__ . '"><b>Go to intro page</b></a></nav>';

core\Goose::end();