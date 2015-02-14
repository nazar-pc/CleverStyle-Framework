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
	'on'  : (event, callback) ->
		if !event || !callback
			return e
		if !callbacks[event]
			callbacks[event] = []
		callbacks[event].push(callback)
		e
	'off' : (event, callback) ->
		if !callbacks[event]
			return e
		if !callback
			delete callbacks[event]
			return e
		callbacks[event] = callbacks[event].filter (c) ->
			c != callback
		e
	once  : (event, callback) ->
		if !event || !callback
			return e
		callback_ = ->
			e.off(event, callback_)
			callback.apply(callback, arguments)
		e.on(event, callback_)
	fire  : (event, param1, _) ->
		if !event || !callbacks[event]
			return true
		args = Array::slice.call(arguments, 1)
		for callback, index in callbacks[event]
			if callback.apply(callback, args) == false
				return false
		true
cs.Event = e
