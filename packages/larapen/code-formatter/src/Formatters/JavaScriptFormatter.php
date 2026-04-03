<?php

namespace Larapen\CodeFormatter\Formatters;

/**
 * JavaScript Code Formatter
 * Supports pretty print and minification
 */
class JavaScriptFormatter extends BaseFormatter
{
	/**
	 * Default options for JavaScript formatting
	 *
	 * @var array
	 */
	protected array $defaultOptions = [
		'indent_size'          => 4,
		'preserve_comments'    => true,
		'newline_before_brace' => false, // true for Allman style
		'space_before_paren'   => true,
		'semicolons'           => true, // Add missing semicolons
		'single_quotes'        => false, // Convert to single quotes
	];
	
	/**
	 * Pretty print JavaScript code
	 *
	 * @param string $code Raw JavaScript code
	 * @param array $options Formatting options
	 * @return string Formatted JavaScript code
	 */
	public function prettyPrint(string $code, array $options = []): string
	{
		$options = $this->mergeOptions($options);
		$code = $this->normalizeLineEndings($code);
		
		$formatted = '';
		$indentLevel = 0;
		$indent = str_repeat(' ', $options['indent_size']);
		$inString = false;
		$inComment = false;
		$stringChar = '';
		
		$lines = explode("\n", $code);
		
		foreach ($lines as $line) {
			$line = trim($line);
			
			if (empty($line)) {
				continue;
			}
			
			// Handle multi-line comments
			if (preg_match('/^\/\*/', $line)) {
				$inComment = true;
			}
			
			if ($inComment) {
				if ($options['preserve_comments']) {
					$formatted .= str_repeat($indent, $indentLevel) . $line . "\n";
				}
				if (preg_match('/\*\/$/', $line)) {
					$inComment = false;
				}
				continue;
			}
			
			// Handle single-line comments
			if (preg_match('/^\/\//', $line)) {
				if ($options['preserve_comments']) {
					$formatted .= str_repeat($indent, $indentLevel) . $line . "\n";
				}
				continue;
			}
			
			// Decrease indent for closing braces
			if (preg_match('/^}/', $line)) {
				$indentLevel = max(0, $indentLevel - 1);
			}
			
			// Add line with current indentation
			$formatted .= str_repeat($indent, max(0, $indentLevel)) . $line . "\n";
			
			// Count braces to adjust indentation
			$openBraces = substr_count($line, '{');
			$closeBraces = substr_count($line, '}');
			
			// Adjust indent level based on braces
			if ($openBraces > $closeBraces) {
				$indentLevel += ($openBraces - $closeBraces);
			}
			
			// Ensure indent level never goes negative
			$indentLevel = max(0, $indentLevel);
		}
		
		$formatted = $this->removeTrailingWhitespace($formatted);
		$formatted = $this->removeExcessiveBlankLines($formatted, 1);
		$formatted = rtrim($formatted) . "\n";
		
		return $formatted;
	}
	
	/**
	 * Minify JavaScript code
	 *
	 * @param string $code Raw JavaScript code
	 * @param array $options Minification options
	 * @return string Minified JavaScript code
	 */
	public function minify(string $code, array $options = []): string
	{
		$code = $this->normalizeLineEndings($code);
		
		// Remove multi-line comments
		$code = preg_replace('/\/\*[\s\S]*?\*\//', '', $code);
		
		// Remove single-line comments (but preserve URLs)
		$code = preg_replace('/(?<!:)\/\/.*$/m', '', $code);
		
		// Remove newlines and excessive whitespace
		$code = preg_replace('/\s+/', ' ', $code);
		
		// Remove spaces around operators and special characters
		$code = preg_replace('/\s*([{}()\[\];,=<>!+\-*\/%&|?:])\s*/', '$1', $code);
		
		// Add space after certain keywords to prevent syntax errors
		$keywords = ['return', 'typeof', 'delete', 'void', 'throw', 'new', 'in', 'instanceof'];
		foreach ($keywords as $keyword) {
			$code = preg_replace('/\b' . $keyword . '\b(?=[^\s])/', $keyword . ' ', $code);
		}
		
		// Remove space before function parenthesis
		$code = preg_replace('/function\s+\(/', 'function(', $code);
		
		// Trim
		$code = trim($code);
		
		return $code;
	}
	
	/**
	 * Validate JavaScript code
	 *
	 * @param string $code JavaScript code to validate
	 * @return bool True if basic validation passes
	 */
	public function validate(string $code): bool
	{
		if (empty($code)) {
			return false;
		}
		
		// Basic validation: check for balanced braces and brackets
		$openBraces = substr_count($code, '{');
		$closeBraces = substr_count($code, '}');
		$openBrackets = substr_count($code, '[');
		$closeBrackets = substr_count($code, ']');
		$openParens = substr_count($code, '(');
		$closeParens = substr_count($code, ')');
		
		return $openBraces === $closeBraces
			&& $openBrackets === $closeBrackets
			&& $openParens === $closeParens;
	}
	
	/**
	 * Get supported file extensions
	 *
	 * @return array
	 */
	public function getSupportedExtensions(): array
	{
		return ['js', 'mjs', 'cjs'];
	}
}
