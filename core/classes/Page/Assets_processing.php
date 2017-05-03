<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;

/**
 * Class includes few methods used for processing CSS, JS and HTML files before putting into cache
 *
 * This is because CSS and HTML files may include other CSS, JS files, images, fonts and so on with absolute and relative paths.
 * Methods of this class handle all this assets, applies basic minification to CSS and JS files and produce single resulting file, nested files are also copied
 * to target directory and processed if needed.
 */
class Assets_processing {
	protected static $extension_to_mime = [
		'jpeg'  => 'image/jpg',
		'jpe'   => 'image/jpg',
		'jpg'   => 'image/jpg',
		'gif'   => 'image/gif',
		'png'   => 'image/png',
		'svg'   => 'image/svg+xml',
		'svgz'  => 'image/svg+xml',
		'woff2' => 'application/font-woff2'
	];
	/**
	 * Analyses file for images, fonts and css links and include they content into single resulting css file.
	 *
	 * Supports next file extensions for possible assets:
	 * jpeg, jpe, jpg, gif, png, ttf, ttc, svg, svgz, woff, css
	 *
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that contains specified in previous parameter content
	 * @param string   $target_directory_path  Target directory for resulting combined files
	 * @param string[] $not_embedded_resources Some resources like images and fonts might not be embedded into resulting CSS because of their size
	 *
	 * @return string    $data
	 */
	public static function css ($data, $file, $target_directory_path = PUBLIC_CACHE, &$not_embedded_resources = []) {
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
		 * Minify repeated colors declarations
		 */
		$data = preg_replace('/#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/i', '#$1$2$3', $data);
		/**
		 * Remove unnecessary zeros
		 */
		$data = preg_replace('/(\D)0\.(\d+)/i', '$1.$2', $data);
		/**
		 * Unnecessary spaces around colons (should have whitespace character after, otherwise `.class :disabled` will be handled incorrectly)
		 */
		$data = preg_replace('/\s*:\s+/', ':', $data);
		/**
		 * Assets processing
		 */
		// TODO: replace by loop, track duplicated stuff that are subject to inlining and if they appear more than once, don't inline them
		$data = preg_replace_callback(
			'/url\((.*)\)|@import\s*(?:url\()?\s*([\'"].*[\'"])\s*\)??(.*);/U',
			function ($match) use ($dir, $target_directory_path, &$not_embedded_resources) {
				$path_matched = $match[2] ?? $match[1];
				$path         = trim($path_matched, '\'" ');
				$link         = explode('?', $path, 2)[0];
				if (!static::is_relative_path_and_exists($link, $dir)) {
					return $match[0];
				}
				$extension     = file_extension($link);
				$absolute_path = static::absolute_path($link, $dir);
				$content       = file_get_contents($absolute_path);
				if ($extension == 'css' && @$match[2]) {
					/**
					 * Only inline CSS imports without media queries, imports with media queries will be placed as separate files
					 */
					if (!trim(@$match[3])) {
						return static::css($content, $absolute_path, $target_directory_path, $not_embedded_resources);
					}
					$filename = static::file_put_contents_with_hash(
						$target_directory_path,
						$extension,
						static::css($content, $absolute_path, $target_directory_path)
					);
					return str_replace($path_matched, "'./$filename'", $match[0]);
				}
				if (!isset(static::$extension_to_mime[$extension])) {
					$filename = static::file_put_contents_with_hash($target_directory_path, $extension, $content);
					return str_replace($path_matched, "'./$filename'", $match[0]);
				}
				$filename = md5_file($absolute_path).'.'.$extension;
				copy($absolute_path, "$target_directory_path/$filename");
				if (strpos($path, '?') === false) {
					$not_embedded_resources[] = str_replace(getcwd(), '', "$target_directory_path/$filename");
				}
				return str_replace($path_matched, "'./$filename'", $match[0]);
			},
			$data
		);
		return trim($data);
	}
	/**
	 * Put `$content` into `$dir` where filename is `md5($content)` with specified extension
	 *
	 * @param string $dir
	 * @param string $extension
	 * @param string $content
	 *
	 * @return string Filename (without full path)
	 */
	protected static function file_put_contents_with_hash ($dir, $extension, $content) {
		$hash = md5($content);
		file_put_contents("$dir/$hash.$extension", $content, LOCK_EX | FILE_BINARY);
		return "$hash.$extension";
	}
	/**
	 * Simple and fast JS minification
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public static function js ($data) {
		/**
		 * Split into array of lines
		 */
		$data = explode("\n", $data);
		/**
		 * Flag that is `true` when inside comment
		 */
		$in_comment              = false;
		$continue_after_position = -1;
		foreach ($data as $index => &$current_line) {
			if ($continue_after_position >= $index) {
				continue;
			}
			$next_line = isset($data[$index + 1]) ? trim($data[$index + 1]) : '';
			/**
			 * Remove starting and trailing spaces
			 */
			$current_line = trim($current_line);
			/**
			 * Remove single-line comments
			 */
			if (mb_strpos($current_line, '//') === 0) {
				$current_line = '';
				continue;
			}
			/**
			 * Starts with multi-line comment
			 */
			if (mb_strpos($current_line, '/*') === 0) {
				$in_comment = true;
			}
			if (!$in_comment) {
				$backticks_position = strpos($current_line, '`');
				/**
				 * Handling template strings can be tricky (since they might be multi-line), so let's fast-forward to the last backticks position and continue
				 * from there
				 */
				if ($backticks_position !== false) {
					$last_item_with_backticks = array_keys(
						array_filter(
							$data,
							function ($d) {
								return strpos($d, '`') !== false;
							}
						)
					);
					$last_item_with_backticks = array_pop($last_item_with_backticks);
					if ($last_item_with_backticks > $index) {
						$continue_after_position = $last_item_with_backticks;
						continue;
					}
				}
				/**
				 * Add new line at the end if only needed
				 */
				if (static::new_line_needed($current_line, $next_line)) {
					$current_line .= "\n";
				}
				/**
				 * Single-line comment
				 */
				$current_line = preg_replace('#^\s*//[^\'"]+$#', '', $current_line);
				/**
				 * If we are not sure - just add new line afterwards
				 */
				$current_line = preg_replace('#//.*$#', "\\0\n", $current_line);
			} else {
				/**
				 * End of multi-line comment
				 */
				if (strpos($current_line, '*/') !== false) {
					$current_line = explode('*/', $current_line)[1];
					$in_comment   = false;
				} else {
					$current_line = '';
				}
			}
		}
		$data = implode('', $data);
		$data = str_replace('</script>', '<\/script>', $data);
		return trim($data, ';').';';
	}
	/**
	 * @param string $current_line
	 * @param string $next_line
	 *
	 * @return bool
	 */
	protected static function new_line_needed ($current_line, $next_line) {
		/**
		 * Set of symbols that are safe to be concatenated without new line with anything else
		 */
		$regexp = /** @lang PhpRegExp */
			'[:;,.+\-*/{}?><^\'"\[\]=&(]';
		return
			$current_line &&
			$next_line &&
			!preg_match("#$regexp\$#", $current_line) &&
			!preg_match("#^$regexp#", $next_line);
	}
	/**
	 * Analyses file for scripts and styles, combines them into resulting files in order to optimize loading process
	 * (files with combined scripts and styles will be created)
	 *
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that contains specified in previous parameter content
	 * @param string   $target_directory_path  Target directory for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	public static function html ($data, $file, $target_directory_path, $vulcanization, &$not_embedded_resources = []) {
		static::html_process_links_and_styles($data, $file, $target_directory_path, $vulcanization, $not_embedded_resources);
		static::html_process_scripts($data, $file, $target_directory_path, $vulcanization, $not_embedded_resources);
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
	 * @param string   $file                   Path to file, that contains specified in previous parameter content
	 * @param string   $target_directory_path  Target directory for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 */
	protected static function html_process_scripts (&$data, $file, $target_directory_path, $vulcanization, &$not_embedded_resources) {
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
				$scripts_content      .= file_get_contents("$dir/$url").";\n";
			} else {
				$scripts_to_replace[] = $scripts[0][$index];
				$scripts_content      .= "$script[1];\n";
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
			$filename = static::file_put_contents_with_hash($target_directory_path, 'js', $scripts_content);
			// Add script with combined content file to the end
			$data                     .= "<script src=\"./$filename\"></script>";
			$not_embedded_resources[] = str_replace(getcwd(), '', "$target_directory_path/$filename");
		} else {
			// Add combined content inline script to the end
			$data .= "<script>$scripts_content</script>";
		}
	}
	/**
	 * @param string   $data                   Content of processed file
	 * @param string   $file                   Path to file, that contains specified in previous parameter content
	 * @param string   $target_directory_path  Target directory for resulting combined files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 */
	protected static function html_process_links_and_styles (&$data, $file, $target_directory_path, $vulcanization, &$not_embedded_resources) {
		if (!preg_match_all('/<link(.*)>|<style(.*)<\/style>/Uims', $data, $links_and_styles)) {
			return;
		}
		$dir = dirname($file);
		foreach ($links_and_styles[1] as $index => $link) {
			/**
			 * For plain styles we do not do anything fancy besides minifying its sources (no rearrangement or anything like that)
			 */
			if (mb_strpos($links_and_styles[0][$index], '</style>') > 0) {
				$content = explode('>', $links_and_styles[2][$index], 2)[1];
				$data    = str_replace(
					$content,
					static::css($content, $file, $target_directory_path, $not_embedded_resources),
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
			/**
			 * TODO: Polymer only supports `custom-style > style`, but no `link`-based counterpart, so we can't provide CSP-compatibility in general,
			 * thus always inlining styles into HTML
			 */
			if ($css_import || $stylesheet) {
				/**
				 * If content is link to CSS file
				 */
				$css  = static::css(
					file_get_contents("$dir/$url"),
					"$dir/$url",
					$target_directory_path,
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
				$data = str_replace(
					$links_and_styles[0][$index],
					static::html(
						file_get_contents("$dir/$url"),
						"$dir/$url",
						$target_directory_path,
						$vulcanization,
						$not_embedded_resources
					),
					$data
				);
			}
		}
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
	 * @param string $path
	 * @param string $dir
	 *
	 * @return bool
	 */
	protected static function is_relative_path_and_exists ($path, $dir) {
		return $dir && !preg_match('#^https?://#i', $path) && file_exists(static::absolute_path($path, $dir));
	}
	/**
	 * @param string $path
	 * @param string $dir
	 *
	 * @return string
	 */
	protected static function absolute_path ($path, $dir) {
		if (strpos($path, '/') === 0) {
			return realpath(getcwd().$path);
		}
		return realpath("$dir/$path");
	}
}
