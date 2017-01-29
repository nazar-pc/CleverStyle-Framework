/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
requirejs.config(
	baseUrl		: '/'
	urlArgs		: (id, url) ->
		for path, hash of requirejs.contexts._.config.hashes
			if url.indexOf(path) == 0
				return (if url.indexOf('?') === -1 then '?' else '&') + hash
		''
	waitSeconds	: 60
)
