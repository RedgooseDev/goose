<?php
if (!defined('__GOOSE__')) exit();

// check user
if (!$this->isAdmin && ($post['email'] != $_SESSION['goose_email']))
{
	return array(
		'state' => 'error',
		'action' => 'back',
		'message' => '권한이 없습니다.'
	);
}


// remove data
$result = Spawn::delete(array(
	'table' => Spawn::getTableName($this->name),
	'where' => 'srl='.$post['user_srl']
));
if ($result != 'success')
{
	return array(
		'state' => 'error',
		'action' => 'back',
		'message' => 'Fail execution database'
	);
}


// redirect url
return array(
	'state' => 'success',
	'action' => 'redirect',
	'url' => __GOOSE_ROOT__.$this->name.'/index/'
);