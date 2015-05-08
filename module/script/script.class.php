<?php
if (!defined('__GOOSE__')) exit();

/**
 * Module - script
 */
class Script
{
	public $name, $goose, $layout, $isAdmin, $param, $set;
	public $path, $skinPath, $runPath;

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
		$this->runPath = 'module/script/run/';
	}

	/**
	 * index method
	 */
	public function index()
	{
		if ($this->param['action'] == 'run')
		{
			$result = $this->run($this->param['params'][0]);
			if ($result) Module::afterAction($result);
		}
		else
		{
			require_once(__GOOSE_PWD__.$this->path.'view.class.php');
			$view = new View($this);
			$view->render();
		}
	}


	/**
	 * run
	 *
	 * @param string $name
	 * @return array
	 */
	public function run($name)
	{
		if ($this->name != 'script') return array( 'state' => 'error', 'message' => '잘못된 객체로 접근했습니다.' );

		// set run path
		$path = $this->runPath.$name.'/';

		// check run file
		if (!file_exists(__GOOSE_PWD__.$path.'run.php'))
		{
			return array( 'state' => 'error', 'message' => '실행코드 파일이 없습니다.' );
		}

		// get meta data
		$meta = Util::jsonToArray(Util::openFile(__GOOSE_PWD__.$path.'meta.json'), null, true);

		return (require_once(__GOOSE_PWD__.$path.'run.php'));
	}
}