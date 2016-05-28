/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
const GUEST_ID	= 1
Polymer(
	'is'		: 'cs-blogs-head-actions'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		settings		: {
			type	: Object
			value	: {
				admin	: false
			}
		}
		can_write_post	: false
	ready : !->
		Promise.all([
			$.ajax(
				url		: 'api/Blogs'
				type	: 'get_settings'
			)
			$.getJSON('api/System/profile')
		]).then ([@settings, profile]) !~>
			@can_write_post	= profile.id != GUEST_ID && (@settings.admin || !settings.new_posts_only_from_admins)
)
