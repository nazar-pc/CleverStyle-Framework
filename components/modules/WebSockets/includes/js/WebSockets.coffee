###*
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
window.cs.WebSockets = do ->
	socket		= null
	handlers	= {}
	do ->
		delay			= 0
		onopen			= ->
			delay	= 1000
			cs.WebSockets.send(
				'Client/authentication'
				session		: cs.getcookie('session')
				user_agent	: navigator.userAgent
				language	: cs.Language.clanguage
			)
			return
		onmessage		= (message) ->
			[action, details]	= JSON.parse(message.data)
			[action, type]	= action.split(':')
			action_handlers		= handlers[action]
			if !action_handlers || !action_handlers.length
				return
			if typeof details in ['boolean', 'number', 'string']
				details	= [details]
			action_handlers.forEach (h) ->
				if type == 'error'
					h[1] && h[1].apply(h[1], details)
				else
					h[0] && h[0].apply(h[0], details)
			return
		connect			= ->
			socket				= new WebSocket(
				(if location.protocol == 'https:' then 'wss' else 'ws') + "://#{location.hostname}/WebSockets"
			)
			socket.onopen		= onopen
			socket.onmessage	= onmessage
			return
		keep_connection	= ->
			setTimeout (->
				if !socket || socket.readyState in [WebSocket.CLOSING, WebSocket.CLOSED]
					delay	= (delay || 1000) * 2
					connect()
				keep_connection()
			), delay
		keep_connection()
	{
		'on'	: (action, callback, error) ->
			if !handlers[action]
				handlers[action]	= []
			handlers[action].push([callback, error])
			cs.WebSockets
		'off'	: (action, callback, error) ->
			if !handlers[action]
				return
			for h, index in handlers[action]
				if h[0] == callback
					h[0] = undefined
				if h[1] == error
					h[1] = undefined
				if h[0] == undefined && h[1] == undefined
					delete handlers[action][index]
			cs.WebSockets
		once	: (action, callback, error) ->
			callback_	= ->
				callback.apply(callback, arguments)
				cs.WebSockets.off(action, callback_, error_)
			error_		= ->
				error.apply(error, arguments)
				cs.WebSockets.off(action, callback_, error_)
			cs.WebSockets.on(action, callback_, error_)
			cs.WebSockets
		send	: (action, details) ->
			socket.send(
				JSON.stringify([action, details])
			)
			cs.WebSockets
	}
