/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-comments-comment'
	behaviors	: [
		cs.Polymer.behaviors.computed_bindings
		cs.Polymer.behaviors.Language('comments_')
	]
	properties	:
		comment			: Object
		parent-comment	: Object
		date			:
			type	: String
			computed	: '_date(comment.date, comment.date_formatted, comment.time_formatted)'
		date_iso8601	:
			type	: String
			computed	: '_date_iso8601(comment.date)'
		location		: location.pathname
		reply			: Object
		editing			: false
		replying		: false
	ready : !->
		@_this	= @
		@scopeSubtree(@$.text, true)
	attached : !->
		if @comment.scroll_to
			setTimeout(@~_scroll_to, 300)
	_scroll_to : !->
		# TODO: Add nice scroll without jQuery and much code:)
		document.querySelector('html').scrollTop	= @offsetTop
	_scroll_to_parent : !->
		@parent-comment._scroll_to()
	_date : (date, date_formatted, time_formatted) ->
		if Math.abs(date - (new Date).getTime() / 1000) < 3600s * 24h
			time_formatted
		else
			date_formatted
	_date_iso8601 : (date) ->
		d = new Date
		d.setTime(date * 1000)
		d.toISOString()
	reload : !->
		@parent-comment.reload()
	_edit	: !->
		@set('comment.edited_text', @comment.text)
		@editing	= true
	_save_edit : !->
		cs.api('put api/Comments/' + @comment.id, {text : @comment.edited_text}).then !~>
			cs.ui.notify(@L.saved, 'success', 5)
			@reload()
			@_cancel_edit()
	_cancel_edit : !->
		@editing	= false
	_reply : !->
		if !@comment.can_reply
			return
		@set(
			'reply'
			parent	: @comment.id
			module	: @comment.module
			item	: @comment.item
			text	: ''
		)
		@replying = true
	_post_reply : !->
		cs.api('post api/Comments', @reply).then !~>
			cs.ui.notify(@L.reply_posted, 'success', 5)
			@reload()
			@_cancel_reply()
	_cancel_reply : !->
		@replying	= false
	_delete : !->
		cs.ui.confirm(@L.sure_to_delete)
			.then ~> cs.api('delete api/Comments/' + @comment.id)
			.then !~>
				cs.ui.notify(@L.deleted, 'success', 5)
				@reload()
)
