<?php
/**
 * @package  Psr15
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Psr15;
use
	cs\App,
	cs\ExitException,
	cs\Page,
	cs\Response as System_response,
	cs\User,
	cs\modules\Psr7\Request,
	cs\modules\Psr7\Response,
	Psr\Http\Server\MiddlewareInterface,
	Psr\Http\Server\RequestHandlerInterface,
	Psr\Http\Message\ServerRequestInterface,
	Psr\Http\Message\ResponseInterface;

class Middleware implements MiddlewareInterface {
	private $memory_cache_disabled = false;
	/**
	 * @var string
	 */
	protected $psr7_response_class_name;
	/**
	 * @param string $psr7_response_class_name
	 */
	public function __construct ($psr7_response_class_name) {
		$this->psr7_response_class_name = $psr7_response_class_name;
	}
	/**
	 * @inheritdoc
	 */
	public function process (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		try {
			System_response::instance()->init_with_typical_default_settings();
			Request::init_from_psr7($request);
			App::instance()->execute();
			if (!$this->memory_cache_disabled) {
				$this->memory_cache_disabled = true;
				User::instance()->disable_memory_cache();
			}
		} catch (ExitException $e) {
			if ($e->getCode() >= 400) {
				Page::instance()->error($e->getMessage() ?: null, $e->getJson());
			}
		}
		return Response::output_to_psr7(new $this->psr7_response_class_name);
	}
}
