<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Page;
use
	cs\Config,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\Singleton,
	h;

/**
 * Meta class for generation of various meta tags
 */
class Meta {
	use
		Singleton;
	/**
	 * Is used as <head prefix="$head_prefix">
	 * @var string
	 */
	public	$head_prefix	= '';
	/**
	 * If false - &lt;head&gt; will not be added automatically, and should be in template if needed
	 * @var bool
	 */
	public	$no_head		= false;
	protected	$links		= '';
	protected	$og_data	= [];
	protected	$og_type	= '';
	protected	$image_src	= false;
	/**
	 * Common wrapper to add all necessary meta tags with images
	 *
	 * @param string|string[]	$images
	 *
	 * @return Meta
	 */
	function image ($images) {
		if (!$images) {
			return $this;
		}
		$images	= (array)$images;
		if (!$this->image_src) {
			$this->image_src	= true;
			$this->links		.= h::link([
				'href'	=> $images[0],
				'rel'	=> 'image_src'
			]);
		}
		$this->__call('og', ['image', $images]);
		return $this;
	}
	/**
	 * Common wrapper for generation of various Open Graph protocol meta tags
	 *
	 * @param string	$type
	 * @param mixed[]	$params
	 *
	 * @return $this
	 */
	function __call ($type, $params) {
		if (!$params) {
			$this->og_type			= $type;
			$this->og_data['type']	= h::meta([
				'property'	=> "og:type",
				'content'	=> $type
			]);
			return $this;
		}
		if (!$params[0]) {
			return $this;
		}
		if (is_array($params[1])) {
			foreach ($params[1] as $p) {
				$this->__call($type, [$params[0], $p]);
			}
		} elseif ($params[1] || $params[1] === 0) {
			if (!isset($this->og_data[$params[0]])) {
				$this->og_data[$params[0]]	= '';
			}
			$this->og_data[$params[0]]	.= h::meta([
				'property'	=> "$type:$params[0]",
				'content'	=> $params[1]
			]);
		}
		return $this;
	}
	/**
	 * Generates Open Graph protocol information, and puts it into HTML
	 *
	 * Usually called by system itself, there is no need to call it manually
	 */
	function render () {
		/**
		 * Automatic generation of some information
		 */
		$Page		= Page::instance();
		$og			= &$this->og_data;
		if (!isset($og['title']) || empty($og['title'])) {
			$this->og('title', $Page->Title);
		}
		if (
			(
				!isset($og['description']) || empty($og['description'])
			) &&
			$Page->Description
		) {
			$this->og('description', $Page->Description);
		}
		$Config		= Config::instance();
		if (!isset($og['url']) || empty($og['url'])) {
			$this->og('url', home_page() ? $Config->base_url() : ($Page->canonical_url ?: $Config->base_url().'/'.Route::instance()->relative_address));
		}
		if (!isset($og['site_name']) || empty($og['site_name'])) {
			$this->og('site_name', get_core_ml_text('name'));
		}
		if (!isset($og['type']) || empty($og['type'])) {
			$this->og('type', 'website');
		}
		if ($Config->core['multilingual']) {
			$L	= Language::instance();
			if (!isset($og['locale']) || empty($og['locale'])) {
				$this->og('locale', $L->clocale);
			}
			if (
				(
					!isset($og['locale:alternate']) || empty($og['locale:alternate'])
				) && count($Config->core['active_languages']) > 1
			) {
				foreach ($Config->core['active_languages'] as $lang) {
					if ($lang != $L->clanguage) {
						$this->og('locale:alternate', $L->get('clocale', $lang));
					}
				}
			}
		}
		$prefix		= 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#';
		$type		= explode('.', $this->og_type, 2)[0];
		switch ($type) {
			case 'article':
			case 'blog':
			case 'book':
			case 'profile':
			case 'video':
			case 'website':
				$prefix	.= " $type: http://ogp.me/ns/$type#";
			break;
		}
		$Page->Head	=
			$Page->Head.
			implode('', $og).
			$this->links;
		if (!$this->no_head) {
			$Page->Head	= h::head(
				$Page->Head,
				[
					'prefix'	=> $prefix.$this->head_prefix
				]
			);
		}
	}
}
