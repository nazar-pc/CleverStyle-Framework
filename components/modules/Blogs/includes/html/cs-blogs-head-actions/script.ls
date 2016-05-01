/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-blogs-head-actions'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		settings		: Object
		can_write_post	: false
	ready : !->
		$.ajax(
			url		: 'api/Blogs'
			type	: 'get_settings'
			success	: (@settings) !~>
				@can_write_post	= cs.is_user && (@settings.admin || !settings.new_posts_only_from_admins)
		)
)
