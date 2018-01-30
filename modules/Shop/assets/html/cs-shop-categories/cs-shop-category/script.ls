/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
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
