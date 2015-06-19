###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L = cs.Language
Polymer(
	publish	:
		can_edit			: false
		can_delete			: false
		comments_enabled	: false
	L		: L
	created	: ->
		@jsonld = JSON.parse(@querySelector('script').innerHTML)
	ready	: ->
		@$.content.innerHTML	= @jsonld.content
		$(@shadowRoot).cs().tooltips_inside()
);
