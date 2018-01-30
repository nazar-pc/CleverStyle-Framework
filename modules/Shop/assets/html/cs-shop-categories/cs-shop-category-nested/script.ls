/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-shop-category-nested'
	properties	:
		href			: String
		category_title	: String
	ready : !->
		img				= @querySelector('#img')
		@$.img.src		= img.src
		@$.img.title	= img.title
		link	= @querySelector('#link')
		@set('href', link.href)
		@set('category_title', link.textContent)
)
