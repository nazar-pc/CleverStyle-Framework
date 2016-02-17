###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-blogs-head-actions'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		admin			: false
		can_write_post	: false
);
