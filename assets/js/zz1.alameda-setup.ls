/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
requirejs.config(
	baseUrl		: '/'
	urlArgs		: (id, url) ->
		for path, hash of requirejs.contexts._.config.hashes
			if url.indexOf(path) == 0
				return (if url.indexOf('?') == -1 then '?' else '&') + hash
		''
	waitSeconds	: 60
)
define('async-eventer', -> window.async_eventer)
