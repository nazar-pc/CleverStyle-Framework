/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Events system similar to one found on backend, including available methods and arguments order, but instead of returning boolean it returns Promise instance
 * Similarly, callbacks may return either boolean result or no result (just like on backend) or Promise instance or any other object that has compatible `then`
 * method (jQuery Deferred as example)
 */
cs.Event = do
	callbacks	= {}
	on : (event, callback) ->
		if event && callback
			if !callbacks[event]
				callbacks[event] = []
			callbacks[event].push(callback)
		@
	off : (event, callback) ->
		if !callbacks[event]
			void # Do nothing if event argument is missing
		else if !callback
			delete callbacks[event]
		else
			callbacks[event] = callbacks[event].filter (c) ->
				c != callback
		@
	once : (event, callback) ->
		if event && callback
			callback_ = ~>
				@off(event, callback_)
				callback.apply(callback, arguments)
			@on(event, callback_)
		@
	fire : (event, ...params) ->
		if event && callbacks[event] && callbacks[event].length
			resolver	= new Callbacks_resolver(callbacks[event], params)
			resolver.execute()
		else
			Promise.resolve()
Object.freeze(cs.Event)
/**
 * Utility callback resolver class
 */
Callbacks_resolver = class
	index	: 0
	(@callbacks, @params) ->
	execute : ->
		callback	= @callbacks[@index]
		++@index
		if !callback
			Promise.resolve()
		else
			result	= callback.apply(callback, @params)
			if result == false
				Promise.reject()
			else
				Promise.resolve(result).then(@~execute)
