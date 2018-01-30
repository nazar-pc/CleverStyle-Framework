<?php
/**
 * @package  Blogs
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Blogs;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\modules\Tags\Tags as Tags_trait,
	cs\Singleton;
/**
 * Tags trait
 *
 * Provides methods for working with tags for faster development
 *
 * @method static $this instance($check = false)
 */
class Tags {
	use
		Tags_trait,
		Singleton;
	/**
	 * @var Prefix
	 */
	protected $cache;
	/**
	 * @var string
	 */
	protected $table = '[prefix]blogs_tags';

	protected function construct () {
		$this->cache = new Prefix('Blogs/tags');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Blogs')->db('posts');
	}
}
