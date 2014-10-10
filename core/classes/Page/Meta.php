<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Page;
use
	cs\Config,
	cs\Language,
	cs\Page,
	cs\Singleton,
	h;

/**
 * Open Graph functionality for <i>cs\Page</i> class
 *
 * @property string $Title
 * @property string $Description
 * @property string $canonical_url
 * @property string $Head
 */
class Meta {
	use
		Singleton;
	public	$og_data	= [];
	public	$og_type	= '';
	/**
	 * Is used as <head prefix="$head_prefix">
	 * @var string
	 */
	public	$head_prefix	= '';
	/**
	 * If false - &lt;head&gt; will not be added automatically, and should be in template if needed
	 * @var bool
	 */
	public	$no_head	= false;
	/**
	 * Open Graph protocol support
	 *
	 * Provides automatic addition of &lt;html prefix="og: http://ogp.me/ns#"&gt;, and is used for simplification of Open Graph protocol support
	 *
	 * @param string			$property		Property name, but without <i>og:</i> prefix. For example, <i>title</i>
	 * @param string|string[]	$content		Content, may be an array
	 * @param string			$custom_prefix	If prefix should differ from <i>og:</i>, for example, <i>article:</i> - specify it here
	 *
	 * @return \cs\Page
	 */
	function og ($property, $content, $custom_prefix = 'og:') {
		if (
			!$property ||
			(
				!$content &&
				$content !== 0
			)
		) {
			return $this;
		}
		if (!Config::instance()->core['og_support']) {
			return $this;
		}
		if (is_array($content)) {
			foreach ($content as $c) {
				$this->og($property, $c, $custom_prefix);
			}
			return $this;
		}
		if (!isset($this->og_data[$property])) {
			$this->og_data[$property]	= '';
		}
		if ($property == 'type') {
			$this->og_type	= $content;
		}
		$this->og_data[$property]	.= h::meta([
			'property'	=> $custom_prefix.$property,
			'content'	=> $content
		]);
		return $this;
	}
	/**
	 * Generates Open Graph protocol information, and puts it into HTML
	 */
	function render () {
		/**
		 * Automatic generation of some information
		 */
		$og			= &$this->og_data;
		if (!isset($og['title']) || empty($og['title'])) {
			$this->og('title', $this->Title);
		}
		if (
			(
				!isset($og['description']) || empty($og['description'])
			) &&
			$this->Description
		) {
			$this->og('description', $this->Description);
		}
		$Config		= Config::instance();
		if (!isset($og['url']) || empty($og['url'])) {
			$this->og('url', home_page() ? $Config->base_url() : ($this->canonical_url ?: $Config->base_url().'/'.$Config->server['relative_address']));
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
		switch (explode('.', $this->og_type, 2)[0]) {
			case 'article':
				$prefix	.= ' article: http://ogp.me/ns/article#';
			break;
			case 'blog':
				$prefix	.= ' blog: http://ogp.me/ns/blog#';
			break;
			case 'book':
				$prefix	.= ' book: http://ogp.me/ns/book#';
			break;
			case 'profile':
				$prefix	.= ' profile: http://ogp.me/ns/profile#';
			break;
			case 'video':
				$prefix	.= ' video: http://ogp.me/ns/video#';
			break;
			case 'website':
				$prefix	.= ' website: http://ogp.me/ns/website#';
			break;
		}
		$Page		= Page::instance();
		$Page->Head	= $Page->Head.implode('', $og);
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
