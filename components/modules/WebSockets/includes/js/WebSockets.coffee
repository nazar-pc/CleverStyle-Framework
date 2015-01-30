###*
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
socket			= null
handlers		= {}
messages_pool	= []
allow_reconnect = true
socket_active	= ->
	socket && socket.readyState not in [WebSocket.CLOSING, WebSocket.CLOSED]
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
		while messages_pool.length
			cs.WebSockets.send(messages_pool.shift())
		return
	onmessage		= (message) ->
		[action, details]	= JSON.parse(message.data)
		[action, type]	= action.split(':')
		# Special system actions
		switch action
			when 'Server/close'
				allow_reconnect = false
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
			(if location.protocol == 'https:' then 'wss' else 'ws') + "://#{location.host}/WebSockets"
		)
		socket.onopen		= onopen
		socket.onmessage	= onmessage
		return
	keep_connection	= ->
		setTimeout (->
			if !allow_reconnect
				return
			if !socket_active()
				delay	= (delay || 1000) * 2
				connect()
			keep_connection()
		), delay
	keep_connection()
w =
	'on'	: (action, callback, error) ->
		if !action || (!callback && !error)
			return w
		if !handlers[action]
			handlers[action]	= []
		handlers[action].push([callback, error])
		w
	'off'	: (action, callback, error) ->
		if !handlers[action]
			return w
		handlers[action] = handlers[action].filter (h) ->
			if h[0] == callback
				delete h[0]
			if h[1] == error
				delete h[1]
			h[0] || h[1]
		w
	once	: (action, callback, error) ->
		if !action || (!callback && !error)
			return w
		callback_	= ->
			w.off(action, callback_, error_)
			callback.apply(callback, arguments)
		error_		= ->
			w.off(action, callback_, error_)
			error.apply(error, arguments)
		w.on(action, callback_, error_)
	send	: (action, details) ->
		if !action
			return w
		message	= JSON.stringify([action, details])
		if !socket_active()
			messages_pool.push(message)
		else
			socket.send(message)
		w
cs.WebSockets = w
