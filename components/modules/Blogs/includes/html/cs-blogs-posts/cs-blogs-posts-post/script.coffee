###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-blogs-posts-post'
	'extends'	: 'article'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		post				: {}
		comments_enabled	: false
	ready		: ->
		@$.short_content.innerHTML	= @post.short_content
	sections_path	: (index) ->
		@post.sections_paths[index]
);
