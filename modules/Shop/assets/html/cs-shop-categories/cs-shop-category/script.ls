/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-category'
	extends		: 'article'
	properties	:
		href	: String
	ready : !->
		img				= @querySelector('#img')
		@$.img.src		= img.src
		@$.img.title	= img.title
		@set('href', @querySelector('#link').href)
);
