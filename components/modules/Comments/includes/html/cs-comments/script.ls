/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
const GUEST_ID	= 1
Polymer(
	'is'		: 'cs-comments'
	behaviors	: [
		cs.Polymer.behaviors.Language('comments_')
	]
	properties	:
		module		: String
		item		: Number
		comments	: Array
		_this		: Object
		text		: ''
		is_user		: Boolean
	ready : !->
		@anchor	= location.hash.substr(1)
		@_this	= @
		@reload()
	reload : !->
		Promise.all([
			$.getJSON('api/Comments?module=' + @module + '&item=' + @item)
			$.getJSON('api/System/profile')
			$.ajax(
				url		: 'api/Comments'
				type	: 'is_admin'
			)
		]).then ([comments, profile, is_admin]) !~>
			id_index_map	= {}
			is_user			= profile.id != GUEST_ID
			@is_user		= is_user
			for comment, index in comments
				id_index_map[comment.id] = index
				comment.children	= []
				comment.can_edit	= is_admin || comment.user == profile.id
				comment.can_reply	= is_user
				comment.scroll_to	= @anchor == 'comment_' + comment.id
			normalized_comments = []
			for comment, index in comments
				if !comment.parent
					normalized_comments.push(comment)
				else
					comments[id_index_map[comment.parent]].children.push(comment)
			for comment, index in comments
				comment.can_delete = comment.can_edit && !comment.children.length
			@set('comments', normalized_comments)
			delete @anchor
	_send : !->
		$.ajax(
			url		: 'api/Comments'
			type	: 'post'
			data	:
				module	: @module
				item	: @item
				text	: @text
				parent	: 0
			success	: !~>
				cs.ui.notify(@L.comment_posted, 'success', 5)
				@reload()
				@text	= ''
		)
)
