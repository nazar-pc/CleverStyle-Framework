/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
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
		(new Promise (resolve, reject) !->
			if event && callbacks[event] && callbacks[event].length
				new Callbacks_resolver(callbacks[event], params, resolve, reject)
			else
				resolve()
		)
Object.freeze(cs.Event)
/**
 * Utility callback resolver class
 */
Callbacks_resolver = class
	index	: 0
	(@callbacks, @params, @resolve, @reject) ->
		@execute()
	execute : !->
		callback	= @callbacks[@index]
		++@index
		if !callback
			@resolve()
			return
		result	= callback.apply(callback, @params)
		if result == false
			@reject()
		# We accept either native Promise object or any other object that has `then` method (asumed to be compatible with `Promise.then` interface)
		else if result && result.then instanceof Function
			result.then(@~execute, @reject)
		else
			@resolve()
