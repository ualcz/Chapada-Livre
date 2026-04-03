<?php

namespace Larapen\CodeFormatter\Formatters;

use Larapen\CodeFormatter\Contracts\FormatterInterface;

/**
 * Abstract base class for all formatters
 */
abstract class BaseFormatter implements FormatterInterface
{
	/**
	 * Default formatting options
	 *
	 * @var array
	 */
	protected array $defaultOptions = [];
	
	/**
	 * Merge user options with defaults
	 *
	 * @param array $options User-provided options
	 * @return array Merged options
	 */
	protected function mergeOptions(array $options = []): array
	{
		return array_merge($this->defaultOptions, $options);
	}
	
	/**
	 * Normalize line endings to \n
	 *
	 * @param string $code Code to normalize
	 * @return string Normalized code
	 */
	protected function normalizeLineEndings(string $code): string
	{
		return str_replace(["\r\n", "\r"], "\n", $code);
	}
	
	/**
	 * Remove trailing whitespace from each line
	 *
	 * @param string $code Code to clean
	 * @return string Cleaned code
	 */
	protected function removeTrailingWhitespace(string $code): string
	{
		return preg_replace('/[ \t]+$/m', '', $code);
	}
	
	/**
	 * Remove multiple consecutive blank lines
	 *
	 * @param string $code Code to clean
	 * @param int $maxBlankLines Maximum number of consecutive blank lines
	 * @return string Cleaned code
	 */
	protected function removeExcessiveBlankLines(string $code, int $maxBlankLines = 2): string
	{
		$pattern = '/\n{' . ($maxBlankLines + 1) . ',}/';
		$replacement = str_repeat("\n", $maxBlankLines);
		
		return preg_replace($pattern, $replacement, $code);
	}
	
	/**
	 * Create indentation string
	 *
	 * @param int $level Indentation level
	 * @param int $size Number of spaces per level
	 * @return string Indentation string
	 */
	protected function indent(int $level, int $size = 4): string
	{
		return str_repeat(' ', $level * $size);
	}
	
	/**
	 * Basic validation - can be overridden by specific formatters
	 *
	 * @param string $code Code to validate
	 * @return bool Always true for base implementation
	 */
	public function validate(string $code): bool
	{
		return !empty($code);
	}
}
