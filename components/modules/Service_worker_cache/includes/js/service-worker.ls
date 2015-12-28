/**
 * @package   Service worker cache
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# We are not going to include polyfill here, just exit if cache is not available
if !caches
	return
addEventListener('fetch', (event) !->
	event.respondWith(
		caches.match(event.request).then (response) ->
			if response
				console.log 'in cache'
				response
			else
				request_copy = event.request.clone()
				fetch(request_copy).then (response) ->
					if response && response.status == 200 && response.type == 'basic'
						path = response.url.match(/:\/\/[^/]+\/(.+)$/)?[1]
						# Only cache relevant URLs
						if path && path.match(/^components|includes|storage\/pcache|themes/)
							response_copy = response.clone()
							caches.open('frontend-cache').then (cache) !->
								cache.put(event.request, response_copy)
					response
	)
)
