/**
 * @package  Service worker cache
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
if navigator.serviceWorker
	navigator.serviceWorker.register(
		'/modules/Service_worker_cache/assets/js/service-worker.js?' + cs.service_worker_cache.version
		scope	: '/'
	)
