###*
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
###
 # Load configuration from special template elements
###
callbacks = {}
e =
	'on'  : (action, callback) ->
		if !action || !callback
			return e
		if !callbacks[action]
			callbacks[action] = []
		callbacks[action].push(callback)
		e
	'off' : (action, callback) ->
		if !callbacks[action]
			return e
		if !callback
			delete callbacks[action]
			return e
		callbacks[action] = callbacks[action].filter (c) ->
			c != callback
		e
	once  : (action, callback) ->
		if !action || !callback
			return e
		callback_ = ->
			e.off(action, callback_)
			callback.apply(callback, arguments)
		e.on(action, callback_)
	fire  : (action, param1, _) ->
		if !action || !callbacks[action]
			return true
		args = Array::slice.call(arguments, 1)
		for callback, index in callbacks[action]
			if callback.apply(callback, args) == false
				return false
		true
cs.Event = e
