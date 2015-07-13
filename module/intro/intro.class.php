<?php
if (!defined('__GOOSE__')) exit();

/**
 * Module - intro
 *
 */

class Intro {

	public $name, $param, $set, $layout;
	public $path, $pwd_container;

	/**
	 * construct
	 *
	 * @param array $getter
	 */
	public function __construct($getter=array())
	{
		$this->name = $getter['name'];
		$this->goose = $getter['goose'];
		$this->isAdmin = $getter['isAdmin'];
		$this->param = $getter['param'];
		$this->path = $getter['path'];
		$this->set = $getter['set'];

		$this->skinPath = $this->path.'skin/'.$this->set['skin'].'/';
	}

	/**
	 * index method
	 */
	public function index()
	{
		// create layout module
		$this->layout = Module::load('layout');

		// set pwd_container
		$this->pwd_container = __GOOSE_PWD__.$this->skinPath.'view_index.html';

		// require layout
		require_once($this->layout->getUrl());
	}

}