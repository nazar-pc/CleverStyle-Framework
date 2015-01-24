###*
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
window.cs.ws = do ->
	handlers			= {}
	methods				=
		'on'	: (action, callback, error) ->
			if !handlers[action]
				handlers[action]	= []
			handlers[action].push([callback, error])
			return
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
			return
		send	: (action, details) ->
			socket.send(
				JSON.stringify([action, details])
			)
	socket				= new WebSocket(
		do ->
			proto	= if location.protocol == 'https:' then 'wss' else 'ws'
			"#{proto}://#{location.host}/WebSockets"
	)
	socket.onopen		= ->
		methods.send(
			'Client/authentication'
			session		: cs.getcookie('session')
			user_agent	: navigator.userAgent
		)
	socket.onmessage	= (message) ->
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
	methods
