<?php

namespace Larapen\CodeFormatter\Contracts;

/**
 * Interface for all code formatters
 */
interface FormatterInterface
{
	/**
	 * Format code with pretty print (human-readable)
	 *
	 * @param string $code Raw code to format
	 * @param array $options Formatting options
	 * @return string Formatted code
	 */
	public function prettyPrint(string $code, array $options = []): string;
	
	/**
	 * Minify code (remove unnecessary whitespace and comments)
	 *
	 * @param string $code Raw code to minify
	 * @param array $options Minification options
	 * @return string Minified code
	 */
	public function minify(string $code, array $options = []): string;
	
	/**
	 * Validate code syntax
	 *
	 * @param string $code Code to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validate(string $code): bool;
	
	/**
	 * Get the file extensions supported by this formatter
	 *
	 * @return array Array of file extensions
	 */
	public function getSupportedExtensions(): array;
}
