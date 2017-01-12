/**
 * @package   Service worker cache
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
if navigator.serviceWorker
	navigator.serviceWorker.register(
		'/modules/Service_worker_cache/assets/js/service-worker.js?' + cs.service_worker_cache.version
		scope	: '/'
	)
