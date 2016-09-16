<?php
if (!defined('__GOOSE__')) exit();

/** @var array $post */


// check post
$errorValue = core\Util::checkExistValue($post, [ 'name', 'id', 'json' ]);
if ($errorValue)
{
	return [
		'state' => 'error',
		'action' => 'back',
		'message' => '[' . $errorValue . ']값이 없습니다.'
	];
}


// 아이디값 중복 확인
$cnt = core\Spawn::count([
	'table' => core\Spawn::getTableName($this->name),
	'where' => "id='$post[id]'"
]);
if ($cnt > 0)
{
	return [
		'state' => 'error',
		'action' => 'back',
		'message' => 'id가 이미 존재합니다.'
	];
}


// insert data
$result = core\Spawn::insert([
	'table' => core\Spawn::getTableName($this->name),
	'data' => [
		'srl' => null,
		'app_srl' => (int)$post['app_srl'],
		'id' => $post['id'],
		'name' => $post['name'],
		'json' => $post['json'],
		'regdate' => date('YmdHis')
	]
]);
if ($result != 'success')
{
	return [
		'state' => 'error',
		'action' => 'back',
		'message' => 'Fail execution database'
	];
}


// redirect url
$params = ($_SESSION['app_srl']) ? $_SESSION['app_srl'] . '/' : '';
return [
	'state' => 'success',
	'action' => 'redirect',
	'url' => __GOOSE_ROOT__ . '/' . $this->name . '/index/' . $params
];