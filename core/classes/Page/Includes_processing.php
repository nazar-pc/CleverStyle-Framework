<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Page;

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
	 * @param string	$data	Content of processed file
	 * @param string	$file	Path to file, that includes specified in previous parameter content
	 *
	 * @return string	$data
	 */
	static function css ($data, $file) {
		$cwd = getcwd();
		chdir(dirname($file));
		/**
		 * Remove comments, tabs and new lines
		 */
		$data	= preg_replace('#(/\*.*?\*/)|\t|\n|\r#s', ' ', $data);
		/**
		 * Remove unnecessary spaces
		 */
		$data	= preg_replace('#\s*([,;>{}\(])\s*#s', '$1', $data);
		$data	= preg_replace('#\s+#s', ' ', $data);
		/**
		 * Return spaces required in media queries
		 */
		$data	= preg_replace('/\s(and|or)\(/s', ' $1 (', $data);
		/**
		 * Remove unnecessary trailing semicolons
		 */
		$data	= str_replace(';}', '}', $data);
		/**
		 * Minify repeated colors declarations
		 */
		$data	= preg_replace('/#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/is', '#$1$2$3', $data);
		/**
		 * Minify rgb colors declarations
		 */
		$data	= preg_replace_callback(
			'/rgb\(([0-9,\.]+)\)/is',
			function ($rgb) {
				$rgb	= explode(',', $rgb[1]);
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
		$data	= preg_replace('/([^0-9])0\.([0-9]+)/is', '$1.$2', $data);
		/**
		 * Includes processing
		 */
		$data	= preg_replace_callback(
			'/url\((.*?)\)|@import[\s\t\n\r]*[\'"](.*?)[\'"]/',
			function ($match) {
				$link		= trim($match[1], '\'" ');
				if (!static::is_relative_path_and_exists($link)) {
					return $match[0];
				}
				$content	= file_get_contents($link);
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
						$mime_type	= 'text/html';
				}
				$content	= base64_encode($content);
				return str_replace($match[1], "data:$mime_type;charset=utf-8;base64,$content", $match[0]);
			},
			$data
		);
		chdir($cwd);
		return $data;
	}
	/**
	 * Analyses file for scripts and styles, combines them into resulting files in order to optimize loading process
	 * (files with combined scripts and styles will be created)
	 *
	 * @param string		$data			Content of processed file
	 * @param string		$file			Path to file, that includes specified in previous parameter content
	 * @param string		$base_filename	Base filename for resulting combined files
	 * @param bool|string	$destination	Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string	$data
	 */
	static function html ($data, $file, $base_filename, $destination) {
		$cwd = getcwd();
		chdir(dirname($file));
		static::html_process_scripts($data, $base_filename, $destination);
		static::html_process_links_and_styles($data, $file, $base_filename, $destination);
		chdir($cwd);
		return $data;
	}
	/**
	 * @param string		$data			Content of processed file
	 * @param string		$base_filename	Base filename for resulting combined files
	 * @param bool|string	$destination	Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string
	 */
	protected static function html_process_scripts (&$data, $base_filename, $destination) {
		if (!preg_match_all('/<script(.*)<\/script>/Uims', $data, $scripts)) {
			return;
		}
		$scripts_content	= '';
		$scripts_to_replace	= [];
		foreach ($scripts[1] as $index => $script) {
			$script	= explode('>', $script);
			if (preg_match('/src\s*=\s*[\'"](.*)[\'"]/Uims', $script[0], $url)) {
				$url	= $url[1];
				if (!static::is_relative_path_and_exists($url)) {
					continue;
				}
				$scripts_to_replace[]	= $scripts[0][$index];
				$scripts_content		.= file_get_contents($url).";\n";
			} else {
				$scripts_content		.= "$script[1];\n";
			}
		}
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
			$content_md5	= substr(md5($scripts_content), 0, 5);
			file_put_contents(
				"$destination/$base_filename.js",
				gzencode($scripts_content, 9),
				LOCK_EX | FILE_BINARY
			);
			// Replace first script with combined file
			$data	= str_replace(
				$scripts_to_replace[0],
				"<script src=\"$base_filename.js?$content_md5\"></script>",
				$data
			);
		} else {
			// Replace first script with combined content
			$data	= str_replace(
				$scripts_to_replace[0],
				"<script>$scripts_content</script>",
				$data
			);
		}
		// Remove the rest of scripts
		$data	= str_replace($scripts_to_replace, '', $data);
	}
	/**
	 * @param string		$data			Content of processed file
	 * @param string		$file			Path to file, that includes specified in previous parameter content
	 * @param string		$base_filename	Base filename for resulting combined files
	 * @param bool|string	$destination	Directory where to put combined files or <i>false</i> to make includes built-in (vulcanization)
	 *
	 * @return string
	 */
	protected static function html_process_links_and_styles (&$data, $file, $base_filename, $destination) {
		if (!preg_match_all('/<link(.*)>|<style(.*)<\/style>/Uims', $data, $links_and_styles)) {
			return;
		}
		$shim							= false;
		$styles_content					= '';
		$imports_content				= '';
		$links_and_styles_to_replace	= [];
		foreach ($links_and_styles[1] as $index => $link) {
			/**
			 * If content is link to CSS file
			 */
			if (static::has_relative_href($link, $url, 'stylesheet')) {
				$links_and_styles_to_replace[]	= $links_and_styles[0][$index];
				$shim							= $shim || static::need_shimming($links_and_styles[0][$index]);
				$styles_content					.= static::css(
					file_get_contents($url),
					$url
				);
			/**
			 * If content is HTML import
			 */
			} elseif (static::has_relative_href($link, $url, 'import')) {
				$links_and_styles_to_replace[]	= $links_and_styles[0][$index];
				$imports_content				.= static::html(
					file_get_contents($url),
					$url,
					"$base_filename-".basename($url, '.html'),
					$destination
				);
			/**
			 * If content is plain CSS
			 */
			} elseif (mb_strpos($links_and_styles[0][$index], '</style>') !== -1) {
				$links_and_styles_to_replace[]	= $links_and_styles[0][$index];
				$shim							= $shim || static::need_shimming($links_and_styles[0][$index]);
				$styles_content					.= static::css(
					explode('>', $links_and_styles[2][$index], 2)[1],
					$file
				);
			}
		}
		if (!$links_and_styles_to_replace) {
			return;
		}
		$shim	= $shim ? ' shim-shadowdom' : '';
		/**
		 * If there is destination - put contents into the file, and put link to it, otherwise put minified content back
		 */
		if ($destination) {
			/**
			 * md5 to distinguish modifications of the files
			 */
			$content_md5	= substr(md5($styles_content), 0, 5);
			file_put_contents(
				"$destination/$base_filename.css",
				gzencode($styles_content, 9),
				LOCK_EX | FILE_BINARY
			);
			// Replace first link or style with combined file
			$data	= str_replace(
				$links_and_styles_to_replace[0],
				"<link rel=\"stylesheet\" href=\"$base_filename.css?$content_md5\"$shim>",
				$data
			);
		} else {
			// Replace first link or style with combined content
			$data	= str_replace(
				$links_and_styles_to_replace[0],
				"<style$shim>$styles_content</style>",
				$data
			);
		}
		// Remove the rest of links and styles
		$data	= str_replace($links_and_styles_to_replace, '', $data);
		// Add imports to the end of file
		$data	.= $imports_content;
	}
	/**
	 * @param string $link
	 * @param string $url
	 * @param string $rel
	 *
	 * @return bool
	 */
	protected static function has_relative_href ($link, &$url, $rel) {
		$result =
			$link &&
			preg_match('/rel\s*=\s*[\'"]'.$rel.'[\'"]/Uims', $link) &&
			preg_match('/href\s*=\s*[\'"](.*)[\'"]/Uims', $link, $url);
		if ($result && static::is_relative_path_and_exists($url[1])) {
			$url = $url[1];
			return true;
		}
		return false;
	}
	/**
	 * Simple check for http[s], ftp and absolute links
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	protected static function is_relative_path_and_exists ($path) {
		return !preg_match('#^(http://|https://|ftp://|/)#i', $path) && file_exists($path);
	}
	/**
	 * @param string $content
	 *
	 * @return bool
	 */
	protected static function need_shimming ($content) {
		return preg_match('/shim-shadowdom/Uims', $content);
	}
}
