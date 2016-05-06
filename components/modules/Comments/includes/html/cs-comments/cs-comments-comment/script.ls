/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# TODO: handle comments anchors and scroll page correspondingly
Polymer(
	'is'		: 'cs-comments-comment'
	behaviors	: [
		cs.Polymer.behaviors.cs
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
		$.ajax(
			url		: 'api/Comments/' + @comment.id
			type	: 'put'
			data	:
				text	: @comment.edited_text
			success	: !~>
				# TODO: success notification
				@reload()
				@_cancel_edit()
		)
	_cancel_edit : !->
		@editing	= false
	_reply : !->
		@set(
			'reply'
			parent	: @comment.id
			module	: @comment.module
			item	: @comment.item
			text	: ''
		)
		@replying = true
	_post_reply : !->
		$.ajax(
			url		: 'api/Comments'
			type	: 'post'
			data	: @reply
			success	: !~>
				# TODO: success notification
				@reload()
				@_cancel_reply()
		)
	_cancel_reply : !->
		@replying	= false
	_delete : !->
		# TODO: confirmation dialog
		$.ajax(
			url		: 'api/Comments/' + @comment.id
			type	: 'delete'
			success	: !~>
				# TODO: success notification
				@reload()
		)
)
