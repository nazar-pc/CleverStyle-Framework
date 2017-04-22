/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-category'
	properties	:
		href	: String
	ready : !->
		img											= @querySelector('#img')
		@$.img.src									= img.src
		@$.img.title								= img.title
		@shadowRoot.querySelector('h1').innerHTML	= @querySelector('h1').innerHTML
		@$.description.innerHTML					= @querySelector('#description')?.innerHTML || ''
		@$.nested.innerHTML							= @querySelector('#nested')?.innerHTML || ''
		@set('href', @querySelector('#link').href)
);
