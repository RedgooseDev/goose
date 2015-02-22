<?php
if(!defined("GOOSE")){exit();}

$allowApiData = array(
	'articles' => array(
		'srl'
		,'group_srl'
		,'nest_srl'
		,'category_srl'
		,'title'
		,'content'
		,'regdate'
		,'modate'
		,'hit'
		,'json'
		,'ipAddress'
	)
	,'categories' => array(
		'srl'
		,'nest_srl'
		,'turn'
		,'name'
		,'regdate'
	)
	,'files' => array(
		'srl'
		,'article_srl'
		,'name'
		,'loc'
	)
	,'jsons' => array(
		'srl'
		,'name'
		,'json'
		,'regdate'
	)
	,'nestGroups' => array(
		'srl'
		,'name'
		,'regdate'
	)
	,'nests' => array(
		'srl'
		,'group_srl'
		,'id'
		,'name'
		,'listCount'
		,'useCategory'
		,'json'
		,'regdate'
	)
	,'tempFiles' => array(
		'srl'
		,'loc'
		,'name'
		,'date'
	)
	,'users' => array(
		'srl'
		,'name'
		,'email'
		,'level'
		,'regdate'
	)
);
?>