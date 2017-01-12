<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;
use
	cs\App,
	cs\Config,
	cs\Language,
	cs\Request,
	cs\Response,
	h,
	cs\Page\Assets\Cache,
	cs\Page\Assets\Collecting,
	cs\Page\Assets\RequireJS;

/**
 * Assets management for `cs\Page` class
 *
 * @property string $Title
 * @property string $Description
 * @property string $canonical_url
 * @property string $Head
 * @property string $post_Body
 * @property string $theme
 */
trait Assets {
	protected $extension_to_as = [
		'jpeg' => 'image',
		'jpe'  => 'image',
		'jpg'  => 'image',
		'gif'  => 'image',
		'png'  => 'image',
		'svg'  => 'image',
		'svgz' => 'image',
		'woff' => 'font',
		//'woff2' => 'font',
		'css'  => 'style',
		'js'   => 'script',
		'html' => 'document'
	];
	/**
	 * @var array
	 */
	protected $core_html;
	/**
	 * @var array
	 */
	protected $core_js;
	/**
	 * @var array
	 */
	protected $core_css;
	/**
	 * @var string
	 */
	protected $core_config;
	/**
	 * @var array
	 */
	protected $html;
	/**
	 * @var array
	 */
	protected $js;
	/**
	 * @var array
	 */
	protected $css;
	/**
	 * @var string
	 */
	protected $config;
	protected function init_assets () {
		$this->core_html   = [];
		$this->core_js     = [];
		$this->core_css    = [];
		$this->core_config = '';
		$this->html        = [];
		$this->js          = [];
		$this->css         = [];
		$this->config      = '';
	}
	/**
	 * @param string|string[] $add
	 *
	 * @return \cs\Page
	 */
	protected function core_html ($add) {
		return $this->include_common('html', $add, true);
	}
	/**
	 * @param string|string[] $add
	 *
	 * @return \cs\Page
	 */
	protected function core_js ($add) {
		return $this->include_common('js', $add, true);
	}
	/**
	 * @param string|string[] $add
	 *
	 * @return \cs\Page
	 */
	protected function core_css ($add) {
		return $this->include_common('css', $add, true);
	}
	/**
	 * Including of Web Components
	 *
	 * @param string|string[] $add Path to including file, or code
	 *
	 * @return \cs\Page
	 */
	public function html ($add) {
		return $this->include_common('html', $add, false);
	}
	/**
	 * Including of JavaScript
	 *
	 * @param string|string[] $add Path to including file, or code
	 *
	 * @return \cs\Page
	 */
	public function js ($add) {
		return $this->include_common('js', $add, false);
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[] $add Path to including file, or code
	 *
	 * @return \cs\Page
	 */
	public function css ($add) {
		return $this->include_common('css', $add, false);
	}
	/**
	 * @param string          $what
	 * @param string|string[] $add
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function include_common ($what, $add, $core) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $a) {
				$this->include_common($what, $a, $core);
			}
		} else {
			if ($core) {
				$what = "core_$what";
			}
			$target   = &$this->$what;
			$target[] = $add;
		}
		return $this;
	}
	/**
	 * Add config on page to make it available on frontend
	 *
	 * @param mixed  $config_structure        Any scalar type or array
	 * @param string $target                  Target is property of `window` object where config will be inserted as value, nested properties like `cs.sub.prop`
	 *                                        are supported and all nested properties are created on demand. It is recommended to use sub-properties of `cs`
	 *
	 * @return \cs\Page
	 */
	public function config ($config_structure, $target) {
		return $this->config_internal($config_structure, $target);
	}
	/**
	 * @param mixed  $config_structure
	 * @param string $target
	 * @param bool   $core
	 *
	 * @return \cs\Page
	 */
	protected function config_internal ($config_structure, $target, $core = false) {
		$config = h::script(
			json_encode($config_structure, JSON_UNESCAPED_UNICODE),
			[
				'target' => $target,
				'class'  => 'cs-config',
				'type'   => 'application/json'
			]
		);
		if ($core) {
			$this->core_config .= $config;
		} else {
			$this->config .= $config;
		}
		return $this;
	}
	/**
	 * Getting of HTML, JS and CSS assets
	 *
	 * @return \cs\Page
	 */
	protected function add_assets_on_page () {
		$Config = Config::instance(true);
		if (!$Config) {
			return $this;
		}
		// TODO: I hope some day we'll get rid of this sh*t :(
		$this->edge();
		$Request = Request::instance();
		/**
		 * If CSS and JavaScript compression enabled
		 */
		$L = Language::instance();
		if ($this->page_compression_usage($Config, $Request)) {
			/**
			 * Rebuilding HTML, JS and CSS cache if necessary
			 */
			(new Cache)->rebuild($Config, $L, $this->theme);
			$this->webcomponents_polyfill($Request, $Config, true);
			$languages_hash = md5(implode('', $Config->core['active_languages']));
			$language_hash  = file_get_json(PUBLIC_CACHE."/languages-$languages_hash.json")[$L->clanguage];
			$this->config_internal(
				[
					'language' => $L->clanguage,
					'hash'     => $language_hash
				],
				'cs.current_language',
				true
			);
			list($assets, $preload) = $this->get_assets_and_preload_resource_for_page_with_compression($Request);
		} else {
			$this->webcomponents_polyfill($Request, $Config, false);
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			$this->config_internal($L, 'cs.Language', true);
			$this->config_internal(RequireJS::get_config(), 'requirejs', true);
			$assets  = $this->get_assets_for_page_without_compression($Config, $Request);
			$preload = [];
		}
		$this->core_css($assets['css']);
		$this->core_js($assets['js']);
		if (isset($assets['html'])) {
			$this->core_html($assets['html']);
		}
		$this->add_assets_on_page_manually_added($Config, $Request, $preload);
		return $this;
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return bool
	 */
	protected function page_compression_usage ($Config, $Request) {
		return $Config->core['cache_compress_js_css'] && !($Request->admin_path && isset($Request->query['debug']));
	}
	/**
	 * Add JS polyfills for IE/Edge
	 */
	protected function edge () {
		if (strpos(Request::instance()->header('user-agent'), 'Edge') === false) {
			return;
		}
		$this->core_js(
			get_files_list(DIR.'/assets/js/microsoft_shit', '/.*\.js$/i', 'f', 'assets/js/microsoft_shit', true)
		);
	}
	/**
	 * Hack: Add WebComponents Polyfill for browsers without native Shadow DOM support
	 *
	 * @param Request $Request
	 * @param Config  $Config
	 * @param bool    $with_compression
	 */
	protected function webcomponents_polyfill ($Request, $Config, $with_compression) {
		if (($this->theme != Config::SYSTEM_THEME && $Config->core['disable_webcomponents']) || $Request->cookie('shadow_dom') == 1) {
			return;
		}
		if ($with_compression) {
			$hash = file_get_contents(PUBLIC_CACHE.'/webcomponents.js.hash');
			$this->add_script_imports_to_document($Config, "<script src=\"/storage/public_cache/$hash.js\"></script>\n");
		} else {
			$this->add_script_imports_to_document($Config, "<script src=\"/assets/js/WebComponents-polyfill/webcomponents-custom.min.js\"></script>\n");
		}
	}
	/**
	 * @param Config $Config
	 * @param string $content
	 */
	protected function add_script_imports_to_document ($Config, $content) {
		if ($Config->core['put_js_after_body']) {
			$this->post_Body .= $content;
		} else {
			$this->Head .= $content;
		}
	}
	/**
	 * @param Request $Request
	 *
	 * @return array[]
	 */
	protected function get_assets_and_preload_resource_for_page_with_compression ($Request) {
		list($dependencies, $compressed_assets_map, $not_embedded_resources_map) = file_get_json(PUBLIC_CACHE."/$this->theme.json");
		$assets  = $this->get_normalized_assets($dependencies, $compressed_assets_map, $Request);
		$preload = [];
		foreach (array_merge(...array_values($assets)) as $path) {
			$preload[] = [$path];
			if (isset($not_embedded_resources_map[$path])) {
				$preload[] = $not_embedded_resources_map[$path];
			}
		}
		return [$assets, array_merge(...$preload)];
	}
	/**
	 * @param array      $dependencies
	 * @param string[][] $assets_map
	 * @param Request    $Request
	 *
	 * @return string[][]
	 */
	protected function get_normalized_assets ($dependencies, $assets_map, $Request) {
		$current_module = $Request->current_module;
		/**
		 * Current URL based on controller path (it better represents how page was rendered)
		 */
		$current_url = array_slice(App::instance()->controller_path, 1);
		$current_url = ($Request->admin_path ? 'admin/' : '')."$current_module/".implode('/', $current_url);
		/**
		 * Narrow the dependencies to current module only
		 */
		$dependencies  = array_unique(
			array_merge(
				['System'],
				$dependencies['System'],
				isset($dependencies[$current_module]) ? $dependencies[$current_module] : []
			)
		);
		$system_assets = [];
		// Array with empty array in order to avoid `array_merge()` failure later
		$dependencies_assets = array_fill_keys($dependencies, [[]]);
		$assets              = [];
		foreach ($assets_map as $path => $local_assets) {
			if ($path == 'System') {
				$system_assets = $local_assets;
			} elseif ($component = $this->get_dependency_component($dependencies, $path, $Request)) {
				/**
				 * @var string $component
				 */
				$dependencies_assets[$component][] = $local_assets;
			} elseif (mb_strpos($current_url, $path) === 0) {
				$assets[] = $local_assets;
			}
		}
		// Convert to indexed array first
		$dependencies_assets = array_values($dependencies_assets);
		// Flatten array on higher level
		$dependencies_assets = array_merge(...$dependencies_assets);
		// Hack: 2 array_merge_recursive() just to be compatible with HHVM, simplify when https://github.com/facebook/hhvm/issues/7087 is resolved
		return _array(array_merge_recursive(array_merge_recursive($system_assets, ...$dependencies_assets), ...$assets));
	}
	/**
	 * @param array   $dependencies
	 * @param string  $url
	 * @param Request $Request
	 *
	 * @return false|string
	 */
	protected function get_dependency_component ($dependencies, $url, $Request) {
		$url_exploded = explode('/', $url);
		/** @noinspection NestedTernaryOperatorInspection */
		$url_component = $url_exploded[0] != 'admin' ? $url_exploded[0] : (@$url_exploded[1] ?: '');
		$is_dependency =
			$url_component !== Config::SYSTEM_MODULE &&
			in_array($url_component, $dependencies) &&
			(
				$Request->admin_path || $Request->admin_path == ($url_exploded[0] == 'admin')
			);
		return $is_dependency ? $url_component : false;
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return string[][]
	 */
	protected function get_assets_for_page_without_compression ($Config, $Request) {
		// To determine all dependencies and stuff we need `$Config` object to be already created
		list($dependencies, $assets_map) = Collecting::get_assets_dependencies_and_map($Config, $this->theme);
		$assets = $this->get_normalized_assets($dependencies, $assets_map, $Request);
		return $this->add_versions_hash(_substr($assets, strlen(DIR)));
	}
	/**
	 * @param string[][] $assets
	 *
	 * @return string[][]
	 */
	protected function add_versions_hash ($assets) {
		$content      = array_reduce(
			get_files_list(DIR.'/components', '/^meta\.json$/', 'f', true, true),
			function ($content, $file) {
				return $content.file_get_contents($file);
			}
		);
		$content_hash = substr(md5($content), 0, 5);
		foreach ($assets as &$files) {
			foreach ($files as &$file) {
				$file .= "?$content_hash";
			}
			unset($file);
		}
		return $assets;
	}
	/**
	 * @param Config   $Config
	 * @param Request  $Request
	 * @param string[] $preload
	 */
	protected function add_assets_on_page_manually_added ($Config, $Request, $preload) {
		/** @noinspection NestedTernaryOperatorInspection */
		$this->Head .= array_reduce(
			array_merge($this->core_css, $this->css),
			function ($content, $href) {
				return "$content<link href=\"$href\" rel=\"stylesheet\">\n";
			}
		);
		if ($this->page_compression_usage($Config, $Request) && $Config->core['frontend_load_optimization']) {
			$this->add_assets_on_page_manually_added_frontend_load_optimization($Config, $Request);
		} else {
			$this->add_assets_on_page_manually_added_normal($Config, $Request, $preload);
		}
	}
	/**
	 * @param Config   $Config
	 * @param Request  $Request
	 * @param string[] $preload
	 */
	protected function add_assets_on_page_manually_added_normal ($Config, $Request, $preload) {
		$this->add_preload($preload, $Request);
		$configs      = $this->core_config.$this->config;
		$scripts      = array_reduce(
			array_merge($this->core_js, $this->js),
			function ($content, $src) {
				return "$content<script src=\"$src\"></script>\n";
			}
		);
		$html_imports = array_reduce(
			array_merge($this->core_html, $this->html),
			function ($content, $href) {
				return "$content<link href=\"$href\" rel=\"import\">\n";
			}
		);
		$this->Head .= $configs;
		$this->add_script_imports_to_document($Config, $scripts.$html_imports);
	}
	/**
	 * @param string[] $preload
	 * @param Request  $Request
	 */
	protected function add_preload ($preload, $Request) {
		if ($Request->cookie('pushed')) {
			return;
		}
		$Response = Response::instance();
		$Response->cookie('pushed', 1, 0, true);
		foreach (array_unique($preload) as $resource) {
			$extension = explode('?', file_extension($resource))[0];
			$as        = $this->extension_to_as[$extension];
			$resource  = str_replace(' ', '%20', $resource);
			$Response->header('Link', "<$resource>; rel=preload; as=$as", false);
		}
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 */
	protected function add_assets_on_page_manually_added_frontend_load_optimization ($Config, $Request) {
		list($optimized_assets, $preload) = file_get_json(PUBLIC_CACHE."/$this->theme.optimized.json");
		$this->add_preload(
			array_merge($preload, $this->core_css, $this->css),
			$Request
		);
		$optimized_assets  = array_flip($optimized_assets);
		$system_scripts    = '';
		$optimized_scripts = [];
		$system_imports    = '';
		$optimized_imports = [];
		foreach (array_merge($this->core_js, $this->js) as $script) {
			if (isset($optimized_assets[$script])) {
				$optimized_scripts[] = $script;
			} else {
				$system_scripts .= "<script src=\"$script\"></script>\n";
			}
		}
		foreach (array_merge($this->core_html, $this->html) as $import) {
			if (isset($optimized_assets[$import])) {
				$optimized_imports[] = $import;
			} else {
				$system_imports .= "<link href=\"$import\" rel=\"import\">\n";
			}
		}
		$this->config([$optimized_scripts, $optimized_imports], 'cs.optimized_assets');
		$this->Head .= $this->core_config.$this->config;
		$this->add_script_imports_to_document($Config, $system_scripts.$system_imports);
	}
}
