<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server\cli;
use
	React,
	cs\App,
	cs\ExitException,
	cs\Page,
	cs\User,
	cs\Response as System_response,
	cs\modules\Psr7\Request,
	cs\modules\Psr7\Response,
	Psr\Http\Message\ServerRequestInterface;

class Controller {
	public static function index_help () {
		return <<<HELP
<y>Http server module</y>

<y>Methods:</y>
  <g>run_server</g> Prints all cli paths and methods available for specified path
  <g>run_pool</g>   Displays help for module or path (should be provided by developer, otherwise will fallback to <g>cli</g>)

<y>Arguments:</y>
  <g>port</g>  Required for <g>run_server</g>, specifies port on which server will be running
  <g>ports</g> Required for <g>run_pool</g>, specifies ports on which server will be running (coma-separated list of ports or ports ranged separated by -)

<y>Examples:</y>
  Run HTTP server on port 8080:
    <g>./cli run_server:Http_server port=8080</g>
  Run pool of HTTP servers on ports 8080, 8081 and range of ports 8082-8087:
    <g>./cli run_pool:Http_server ports=8080,8081,8082-8087</g>

HELP;
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function index_run_server ($Request) {
		$port = $Request->query('port');
		if (!$port) {
			throw new ExitException('Port is required', 400);
		}
		$_SERVER['SERVER_SOFTWARE'] = 'ReactPHP';
		$memory_cache_disabled      = false;
		$app                        = function (ServerRequestInterface $request) use (&$memory_cache_disabled) {
			try {
				System_response::instance()->init_with_typical_default_settings();
				Request::init_from_psr7($request);
				App::instance()->execute();
				if (!$memory_cache_disabled) {
					$memory_cache_disabled = true;
					User::instance()->disable_memory_cache();
				}
			} catch (ExitException $e) {
				if ($e->getCode() >= 400) {
					Page::instance()->error($e->getMessage() ?: null, $e->getJson());
				}
			}
			return Response::output_to_psr7(new React\Http\Response);
		};

		$loop   = React\EventLoop\Factory::create();
		$socket = new React\Socket\Server($port, $loop);
		$http   = new React\Http\StreamingServer([new React\Http\Middleware\RequestBodyBufferMiddleware(), $app]);
		$http->listen($socket);
		$loop->run();
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function index_run_pool ($Request) {
		$ports = $Request->query('ports');
		if (!$ports) {
			throw new ExitException('Ports are required', 400);
		}
		$ports = static::prepare_ports($ports);
		foreach ($ports as $p) {
			static::cross_platform_server_in_background($p);
		}
		echo "Pool of Http servers started!\n";
	}
	/**
	 * @param string $ports
	 *
	 * @return int[]
	 */
	protected static function prepare_ports ($ports) {
		$result_ports = [];
		foreach (explode(',', $ports) as $p) {
			if (strpos($p, '-') !== false) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$result_ports = array_merge($result_ports, range(...explode('-', $p)));
			} else {
				$result_ports[] = $p;
			}
		}
		sort($result_ports);
		return $result_ports;
	}
	/**
	 * Running Http server in background on any platform
	 *
	 * @param int $port
	 */
	protected static function cross_platform_server_in_background ($port) {
		$dir        = dirname(__DIR__);
		$supervisor = "php $dir/supervisor.php";
		$cmd        = escapeshellarg(PHP_BINARY.' '.DIR."/cli run_server:Http_server port=$port");
		if (strpos(PHP_OS, 'WIN') === false) {
			exec("$supervisor $cmd > /dev/null &");
		} else {
			pclose(popen("start /B $supervisor $cmd", 'r'));
		}
	}
}
