<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Helpers\Common\XmlParser\Parsers;

use App\Helpers\Common\XmlParser\Contracts\ParserInterface;

class SimpleXMLParser implements ParserInterface
{
	private array $config;
	
	public function __construct(array $config = [])
	{
		$this->config = $config;
	}
	
	public function parse(string $xml): array
	{
		libxml_use_internal_errors(true);
		
		$element = simplexml_load_string($xml);
		
		if ($element === false) {
			$errors = libxml_get_errors();
			libxml_clear_errors();
			throw new \RuntimeException('SimpleXML parsing failed: ' . ($errors[0]->message ?? 'Unknown error'));
		}
		
		libxml_clear_errors();
		
		return $this->simpleXMLToArray($element);
	}
	
	public function parseWithXPath(string $xml, string $query, array $namespaces = []): array
	{
		libxml_use_internal_errors(true);
		
		$element = simplexml_load_string($xml);
		
		if ($element === false) {
			libxml_clear_errors();
			throw new \RuntimeException('SimpleXML parsing failed');
		}
		
		foreach ($namespaces as $prefix => $uri) {
			$element->registerXPathNamespace($prefix, $uri);
		}
		
		$nodes = $element->xpath($query);
		libxml_clear_errors();
		
		if (empty($nodes)) {
			return [];
		}
		
		$result = [];
		foreach ($nodes as $node) {
			$result[] = $this->simpleXMLToArray($node);
		}
		
		return $result;
	}
	
	private function simpleXMLToArray(\SimpleXMLElement $element): array
	{
		$result = [];
		
		// Add attributes
		$attributes = $element->attributes();
		if ($attributes && count($attributes) > 0) {
			$result['@attributes'] = [];
			foreach ($attributes as $key => $value) {
				$result['@attributes'][$key] = (string) $value;
			}
		}
		
		// Add namespaced attributes
		foreach ($element->getNamespaces(true) as $prefix => $namespace) {
			$attributes = $element->attributes($namespace);
			if ($attributes && count($attributes) > 0) {
				if (!isset($result['@attributes'])) {
					$result['@attributes'] = [];
				}
				foreach ($attributes as $key => $value) {
					$attrName = $prefix ? "{$prefix}:{$key}" : $key;
					$result['@attributes'][$attrName] = (string) $value;
				}
			}
		}
		
		// Add children
		$children = [];
		foreach ($element->children() as $child) {
			$name = $child->getName();
			$childArray = $this->simpleXMLToArray($child);
			
			if (isset($children[$name])) {
				if (!isset($children[$name][0])) {
					$children[$name] = [$children[$name]];
				}
				$children[$name][] = $childArray;
			} else {
				$children[$name] = $childArray;
			}
		}
		
		// Add text value
		$text = trim((string) $element);
		if ($text !== '' && empty($children)) {
			$result['@value'] = $text;
		} elseif (!empty($children)) {
			$result = array_merge($result, $children);
		} elseif ($text !== '') {
			$result['@value'] = $text;
		}
		
		return $result;
	}
	
	public function getName(): string
	{
		return 'SimpleXML';
	}
	
	public function isAvailable(): bool
	{
		return (extension_loaded('simplexml') && function_exists('simplexml_load_string'));
	}
}
