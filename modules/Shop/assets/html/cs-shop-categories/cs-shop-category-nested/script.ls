/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-category-nested'
	extends		: 'article'
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
