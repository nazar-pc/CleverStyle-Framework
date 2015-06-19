###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L = cs.Language
Polymer(
	publish		:
		post				: {}
		comments_enabled	: false
	ready		: ->
		@post.comments_enabled		= @comments_enabled
		@post.read_more_text		= cs.Language.read_more
		@$.short_content.innerHTML	= @post.short_content
);
