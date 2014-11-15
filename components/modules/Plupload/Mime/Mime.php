<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Mime
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @since      File available since Release 1.0.0
 */

namespace Karwana\Mime;

class Mime {

	private static $mime_types;

	private static function ensureDataLoaded() {
		if (!isset(static::$mime_types)) {
			$json_path = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Resources', 'mime_types.json'));
			static::$mime_types = json_decode(file_get_contents($json_path), true);
		}
	}


	/**
	 * Get the MIME type associated with a given extension.
	 *
	 * @param string $extension A file extension e.g. pdf.
	 *
	 * @return string|null The associated MIME type or null if none found.
	 */
	public static function getTypeForExtension($extension) {
		static::ensureDataLoaded();

		$extension = strtolower($extension);
		foreach (static::$mime_types as $mime_type => $extensions) {
			if (is_array($extensions)) {
				if (in_array($extension, $extensions, true)) {
					return $mime_type;
				}
			} else if ($extension === $extensions) {
				return $mime_type;
			}
		}
	}


	/**
	 * Get the extension associated with a given MIME type.
	 *
	 * @param string $mime_type A MIME type e.g. application/pdf.
	 *
	 * @return string|null The first available associated extension or null if none found.
	 */
	public static function getExtensionForType($mime_type) {
		static::ensureDataLoaded();

		if (!static::hasType($mime_type)) {
			return;
		}

		$extensions = static::$mime_types[$mime_type];
		if (is_array($extensions)) {
			return $extensions[0];
		}

		return $extensions;
	}


	/**
	 * Get all the extensions associated with a given MIME type.
	 *
	 * @param string $mime_type A MIME type e.g. application/pdf.
	 *
	 * @return string[]|null An array of all the associated extensions or null if none found.
	 */
	public static function getExtensionsForType($mime_type) {
		static::ensureDataLoaded();

		if (static::hasType($mime_type)) {
			return (array) static::$mime_types[$mime_type];
		}
	}


	/**
	 * Check if an extension is known.
	 *
	 * @param string $extension An extension e.g. pdf.
	 *
	 * @return boolean
	 */
	public static function hasExtension($extension) {
		static::ensureDataLoaded();

		$extension = strtolower($extension);
		foreach (static::$mime_types as $extensions) {
			if (is_array($extensions)) {
				if (in_array($extension, $extensions, true)) {
					return true;
				}
			} else if ($extension === $extensions) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Check if a MIME type is known.
	 *
	 * @param string $mime_type A MIME type e.g. application/pdf.
	 *
	 * @return boolean
	 */
	public static function hasType($mime_type) {
		static::ensureDataLoaded();
		return isset(static::$mime_types[$mime_type]);
	}


	/**
	 * Guess the MIME type of a given file, first by checking the extension then by falling back to magic.
	 *
	 * @param string $file_path Relative or absolute path to an existing file.
	 * @param string $reference_name Use this name for detection based on the extension.
	 * @param string $default Default MIME type.
	 *
	 * @return string|null The associated MIME type or the default if none found.
	 */
	public static function guessType($file_path, $reference_name = null, $default = 'application/octet-stream') {
		if (!$reference_name) {
			$reference_name = basename($file_path);
		}

		$extension = pathinfo($reference_name, PATHINFO_EXTENSION);
		if ($extension and $mime_type = static::getTypeForExtension($extension)) {
			return $mime_type;
		}

		// While it's true that the extension doesn't determine the type,
		// only use finfo as a fallback because it's bad at detecting text
		// types like CSS and JavaScript.
		if ($mime_type = static::getMagicType($file_path)) {
			return $mime_type;
		}

		return $default;
	}


	/**
	 * Guess the extension of a given file, first by checking the path then by falling back to magic.
	 *
	 * @param string $file_path Relative or absolute path to an existing file.
	 * @param string $reference_name Use this name for detection based on the extension.
	 * @param string $default Default extension.
	 *
	 * @return string|null The associated extension or the default if none found.
	 */
	public static function guessExtension($file_path, $reference_name = null, $default = 'bin') {
		if (!$reference_name) {
			$reference_name = basename($file_path);
		}

		if ($extension = pathinfo($reference_name, PATHINFO_EXTENSION) and static::hasExtension($extension)) {
			return strtolower($extension);
		}

		$mime_type = static::getMagicType($file_path);
		if ($mime_type and $extension = static::getExtensionForType($mime_type)) {
			return $extension;
		}

		return $default;
	}


	/**
	 * Get the MIME type of a file using magic.
	 *
	 * @param string $file_path Relative or absolute path to an existing file.
	 *
	 * @return string|null The associated MIME type or null if no known type was detected.
	 */
	public static function getMagicType($file_path) {
		$file_info = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($file_info, $file_path);
		finfo_close($file_info);

		// Only return valid types, in order to maintain circular compatibility between methods.
		if (static::hasType($mime_type)) {
			return $mime_type;
		}
	}
}
