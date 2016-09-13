<?php
namespace mod\User;
use mod, core, stdClass;
if (!defined('__GOOSE__')) exit();


class View {

	/** @var User $parent */
	public $parent;

	public function __construct($parent)
	{
		$this->name = 'View';
		$this->parent = $parent;

		// set blade class
		$this->blade = new core\Blade();
	}


	/**
	 * check admin
	 */
	private function checkAdmin()
	{
		if (!$this->parent->isAdmin)
		{
			core\Util::back('권한이 없습니다.');
			core\Goose::end();
		}
	}

	/**
	 * view - index
	 */
	public function view_index()
	{
		// make repo
		$repo = new stdClass();
		$repo->user = core\Spawn::items([
			'table' => core\Spawn::getTableName($this->parent->name),
		]);

		// set skin path
		$this->setSkinPath('index');

		// render page
		$this->blade->render($this->parent->skinAddr . '.index', [
			'mod' => $this->parent,
			'repo' => $repo
		]);
	}

	/**
	 * view - create
	 */
	public function view_create()
	{
		// set skin path
		$this->setSkinPath('form');

		// render page
		$this->blade->render($this->parent->skinAddr . '.form', [
			'mod' => $this->parent,
			'action' => $this->parent->params['action'],
			'typeName' => '등록'
		]);
	}

	/**
	 * view - modify
	 */
	public function view_modify()
	{
		// set user srl
		$user_srl = ($this->parent->params['params'][0]) ? (int)$this->parent->params['params'][0] : null;

		// set repo
		$repo = new stdClass();
		$repo->user = core\Spawn::item([
			'table' => core\Spawn::getTableName($this->parent->name),
			'where' => 'srl=' . (int)$user_srl
		]);

		// check self and admin user
		if ($_SESSION['goose_email'] !== $repo->user['email'])
		{
			$this->checkAdmin();
		}

		// set skin path
		$this->setSkinPath('form');

		// play render page
		$this->blade->render($this->parent->skinAddr . '.form', [
			'user_srl' => $user_srl,
			'mod' => $this->parent,
			'repo' => $repo,
			'action' => $this->parent->params['action'],
			'typeName' => '수정'
		]);
	}

	/**
	 * view - remove
	 */
	public function view_remove()
	{
		// set user srl
		$user_srl = ($this->parent->params['params'][0]) ? (int)$this->parent->params['params'][0] : null;

		// check admin
		$this->checkAdmin();

		// set repo
		$repo = new stdClass();
		$repo->user = core\Spawn::item([
			'table' => core\Spawn::getTableName($this->parent->name),
			'where' => 'srl=' . (int)$user_srl
		]);

		// set skin path
		$this->setSkinPath('remove');

		// play render page
		$this->blade->render($this->parent->skinAddr . '.remove', [
			'app_srl' => $user_srl,
			'mod' => $this->parent,
			'repo' => $repo,
			'user_srl' => $user_srl,
			'action' => $this->parent->params['action'],
			'typeName' => '삭제'
		]);
	}

	/**
	 * set skin path
	 *
	 * @param string $type
	 * @param string $userSkin
	 */
	private function setSkinPath($type, $userSkin=null)
	{
		// check blade file
		$bladeResult = core\Blade::isFile(__GOOSE_PWD__ . 'mod', $type, [
			$this->parent->name . '.skin.' . $_GET['skin'],
			$this->parent->name . '.skin.' . $userSkin,
			$this->parent->name . '.skin.' . $this->parent->set['skin'],
			$this->parent->name . '.skin.default'
		]);

		// set blade and file path
		$this->parent->skinAddr = $bladeResult['address'];
		$this->parent->skinPath = ($bladeResult['path']) ? 'mod/' . $bladeResult['path'] . '/' : '';
	}
}