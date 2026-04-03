<?php

namespace Larapen\CodeFormatter\Formatters;

/**
 * JSON Code Formatter
 * Supports pretty print and minification
 */
class JsonFormatter extends BaseFormatter
{
	/**
	 * Default options for JSON formatting
	 *
	 * @var array
	 */
	protected array $defaultOptions = [
		'indent_size'  => 4,
		'sort_keys'    => false,
		'ensure_ascii' => false,
	];
	
	/**
	 * Pretty print JSON code
	 *
	 * @param string $code Raw JSON code
	 * @param array $options Formatting options
	 * @return string Formatted JSON code
	 */
	public function prettyPrint(string $code, array $options = []): string
	{
		$options = $this->mergeOptions($options);
		
		try {
			$data = json_decode($code, true, 512, JSON_THROW_ON_ERROR);
			
			if ($options['sort_keys']) {
				$data = $this->sortKeysRecursive($data);
			}
			
			$flags = JSON_PRETTY_PRINT;
			
			if (!$options['ensure_ascii']) {
				$flags |= JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
			}
			
			$formatted = json_encode($data, $flags);
			
			// Adjust indentation if not using default 4 spaces
			if ($options['indent_size'] !== 4) {
				$formatted = $this->adjustIndentation($formatted, $options['indent_size']);
			}
			
			return $formatted . "\n";
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException("Invalid JSON: " . $e->getMessage());
		}
	}
	
	/**
	 * Minify JSON code
	 *
	 * @param string $code Raw JSON code
	 * @param array $options Minification options
	 * @return string Minified JSON code
	 */
	public function minify(string $code, array $options = []): string
	{
		try {
			$data = json_decode($code, true, 512, JSON_THROW_ON_ERROR);
			
			return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException("Invalid JSON: " . $e->getMessage());
		}
	}
	
	/**
	 * Sort array keys recursively
	 *
	 * @param mixed $data Data to sort
	 * @return mixed Sorted data
	 */
	protected function sortKeysRecursive($data)
	{
		if (!is_array($data)) {
			return $data;
		}
		
		// Check if it's an associative array
		if (array_keys($data) !== range(0, count($data) - 1)) {
			ksort($data);
		}
		
		foreach ($data as $key => $value) {
			$data[$key] = $this->sortKeysRecursive($value);
		}
		
		return $data;
	}
	
	/**
	 * Adjust indentation size
	 *
	 * @param string $json JSON string with default indentation
	 * @param int $size Desired indentation size
	 * @return string JSON with adjusted indentation
	 */
	protected function adjustIndentation(string $json, int $size): string
	{
		$lines = explode("\n", $json);
		$result = [];
		
		foreach ($lines as $line) {
			if (preg_match('/^(\s+)/', $line, $matches)) {
				$currentIndent = strlen($matches[1]);
				$level = $currentIndent / 4; // Default is 4 spaces
				$newIndent = str_repeat(' ', $level * $size);
				$line = $newIndent . ltrim($line);
			}
			$result[] = $line;
		}
		
		return implode("\n", $result);
	}
	
	/**
	 * Validate JSON code
	 *
	 * @param string $code JSON code to validate
	 * @return bool True if valid JSON
	 */
	public function validate(string $code): bool
	{
		if (empty($code)) {
			return false;
		}
		
		json_decode($code);
		
		return json_last_error() === JSON_ERROR_NONE;
	}
	
	/**
	 * Get supported file extensions
	 *
	 * @return array
	 */
	public function getSupportedExtensions(): array
	{
		return ['json'];
	}
}
