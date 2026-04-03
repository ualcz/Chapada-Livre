<?php

namespace Larapen\CodeFormatter;

use Larapen\CodeFormatter\Contracts\FormatterInterface;
use Larapen\CodeFormatter\Formatters\CssFormatter;
use Larapen\CodeFormatter\Formatters\HtmlFormatter;
use Larapen\CodeFormatter\Formatters\JavaScriptFormatter;
use Larapen\CodeFormatter\Formatters\JsonFormatter;

/**
 * Main Code Formatter Factory
 * Provides easy access to all formatters
 */
class CodeFormatter
{
	/**
	 * Registered formatters
	 *
	 * @var array<string, FormatterInterface>
	 */
	protected static array $formatters = [];
	
	/**
	 * Register default formatters
	 */
	protected static function registerDefaults(): void
	{
		if (!empty(self::$formatters)) {
			return;
		}
		
		self::register('css', new CssFormatter());
		self::register('js', new JavaScriptFormatter());
		self::register('json', new JsonFormatter());
		self::register('html', new HtmlFormatter());
	}
	
	/**
	 * Register a custom formatter
	 *
	 * @param string $type Format type identifier
	 * @param FormatterInterface $formatter Formatter instance
	 */
	public static function register(string $type, FormatterInterface $formatter): void
	{
		self::$formatters[strtolower($type)] = $formatter;
	}
	
	/**
	 * Get formatter by type
	 *
	 * @param string $type Format type (css, js, json, html)
	 * @return FormatterInterface
	 * @throws \InvalidArgumentException If formatter not found
	 */
	public static function getFormatter(string $type): FormatterInterface
	{
		self::registerDefaults();
		
		$type = strtolower($type);
		
		if (!isset(self::$formatters[$type])) {
			throw new \InvalidArgumentException("No formatter registered for type: {$type}");
		}
		
		return self::$formatters[$type];
	}
	
	/**
	 * Get formatter by file extension
	 *
	 * @param string $filename Filename or extension
	 * @return FormatterInterface
	 * @throws \InvalidArgumentException If no formatter found for extension
	 */
	public static function getFormatterByExtension(string $filename): FormatterInterface
	{
		self::registerDefaults();
		
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		
		foreach (self::$formatters as $formatter) {
			if (in_array($extension, $formatter->getSupportedExtensions())) {
				return $formatter;
			}
		}
		
		throw new \InvalidArgumentException("No formatter found for extension: {$extension}");
	}
	
	/**
	 * Pretty print code
	 *
	 * @param string $code Code to format
	 * @param string $type Format type (css, js, json, html)
	 * @param array $options Formatting options
	 * @return string Formatted code
	 */
	public static function prettyPrint(string $code, string $type, array $options = []): string
	{
		return self::getFormatter($type)->prettyPrint($code, $options);
	}
	
	/**
	 * Minify code
	 *
	 * @param string $code Code to minify
	 * @param string $type Format type (css, js, json, html)
	 * @param array $options Minification options
	 * @return string Minified code
	 */
	public static function minify(string $code, string $type, array $options = []): string
	{
		return self::getFormatter($type)->minify($code, $options);
	}
	
	/**
	 * Format file and return formatted content
	 *
	 * @param string $filePath Path to file
	 * @param array $options Formatting options
	 * @return string Formatted content
	 * @throws \InvalidArgumentException If file doesn't exist
	 */
	public static function formatFile(string $filePath, array $options = []): string
	{
		if (!file_exists($filePath)) {
			throw new \InvalidArgumentException("File not found: {$filePath}");
		}
		
		$content = file_get_contents($filePath);
		$formatter = self::getFormatterByExtension($filePath);
		
		return $formatter->prettyPrint($content, $options);
	}
	
	/**
	 * Minify file and return minified content
	 *
	 * @param string $filePath Path to file
	 * @param array $options Minification options
	 * @return string Minified content
	 * @throws \InvalidArgumentException If file doesn't exist
	 */
	public static function minifyFile(string $filePath, array $options = []): string
	{
		if (!file_exists($filePath)) {
			throw new \InvalidArgumentException("File not found: {$filePath}");
		}
		
		$content = file_get_contents($filePath);
		$formatter = self::getFormatterByExtension($filePath);
		
		return $formatter->minify($content, $options);
	}
	
	/**
	 * Format file and save to same or different location
	 *
	 * @param string $inputPath Input file path
	 * @param string|null $outputPath Output file path (null = overwrite input)
	 * @param array $options Formatting options
	 * @return bool True on success
	 */
	public static function formatFileAndSave(string $inputPath, ?string $outputPath = null, array $options = []): bool
	{
		$formatted = self::formatFile($inputPath, $options);
		$outputPath = $outputPath ?? $inputPath;
		
		return file_put_contents($outputPath, $formatted) !== false;
	}
	
	/**
	 * Minify file and save to same or different location
	 *
	 * @param string $inputPath Input file path
	 * @param string|null $outputPath Output file path (null = overwrite input)
	 * @param array $options Minification options
	 * @return bool True on success
	 */
	public static function minifyFileAndSave(string $inputPath, ?string $outputPath = null, array $options = []): bool
	{
		$minified = self::minifyFile($inputPath, $options);
		$outputPath = $outputPath ?? $inputPath;
		
		return file_put_contents($outputPath, $minified) !== false;
	}
	
	/**
	 * Validate code
	 *
	 * @param string $code Code to validate
	 * @param string $type Format type
	 * @return bool True if valid
	 */
	public static function validate(string $code, string $type): bool
	{
		return self::getFormatter($type)->validate($code);
	}
	
	/**
	 * Get list of supported types
	 *
	 * @return array
	 */
	public static function getSupportedTypes(): array
	{
		self::registerDefaults();
		
		return array_keys(self::$formatters);
	}
}
