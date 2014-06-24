<?
if(!defined("GOOSE")){exit();}

// error reporting
if(version_compare(PHP_VERSION, '5.4.0', '<'))
{
	//@error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
}
else
{
	//@error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING ^ E_STRICT);
}

// init Util class
require_once(PWD.'/libs/Util.class.php');
$util = new Util();

// check install
if (!file_exists(PWD."/data/config/user.php"))
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		require_once(PWD.'/transaction/install.php');
	}
	else
	{
		require_once(PWD.'/pages/install.php');
	}
	$util->out();
}


require_once(PWD.'/data/config/user.php');
require_once(PWD.'/libs/variable.php');
require_once(PWD.'/libs/Database.class.php');
require_once(PWD.'/libs/Spawn.class.php');
require_once(PWD.'/libs/Router.class.php');
require_once(PWD.'/libs/functions.php');

// init router
$router = new Router();

// create instanse object
$spawn = new Spawn($dbConfig);

// route setting
$router->setBasePath(ROOT);
require_once(PWD.'/libs/route.maps.php');
$route = $router->matchCurrentRequest();

// route action
if ($route)
{
	$routePapameters = $route->getParameters();
	$routeTarget = $route->getTarget();
	$routeMethod = $route->getMethods();

	$paramController = $routePapameters['controller'];
	$paramAction = $routePapameters['action'];

	switch ($routeTarget['type'])
	{
		case "api":
			$file = PWD.'/api/'. $routePapameters[type] .'.php';
			if (is_file($file))
			{
				require($file);
			}
			else
			{
				$util->error(404);
				$util->out();
			}
			break;

		default:
			// login check
			if (!$_SESSION['gooseEmail'] and $paramController != 'auth' and !$routeTarget['type'])
			{
				$containerDirectory = PWD.'/pages/auth.php';
				require(PWD.'/pages/layout.php');
				$util->out();
			}
			if ($paramController == 'auth' and $paramAction == 'logout')
			{
				require(PWD.'/transaction/auth.php');
				$util->out();
			}

			$paramController = ($paramController) ? $paramController : 'index';
			$subdir = ($routeMethod[0] == 'POST') ? 'transaction' : 'pages';
			$containerDirectory = PWD.'/'.$subdir.'/'.$paramController.'.php';
			if (($routeMethod[0] == 'POST'))
			{
				if (is_file($containerDirectory))
				{
					require($containerDirectory);
				}
				else
				{
					$util->error(404);
					$util->out();
				}
			}
			else
			{
				if (is_file($containerDirectory))
				{
					require(PWD.'/pages/layout.php');
				}
				else
				{
					$util->error(404);
					$util->out();
				}
			}
			break;
	}
}
else
{
	$util->error(404);
	$util->out();
}

$util->out();
?>