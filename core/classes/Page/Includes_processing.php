<?php
/**
 * @package   CleverStyle CMS
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
	 * Analyses file for images, fonts and css links and include they content into single resulting css file.
	 *
	 * Supports next file extensions for possible includes:
	 * jpeg, jpe, jpg, gif, png, ttf, ttc, svg, svgz, woff, eot, css
	 *
	 * @param string $data Content of processed file
	 * @param string $file Path to file, that includes specified in previous parameter content
	 *
	 * @return string    $data
	 */
	static function css ($data, $file) {
		$dir = dirname($file);
		/**
		 * Remove comments, tabs and new lines
		 */
		$data = preg_replace('#(/\*.*?\*/)|\t|\n|\r#s', ' ', $data);
		/**
		 * Remove unnecessary spaces
		 */
		$data = preg_replace('/\s*([,;>{}\(])\s*/', '$1', $data);
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
			'/rgb\(([0-9,\.]+)\)/i',
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
			function ($match) use ($dir) {
				$link = trim($match[1], '\'" ');
				$link = explode('?', $link, 2)[0];
				if (!static::is_relative_path_and_exists($link, $dir)) {
					return $match[0];
				}
				/**
				 * Do not inline files bigger than 4 KiB
				 */
				$content = file_get_contents("$dir/$link");
				if (filesize("$dir/$link") > 4096) {
					$path_relatively_to_the_root = str_replace(getcwd(), '', realpath("$dir/$link"));
					$path_relatively_to_the_root .= '?'.substr(md5($content), 0, 5);
					return str_replace($match[1], $path_relatively_to_the_root, $match[0]);
				}
				switch (file_extension($link)) {
					case 'jpeg':
					case 'jpe':
					case 'jpg':
						$mime_type = 'image/jpg';
						break;
					case 'gif':
						$mime_type = 'image/gif';
						break;
					case 'png':
						$mime_type = 'image/png';
						break;
					case 'ttf':
					case 'ttc':
						$mime_type = 'application/x-font-ttf';
						break;
					case 'svg':
					case 'svgz':
						$mime_type = 'image/svg+xml';
						break;
					case 'woff':
						$mime_type = 'application/x-font-woff';
						break;
					case 'eot':
						$mime_type = 'application/vnd.ms-fontobject';
						break;
					case 'css':
						$mime_type = 'text/css';
						/**
						 * For recursive includes processing, if CSS file includes others CSS files
						 */
						$content = static::css($content, $link);
						break;
					default:
						$mime_type = 'text/html';
				}
				$content = base64_encode($content);
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
		$regexp = '[:;,.+\-*\/{}?><^\'"\[\]=&\(\)]';
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
				!preg_match("/$regexp\$/", $d) &&
				!preg_match("/^$regexp/", $next_line)
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
		return trim($data, ';').';';
	}
	/**
	 * Analyses file for scripts and styles, combines them into resulting files in order to optimize loading process
	 * (files with combined scripts and styles will be created)
	 *
	 * @param string      $data          Content of processed file
	 * @param string      $file          Path to file, that includes specified in previous parameter content
	 * @param string      $base_filename Base filename for resulting combined files
	 * @param bool|string $destination   Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string    $data
	 */
	static function html ($data, $file, $base_filename, $destination) {
		static::html_process_scripts($data, $file, $base_filename, $destination);
		static::html_process_links_and_styles($data, $file, $base_filename, $destination);
		return preg_replace("/\n+/", "\n", $data);
	}
	/**
	 * @param string      $data          Content of processed file
	 * @param string      $file          Path to file, that includes specified in previous parameter content
	 * @param string      $base_filename Base filename for resulting combined files
	 * @param bool|string $destination   Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string
	 */
	protected static function html_process_scripts (&$data, $file, $base_filename, $destination) {
		if (!preg_match_all('/<script(.*)<\/script>/Uims', $data, $scripts)) {
			return;
		}
		$scripts_content    = '';
		$scripts_to_replace = [];
		$dir                = dirname($file);
		foreach ($scripts[1] as $index => $script) {
			$script = explode('>', $script);
			if (preg_match('/src\s*=\s*[\'"](.*)[\'"]/Uims', $script[0], $url)) {
				$url = $url[1];
				if (!static::is_relative_path_and_exists($url, $dir)) {
					continue;
				}
				$scripts_to_replace[] = $scripts[0][$index];
				$scripts_content .= file_get_contents("$dir/$url").";\n";
			} else {
				$scripts_content .= "$script[1];\n";
			}
		}
		$scripts_content = static::js($scripts_content);
		if (!$scripts_to_replace) {
			return;
		}
		/**
		 * If there is destination - put contents into the file, and put link to it, otherwise put minified content back
		 */
		if ($destination) {
			/**
			 * md5 to distinguish modifications of the files
			 */
			$content_md5 = substr(md5($scripts_content), 0, 5);
			file_put_contents(
				"$destination/$base_filename.js",
				gzencode($scripts_content, 9),
				LOCK_EX | FILE_BINARY
			);
			// Replace first script with combined file
			$data = str_replace(
				$scripts_to_replace[0],
				"<script src=\"$base_filename.js?$content_md5\"></script>",
				$data
			);
		} else {
			// Replace first script with combined content
			$data = str_replace(
				$scripts_to_replace[0],
				"<script>$scripts_content</script>",
				$data
			);
		}
		// Remove the rest of scripts
		$data = str_replace($scripts_to_replace, '', $data);
	}
	/**
	 * @param string      $data          Content of processed file
	 * @param string      $file          Path to file, that includes specified in previous parameter content
	 * @param string      $base_filename Base filename for resulting combined files
	 * @param bool|string $destination   Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string
	 */
	protected static function html_process_links_and_styles (&$data, $file, $base_filename, $destination) {
		// Drop Polymer inclusion, since it is already present
		$data = str_replace('<link rel="import" href="../polymer/polymer.html">', '', $data);
		if (!preg_match_all('/<link(.*)>|<style(.*)<\/style>/Uims', $data, $links_and_styles)) {
			return;
		}
		$styles_content              = '';
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
					static::css($content, $file),
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
			 * If content is link to CSS file
			 */
			if ($css_import || $stylesheet) {
				$links_and_styles_to_replace[] = $links_and_styles[0][$index];
				$styles_content .= static::css(
					file_get_contents("$dir/$url"),
					"$dir/$url"
				);
				/**
				 * If content is HTML import
				 */
			} elseif ($import) {
				$links_and_styles_to_replace[] = $links_and_styles[0][$index];
				$imports_content .= static::html(
					file_get_contents("$dir/$url"),
					"$dir/$url",
					"$base_filename-".basename($url, '.html'),
					$destination
				);
			}
		}
		if (!$links_and_styles_to_replace) {
			return;
		}
		/**
		 * If there is destination - put contents into the file, and put link to it, otherwise put minified content back
		 */
		if ($destination) {
			/**
			 * md5 to distinguish modifications of the files
			 */
			$content_md5 = substr(md5($styles_content), 0, 5);
			file_put_contents(
				"$destination/$base_filename.css",
				gzencode($styles_content, 9),
				LOCK_EX | FILE_BINARY
			);
			// Replace first link or style with combined file
			$data = str_replace(
				$links_and_styles_to_replace[0],
				"<link rel=\"import\" type=\"css\" href=\"$base_filename.css?$content_md5\">",
				$data
			);
		} else {
			// Replace first `<template>` with combined content
			$data = preg_replace(
				'/<template>/',
				"$0<style>$styles_content</style>",
				$data,
				1
			);
		}
		// Remove the rest of links and styles
		$data = str_replace($links_and_styles_to_replace, '', $data);
		// Add imports to the end of file
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
