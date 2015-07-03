<?php
/**
 * @package   Composer assets
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\plugins\Composer_assets;
use
	Exception,
	cs\Page\Includes_processing,
	Less_Parser,
	Leafo\ScssPhp\Compiler as Scss_compiler;

class Assets_processing {
	/**
	 * @var string
	 */
	protected $package_name;
	/**
	 * @var string
	 */
	protected $package_dir;
	/**
	 * @var string
	 */
	protected $target_dir;
	/**
	 * @var string[][]
	 */
	protected $includes_map;
	/**
	 * @param string     $package_name
	 * @param string     $package_dir
	 * @param string     $target_dir
	 * @param string[][] $includes_map
	 */
	function __construct ($package_name, $package_dir, $target_dir, &$includes_map) {
		$this->package_name = $package_name;
		$this->package_dir  = $package_dir;
		$this->target_dir   = $target_dir;
		$this->includes_map = &$includes_map;
	}
	/**
	 * @param string|string[] $files
	 */
	function add ($files) {
		foreach ((array)$files as $file) {
			$file = "$this->package_dir/$file";
			switch (file_extension($file)) {
				case 'js':
					$this->add_content(
						file_get_contents($file),
						'js'
					);
					break;
				case 'css':
					$this->add_content(
						Includes_processing::css(
							file_get_contents($file),
							$file
						),
						'css'
					);
					break;
				case 'html':
					$this->add_content(
						Includes_processing::html(
							file_get_contents($file),
							$file,
							$this->package_name,
							$this->target_dir
						),
						'html'
					);
					break;
				case 'less':
					try {
						$this->add_content(
							Includes_processing::css(
								(new Less_Parser)->parseFile($file)->getCss(),
								$file
							),
							'css'
						);
					} catch (Exception $e) {
					}
					break;
				case 'scss':
					$this->add_content(
						Includes_processing::css(
							(new Scss_compiler)->compile(file_get_contents($file)),
							$file
						),
						'css'
					);
					break;
			}
		}
	}
	/**
	 * @param string $content
	 * @param string $extension
	 */
	protected function add_content ($content, $extension) {
		$target_file = $this->target_dir;
		switch ($extension) {
			case 'css':
				$target_file .= '/style.css';
				break;
			case 'js':
				$target_file .= '/script.js';
				break;
			case 'html':
				$target_file .= '/index.html';
				break;
		}
		file_put_contents($target_file, $content, FILE_APPEND);
		$this->includes_map[$this->package_name][$extension] = [$target_file];
	}
}
