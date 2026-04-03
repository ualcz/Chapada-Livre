<?php

namespace Larapen\CodeFormatter\Formatters;

/**
 * HTML Code Formatter
 * Supports pretty print and minification
 */
class HtmlFormatter extends BaseFormatter
{
	/**
	 * Default options for HTML formatting
	 *
	 * @var array
	 */
	protected array $defaultOptions = [
		'indent_size'       => 4,
		'preserve_comments' => true,
		'inline_tags'       => ['span', 'a', 'strong', 'em', 'b', 'i', 'u', 'small', 'code'],
		'self_closing_tags' => ['img', 'br', 'hr', 'input', 'meta', 'link'],
	];
	
	/**
	 * Pretty print HTML code
	 *
	 * @param string $code Raw HTML code
	 * @param array $options Formatting options
	 * @return string Formatted HTML code
	 */
	public function prettyPrint(string $code, array $options = []): string
	{
		$options = $this->mergeOptions($options);
		$code = $this->normalizeLineEndings($code);
		
		// Remove existing indentation
		$code = preg_replace('/^\s+/m', '', $code);
		
		$formatted = '';
		$indentLevel = 0;
		$indent = str_repeat(' ', $options['indent_size']);
		
		// Split by tags
		preg_match_all('/(<[^>]+>|[^<]+)/', $code, $matches);
		$tokens = $matches[0];
		
		foreach ($tokens as $token) {
			$token = trim($token);
			
			if (empty($token)) {
				continue;
			}
			
			// Check if it's a tag
			if (preg_match('/^</', $token)) {
				// Comment
				if (preg_match('/^<!--/', $token)) {
					if ($options['preserve_comments']) {
						$formatted .= str_repeat($indent, $indentLevel) . $token . "\n";
					}
					continue;
				}
				
				// Closing tag
				if (preg_match('/^<\/([a-zA-Z0-9]+)>/', $token, $matches)) {
					$tagName = $matches[1];
					
					if (!in_array($tagName, $options['inline_tags'])) {
						$indentLevel--;
						$formatted .= str_repeat($indent, $indentLevel) . $token . "\n";
					} else {
						$formatted = rtrim($formatted) . $token . "\n";
					}
					continue;
				}
				
				// Self-closing or opening tag
				if (preg_match('/^<([a-zA-Z0-9]+)/', $token, $matches)) {
					$tagName = $matches[1];
					$isSelfClosing = preg_match('/\/>$/', $token) || in_array($tagName, $options['self_closing_tags']);
					
					if (!in_array($tagName, $options['inline_tags'])) {
						$formatted .= str_repeat($indent, $indentLevel) . $token . "\n";
						
						if (!$isSelfClosing) {
							$indentLevel++;
						}
					} else {
						$formatted .= str_repeat($indent, $indentLevel) . $token;
					}
					continue;
				}
				
				// Other tags
				$formatted .= str_repeat($indent, $indentLevel) . $token . "\n";
			} else {
				// Text content
				$token = trim($token);
				if (!empty($token)) {
					$formatted .= str_repeat($indent, $indentLevel) . $token . "\n";
				}
			}
		}
		
		$formatted = $this->removeTrailingWhitespace($formatted);
		$formatted = rtrim($formatted) . "\n";
		
		return $formatted;
	}
	
	/**
	 * Minify HTML code
	 *
	 * @param string $code Raw HTML code
	 * @param array $options Minification options
	 * @return string Minified HTML code
	 */
	public function minify(string $code, array $options = []): string
	{
		// Remove HTML comments
		$code = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $code);
		
		// Remove whitespace between tags
		$code = preg_replace('/>\s+</', '><', $code);
		
		// Remove leading/trailing whitespace
		$code = preg_replace('/^\s+/m', '', $code);
		$code = preg_replace('/\s+$/m', '', $code);
		
		// Compress multiple spaces into one
		$code = preg_replace('/\s{2,}/', ' ', $code);
		
		// Remove whitespace around equals signs in attributes
		$code = preg_replace('/\s*=\s*/', '=', $code);
		
		return trim($code);
	}
	
	/**
	 * Validate HTML code
	 *
	 * @param string $code HTML code to validate
	 * @return bool True if basic validation passes
	 */
	public function validate(string $code): bool
	{
		if (empty($code)) {
			return false;
		}
		
		// Basic validation: just check if it's not empty and has some tags
		return preg_match('/<[^>]+>/', $code) === 1;
	}
	
	/**
	 * Get supported file extensions
	 *
	 * @return array
	 */
	public function getSupportedExtensions(): array
	{
		return ['html', 'htm'];
	}
}
