/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is		: 'cs-blogs-posts'
	ready : !->
		@jsonld = JSON.parse(@children[0].innerHTML)
		@posts	= @jsonld['@graph']
)
