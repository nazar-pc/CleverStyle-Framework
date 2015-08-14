###*
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
###
 # Events system similar to one found on backend, including available methods and arguments order
###
(->
	callbacks	= {}
	@on			= (event, callback) ->
		if !event || !callback
			return @
		if !callbacks[event]
			callbacks[event] = []
		callbacks[event].push(callback)
		@
	@off		= (event, callback) ->
		if !callbacks[event]
			return @
		if !callback
			delete callbacks[event]
			return @
		callbacks[event] = callbacks[event].filter (c) ->
			c != callback
		@
	@once		= (event, callback) ->
		if !event || !callback
			return @
		callback_ = =>
			@off(event, callback_)
			callback.apply(callback, arguments)
		@on(event, callback_)
	@fire		= (event, params...) ->
		if !event || !callbacks[event]
			return true
		for callback, index in callbacks[event]
			if callback.apply(callback, params) == false
				return false
		true
).call(cs.Event = {})
