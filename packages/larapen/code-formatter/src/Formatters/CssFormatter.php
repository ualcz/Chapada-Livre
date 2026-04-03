<?php

namespace Larapen\CodeFormatter\Formatters;

/**
 * CSS Code Formatter
 * Supports pretty print and minification
 */
class CssFormatter extends BaseFormatter
{
	/**
	 * Default options for CSS formatting
	 *
	 * @var array
	 */
	protected array $defaultOptions = [
		'indent_size'           => 4,
		'preserve_comments'     => true,
		'newline_between_rules' => true,
		'space_before_brace'    => true,
		'space_after_colon'     => true,
		'lowercase_selectors'   => false,
		'sort_properties'       => false,
		'compress_colors'       => false, // #ffffff -> #fff
	];
	
	/**
	 * Pretty print CSS code
	 *
	 * @param string $code Raw CSS code
	 * @param array $options Formatting options
	 * @return string Formatted CSS code
	 */
	public function prettyPrint(string $code, array $options = []): string
	{
		$options = $this->mergeOptions($options);
		$code = $this->normalizeLineEndings($code);
		
		// Remove all existing indentation
		$code = preg_replace('/^\s+/m', '', $code);
		
		$formatted = '';
		$indentLevel = 0;
		$indent = str_repeat(' ', $options['indent_size']);
		$inComment = false;
		$inRule = false;
		$currentProperties = [];
		
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
					if ($options['preserve_comments'] && $options['newline_between_rules']) {
						$formatted .= "\n";
					}
				}
				continue;
			}
			
			// Handle closing brace
			if ($line === '}') {
				// Output collected properties
				if ($options['sort_properties'] && !empty($currentProperties)) {
					sort($currentProperties);
				}
				
				foreach ($currentProperties as $property) {
					$formatted .= str_repeat($indent, $indentLevel) . $property . "\n";
				}
				$currentProperties = [];
				
				$indentLevel--;
				$formatted .= str_repeat($indent, $indentLevel) . "}\n";
				
				if ($options['newline_between_rules']) {
					$formatted .= "\n";
				}
				
				$inRule = false;
				continue;
			}
			
			// Handle selector with opening brace
			if (str_contains($line, '{')) {
				$selector = trim(str_replace('{', '', $line));
				
				if ($options['lowercase_selectors'] && !preg_match('/@[a-z-]+/', $selector)) {
					$selector = strtolower($selector);
				}
				
				$brace = $options['space_before_brace'] ? ' {' : '{';
				$formatted .= str_repeat($indent, $indentLevel) . $selector . $brace . "\n";
				$indentLevel++;
				$inRule = true;
				continue;
			}
			
			// Handle CSS properties (but not selectors with pseudo-classes)
			if (str_contains($line, ':') && $this->isCssProperty($line, $inRule)) {
				$parts = explode(':', $line, 2);
				$property = trim($parts[0]);
				$value = trim($parts[1] ?? '');
				
				// Compress colors if option is enabled
				if ($options['compress_colors']) {
					$value = $this->compressColors($value);
				}
				
				$colon = $options['space_after_colon'] ? ': ' : ':';
				$formattedProperty = $property . $colon . $value;
				
				if ($options['sort_properties'] && $inRule) {
					$currentProperties[] = $formattedProperty;
				} else {
					$formatted .= str_repeat($indent, $indentLevel) . $formattedProperty . "\n";
				}
				continue;
			}
			
			// Handle other content (selectors, at-rules, etc.)
			$formatted .= str_repeat($indent, $indentLevel) . $line . "\n";
		}
		
		$formatted = $this->removeTrailingWhitespace($formatted);
		$formatted = rtrim($formatted) . "\n";
		
		return $formatted;
	}
	
	/**
	 * Minify CSS code
	 *
	 * @param string $code Raw CSS code
	 * @param array $options Minification options
	 * @return string Minified CSS code
	 */
	public function minify(string $code, array $options = []): string
	{
		$options = $this->mergeOptions(array_merge($options, [
			'preserve_comments' => $options['preserve_comments'] ?? false,
			'compress_colors'   => true,
		]));
		
		$code = $this->normalizeLineEndings($code);
		
		// Remove comments unless preservation is requested
		if (!$options['preserve_comments']) {
			$code = preg_replace('/\/\*[\s\S]*?\*\//', '', $code);
		}
		
		// Remove newlines and tabs
		$code = str_replace(["\n", "\r", "\t"], '', $code);
		
		// Remove spaces around special characters
		$code = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $code);
		
		// Remove space before !important
		$code = str_replace(' !important', '!important', $code);
		
		// Compress colors
		$code = $this->compressColors($code);
		
		// Remove unnecessary semicolons before closing braces
		$code = str_replace(';}', '}', $code);
		
		// Remove leading zeros (0.5 -> .5)
		$code = preg_replace('/([^0-9])0+\.(\d+)/', '$1.$2', $code);
		
		// Remove units for zero values
		$code = preg_replace('/:0(px|em|rem|%|vh|vw|pt|cm|mm|in|pc)/', ':0', $code);
		
		// Compress whitespace
		$code = preg_replace('/\s+/', ' ', $code);
		$code = trim($code);
		
		return $code;
	}
	
	/**
	 * Compress color values (#ffffff -> #fff)
	 *
	 * @param string $value CSS value
	 * @return string Compressed value
	 */
	protected function compressColors(string $value): string
	{
		// Compress 6-digit hex to 3-digit where possible
		$value = preg_replace_callback(
			'/#([0-9a-fA-F])\1([0-9a-fA-F])\2([0-9a-fA-F])\3\b/',
			function ($matches) {
				return '#' . $matches[1] . $matches[2] . $matches[3];
			},
			$value
		);
		
		// Convert named colors to hex (optional, could expand this)
		$namedColors = [
			'white' => '#fff',
			'black' => '#000',
		];
		
		foreach ($namedColors as $name => $hex) {
			$value = str_ireplace($name, $hex, $value);
		}
		
		return $value;
	}
	
	/**
	 * Validate CSS code
	 *
	 * @param string $code CSS code to validate
	 * @return bool True if valid
	 */
	public function validate(string $code): bool
	{
		if (empty($code)) {
			return false;
		}
		
		// Basic validation: check for balanced braces
		$openBraces = substr_count($code, '{');
		$closeBraces = substr_count($code, '}');
		
		return $openBraces === $closeBraces;
	}
	
	/**
	 * Get supported file extensions
	 *
	 * @return array
	 */
	public function getSupportedExtensions(): array
	{
		return ['css'];
	}
	
	/**
	 * Determine if a line is a CSS property declaration (not a selector with pseudo-classes)
	 *
	 * @param string $line The line to check
	 * @param bool $inRule Whether we're currently inside a CSS rule
	 * @return bool True if it's a CSS property declaration
	 */
	protected function isCssProperty(string $line, bool $inRule): bool
	{
		// If not inside a rule block, it's a selector (not a property)
		if (!$inRule) {
			return false;
		}
		
		// Check if line contains pseudo-classes or pseudo-elements
		// Common pseudo-classes: :hover, :focus, :active, :visited, :first-child, :last-child, :nth-child(), etc.
		// Common pseudo-elements: ::before, ::after, ::first-line, ::first-letter, etc.
		$pseudoPattern = '/:(hover|focus|active|visited|link|'
			. 'first-child|last-child|nth-child|nth-last-child|nth-of-type|nth-last-of-type|'
			. 'first-of-type|last-of-type|only-child|only-of-type|'
			. 'empty|root|target|enabled|disabled|checked|indeterminate|'
			. 'valid|invalid|required|optional|read-only|read-write|'
			. 'in-range|out-of-range|default|placeholder-shown|'
			. 'lang|dir|not|is|where|has)\b/i';
		
		if (preg_match($pseudoPattern, $line)) {
			return false;
		}
		
		// Check if line contains attribute selectors with colons (rare but possible)
		if (preg_match('/\[[^\]]*:[^\]]*\]/', $line)) {
			return false;
		}
		
		// Check if it looks like a property declaration
		// Properties typically have format: property: value; or property: value
		// The part before colon should be a valid CSS property name (alphanumeric, hyphens)
		$colonPos = strpos($line, ':');
		if ($colonPos === false) {
			return false;
		}
		
		$beforeColon = trim(substr($line, 0, $colonPos));
		
		// CSS property names contain only letters, numbers, and hyphens
		// They don't contain spaces, dots, hash symbols, or other selector characters
		if (preg_match('/^[a-zA-Z-]+$/', $beforeColon)) {
			return true;
		}
		
		return false;
	}
}
