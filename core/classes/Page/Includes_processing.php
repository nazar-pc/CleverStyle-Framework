<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;

/**
 * Class includes few methods used for processing CSS and HTML files before putting into cache.
 *
 * This is because CSS and HTML files may contain other includes of other CSS, JS files, images, fonts and so on with absolute and relative paths.
 * Methods of this class handles all this includes and put them into single resulting file compressed with gzip.
 * This allows to decrease number of HTTP requests on page and avoid breaking of relative paths for fonts, images and other includes
 * after putting them into cache directory.
 */
class Includes_processing {
	/**
	 * Do not inline files bigger than 4 KiB
	 */
	const MAX_EMBEDDING_SIZE = 4096;
	protected static $extension_to_mime = [
		'jpeg' => 'image/jpg',
		'jpe'  => 'image/jpg',
		'jpg'  => 'image/jpg',
		'gif'  => 'image/gif',
		'png'  => 'image/png',
		'svg'  => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		'woff' => 'application/font-woff',
		//'woff2' => 'application/font-woff2',
		'css'  => 'text/css'
	];
	/**
	 * Analyses file for images, fonts and css links and include they content into single resulting css file.
	 *
	 * Supports next file extensions for possible includes:
	 * jpeg, jpe, jpg, gif, png, ttf, ttc, svg, svgz, woff, eot, css
	 *
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that includes specified in previous parameter content
	 * @param string[] $not_embedded_resources Some resources like images and fonts might not be embedded into resulting CSS because of their size
	 *
	 * @return string    $data
	 */
	static function css ($data, $file, &$not_embedded_resources = []) {
		$dir = dirname($file);
		/**
		 * Remove comments, tabs and new lines
		 */
		$data = preg_replace('#(/\*.*?\*/)|\t|\n|\r#s', ' ', $data);
		/**
		 * Remove unnecessary spaces
		 */
		$data = preg_replace('/\s*([,;>{}(])\s*/', '$1', $data);
		$data = preg_replace('/\s+/', ' ', $data);
		/**
		 * Return spaces required in media queries
		 */
		$data = preg_replace('/\s(and|or)\(/', ' $1 (', $data);
		/**
		 * Duplicated semicolons
		 */
		$data = preg_replace('/;+/m', ';', $data);
		/**
		 * Minify repeated colors declarations
		 */
		$data = preg_replace('/#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/i', '#$1$2$3', $data);
		/**
		 * Minify rgb colors declarations
		 */
		$data = preg_replace_callback(
			'/rgb\(([0-9,.]+)\)/i',
			function ($rgb) {
				$rgb = explode(',', $rgb[1]);
				return
					'#'.
					str_pad(dechex($rgb[0]), 2, 0, STR_PAD_LEFT).
					str_pad(dechex($rgb[1]), 2, 0, STR_PAD_LEFT).
					str_pad(dechex($rgb[2]), 2, 0, STR_PAD_LEFT);
			},
			$data
		);
		/**
		 * Remove unnecessary zeros
		 */
		$data = preg_replace('/(\D)0\.(\d+)/i', '$1.$2', $data);
		/**
		 * Includes processing
		 */
		$data = preg_replace_callback(
			'/url\((.*?)\)|@import[\s\t\n\r]*[\'"](.*?)[\'"]/',
			function ($match) use ($dir, &$not_embedded_resources) {
				$link = trim($match[1], '\'" ');
				$link = explode('?', $link, 2)[0];
				if (!static::is_relative_path_and_exists($link, $dir)) {
					return $match[0];
				}
				$content   = file_get_contents("$dir/$link");
				$extension = file_extension($link);
				if (!isset(static::$extension_to_mime[$extension]) || filesize("$dir/$link") > static::MAX_EMBEDDING_SIZE) {
					$path_relatively_to_the_root = str_replace(getcwd(), '', realpath("$dir/$link"));
					$path_relatively_to_the_root .= '?'.substr(md5($content), 0, 5);
					if (strpos($match[1], '?') === false) {
						$not_embedded_resources[] = $path_relatively_to_the_root;
					}
					return str_replace($match[1], "'".str_replace("'", "\\'", $path_relatively_to_the_root)."'", $match[0]);
				}
				if ($extension == 'css') {
					/**
					 * For recursive includes processing, if CSS file includes others CSS files
					 */
					$content = static::css($content, $link, $not_embedded_resources);
				}
				$mime_type = static::$extension_to_mime[$extension];
				$content   = base64_encode($content);
				return str_replace($match[1], "data:$mime_type;charset=utf-8;base64,$content", $match[0]);
			},
			$data
		);
		return trim($data);
	}
	/**
	 * Simple and fast JS minification
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	static function js ($data) {
		/**
		 * Split into array of lines
		 */
		$data = explode("\n", $data);
		/**
		 * Flag that is `true` when inside comment
		 */
		$comment = false;
		/**
		 * Set of symbols that are safe to be concatenated without new line with anything else
		 */
		$regexp = /** @lang PhpRegExp */
			'[:;,.+\-*/{}?><^\'"\[\]=&(]';
		foreach ($data as $index => &$d) {
			$next_line = isset($data[$index + 1]) ? trim($data[$index + 1]) : '';
			/**
			 * Remove starting and trailing spaces
			 */
			$d = trim($d);
			/**
			 * Remove single-line comments
			 */
			if (mb_strpos($d, '//') === 0) {
				$d = '';
				continue;
			}
			/**
			 * Starts with multi-line comment
			 */
			if (mb_strpos($d, '/*') === 0) {
				$comment = true;
			}
			/**
			 * Add new line at the end if only needed
			 */
			if (
				$d &&
				$next_line &&
				!$comment &&
				!preg_match("#$regexp\$#", $d) &&
				!preg_match("#^$regexp#", $next_line)
			) {
				$d .= "\n";
			}
			if ($comment) {
				/**
				 * End of multi-line comment
				 */
				if (strpos($d, '*/') !== false) {
					$d       = explode('*/', $d)[1];
					$comment = false;
				} else {
					$d = '';
				}
			} else {
				/**
				 * Single-line comment
				 */
				$d = preg_replace('#^\s*//[^\'"]+$#', '', $d);
				/**
				 * If we are not sure - just add new like afterwards
				 */
				$d = preg_replace('#//.*$#', "\\0\n", $d);
			}
		}
		$data = implode('', $data);
		$data = str_replace('</script>', '<\/script>', $data);
		return trim($data, ';').';';
	}
	/**
	 * Analyses file for scripts and styles, combines them into resulting files in order to optimize loading process
	 * (files with combined scripts and styles will be created)
	 *
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that includes specified in previous parameter content
	 * @param string   $base_target_file_path  Base filename for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make includes built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	static function html ($data, $file, $base_target_file_path, $vulcanization, &$not_embedded_resources = []) {
		static::html_process_scripts($data, $file, $base_target_file_path, $vulcanization, $not_embedded_resources);
		static::html_process_links_and_styles($data, $file, $base_target_file_path, $vulcanization, $not_embedded_resources);
		// Removing HTML comments (those that are mostly likely comments, to avoid problems)
		$data = preg_replace_callback(
			'/^\s*<!--([^>-].*[^-])?-->/Ums',
			function ($matches) {
				return mb_strpos('--', $matches[1]) === false ? '' : $matches[0];
			},
			$data
		);
		return preg_replace("/\n+/", "\n", $data);
	}
	/**
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that includes specified in previous parameter content
	 * @param string   $base_target_file_path  Base filename for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make includes built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	protected static function html_process_scripts (&$data, $file, $base_target_file_path, $vulcanization, &$not_embedded_resources) {
		if (!preg_match_all('/<script(.*)<\/script>/Uims', $data, $scripts)) {
			return;
		}
		$scripts_content    = '';
		$scripts_to_replace = [];
		$dir                = dirname($file);
		foreach ($scripts[1] as $index => $script) {
			$script = explode('>', $script, 2);
			if (preg_match('/src\s*=\s*[\'"](.*)[\'"]/Uims', $script[0], $url)) {
				$url = $url[1];
				if (!static::is_relative_path_and_exists($url, $dir)) {
					continue;
				}
				$scripts_to_replace[] = $scripts[0][$index];
				$scripts_content .= file_get_contents("$dir/$url").";\n";
			} else {
				$scripts_to_replace[] = $scripts[0][$index];
				$scripts_content .= "$script[1];\n";
			}
		}
		$scripts_content = static::js($scripts_content);
		if (!$scripts_to_replace) {
			return;
		}
		// Remove all scripts
		$data = str_replace($scripts_to_replace, '', $data);
		/**
		 * If vulcanization is not used - put contents into separate file, and put link to it, otherwise put minified content back
		 */
		if (!$vulcanization) {
			/**
			 * md5 to distinguish modifications of the files
			 */
			$content_md5 = substr(md5($scripts_content), 0, 5);
			file_put_contents(
				"$base_target_file_path.js",
				gzencode($scripts_content, 9),
				LOCK_EX | FILE_BINARY
			);
			$base_target_file_name = basename($base_target_file_path);
			// Add script with combined content file to the end
			$data .= "<script src=\"$base_target_file_name.js?$content_md5\"></script>";
			$not_embedded_resources[] = "$base_target_file_name.js?$content_md5";
		} else {
			// Add combined content inline script to the end
			$data .= "<script>$scripts_content</script>";
		}
	}
	/**
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that includes specified in previous parameter content
	 * @param string   $base_target_file_path  Base filename for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make includes built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	protected static function html_process_links_and_styles (&$data, $file, $base_target_file_path, $vulcanization, &$not_embedded_resources) {
		// Drop Polymer inclusion, since it is already present
		$data = str_replace('<link rel="import" href="../polymer/polymer.html">', '', $data);
		if (!preg_match_all('/<link(.*)>|<style(.*)<\/style>/Uims', $data, $links_and_styles)) {
			return;
		}
		$imports_content             = '';
		$links_and_styles_to_replace = [];
		$dir                         = dirname($file);
		foreach ($links_and_styles[1] as $index => $link) {
			/**
			 * Check for custom styles `is="custom-style"` or styles includes `include=".."` - we'll skip them
			 * Or if content is plain CSS
			 */
			if (
				preg_match('/^[^>]*(is="custom-style"|include=)[^>]*>/Uim', $links_and_styles[2][$index]) ||
				mb_strpos($links_and_styles[0][$index], '</style>') > 0
			) {
				$content = explode('>', $links_and_styles[2][$index], 2)[1];
				$data    = str_replace(
					$content,
					static::css($content, $file, $not_embedded_resources),
					$data
				);
				continue;
			}
			if (!static::has_relative_href($link, $url, $dir)) {
				continue;
			}
			$import = preg_match('/rel\s*=\s*[\'"]import[\'"]/Uim', $link);
			/**
			 * CSS imports are available in Polymer alongside with HTML imports
			 */
			$css_import = $import && preg_match('/type\s*=\s*[\'"]css[\'"]/Uim', $link);
			$stylesheet = preg_match('/rel\s*=\s*[\'"]stylesheet[\'"]/Uim', $link);
			// TODO: Polymer only supports `style[is=custom-style]`, but no `link`-based counterpart, so we can't provide CSP-compatibility for CSS anyway
			if ($css_import || $stylesheet) {
				/**
				 * If content is link to CSS file
				 */
				$css  = static::css(
					file_get_contents("$dir/$url"),
					"$dir/$url",
					$not_embedded_resources
				);
				$data = preg_replace(
					'/'.$links_and_styles[0][$index].'.*<template>/Uims',
					"<template><style>$css</style>",
					$data
				);
			} elseif ($import) {
				/**
				 * If content is HTML import
				 */
				$links_and_styles_to_replace[] = $links_and_styles[0][$index];
				$imports_content .= static::html(
					file_get_contents("$dir/$url"),
					"$dir/$url",
					"$base_target_file_path-".basename($url, '.html'),
					$vulcanization,
					$not_embedded_resources
				);
			}
		}
		if (!$links_and_styles_to_replace) {
			return;
		}
		// Add imports to the end
		$data .= $imports_content;
	}
	/**
	 * @param string $link
	 * @param string $url
	 * @param string $dir
	 *
	 * @return bool
	 */
	protected static function has_relative_href ($link, &$url, $dir) {
		$result =
			$link &&
			preg_match('/href\s*=\s*[\'"](.*)[\'"]/Uims', $link, $url);
		if ($result && static::is_relative_path_and_exists($url[1], $dir)) {
			$url = $url[1];
			return true;
		}
		return false;
	}
	/**
	 * Simple check for http[s], ftp and absolute links
	 *
	 * @param string $path
	 * @param string $dir
	 *
	 * @return bool
	 */
	protected static function is_relative_path_and_exists ($path, $dir) {
		return !preg_match('#^(http://|https://|ftp://|/)#i', $path) && file_exists("$dir/$path");
	}
}
