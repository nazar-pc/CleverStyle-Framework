/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
csw.behaviors.ready =
	_when_ready : (action) !->
		if document.readyState != 'complete'
			callback	= !->
				setTimeout(action)
				document.removeEventListener('WebComponentsReady', callback)
			document.addEventListener('WebComponentsReady', callback)
		else
			setTimeout(action)
