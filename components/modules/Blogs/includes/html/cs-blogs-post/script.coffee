###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
do (L = cs.Language) ->
	Polymer(
		publish		:
			can_edit			: false
			can_delete			: false
			comments_enabled	: false
		edit_text	: L.edit
		delete_text	: L.delete
		created		: ->
			@jsonld = JSON.parse(@querySelector('script').innerHTML)
		ready		: ->
			@$.content.innerHTML	= @jsonld.content
			@$.title.innerHTML		= @jsonld.title
	);
