/**
 * @package  Blogs
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is		: 'cs-blogs-posts'
	ready : !->
		@jsonld = JSON.parse(@children[0].innerHTML)
		@posts	= @jsonld['@graph']
)
