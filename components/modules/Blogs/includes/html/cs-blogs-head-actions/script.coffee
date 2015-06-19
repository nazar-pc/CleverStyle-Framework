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
		admin			: false
		can_write_post	: false
	L			: L
	ready		: ->
		$(@shadowRoot).cs().tooltips_inside()
);
