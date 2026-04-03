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

class DOMParser implements ParserInterface
{
	private array $config;
	
	public function __construct(array $config = [])
	{
		$this->config = $config;
	}
	
	public function parse(string $xml): array
	{
		libxml_use_internal_errors(true);
		
		$dom = new \DOMDocument();
		$dom->preserveWhiteSpace = $this->config['preserve_whitespace'] ?? false;
		
		if (!$dom->loadXML($xml)) {
			$errors = libxml_get_errors();
			libxml_clear_errors();
			throw new \RuntimeException('DOM parsing failed: ' . ($errors[0]->message ?? 'Unknown error'));
		}
		
		libxml_clear_errors();
		
		return $this->domNodeToArray($dom->documentElement);
	}
	
	public function parseWithXPath(string $xml, string $query, array $namespaces = []): array
	{
		libxml_use_internal_errors(true);
		
		$dom = new \DOMDocument();
		$dom->loadXML($xml);
		
		$xpath = new \DOMXPath($dom);
		
		foreach ($namespaces as $prefix => $uri) {
			$xpath->registerNamespace($prefix, $uri);
		}
		
		$nodes = $xpath->query($query);
		libxml_clear_errors();
		
		if (!$nodes || $nodes->length === 0) {
			return [];
		}
		
		$result = [];
		foreach ($nodes as $node) {
			$result[] = $this->domNodeToArray($node);
		}
		
		return $result;
	}
	
	private function domNodeToArray(\DOMNode $node): array
	{
		$result = [];
		
		// Add attributes
		if ($node->hasAttributes()) {
			$result['@attributes'] = [];
			foreach ($node->attributes as $attr) {
				$result['@attributes'][$attr->nodeName] = $attr->nodeValue;
			}
		}
		
		// Add child nodes
		if ($node->hasChildNodes()) {
			$children = [];
			foreach ($node->childNodes as $child) {
				if ($child->nodeType === XML_TEXT_NODE) {
					$value = trim($child->nodeValue);
					if ($value !== '') {
						$result['@value'] = $value;
					}
				} elseif ($child->nodeType === XML_ELEMENT_NODE) {
					$childArray = $this->domNodeToArray($child);
					
					if (isset($children[$child->nodeName])) {
						if (!isset($children[$child->nodeName][0])) {
							$children[$child->nodeName] = [$children[$child->nodeName]];
						}
						$children[$child->nodeName][] = $childArray;
					} else {
						$children[$child->nodeName] = $childArray;
					}
				}
			}
			$result = array_merge($result, $children);
		}
		
		return $result;
	}
	
	public function getName(): string
	{
		return 'DOMDocument';
	}
	
	public function isAvailable(): bool
	{
		return (extension_loaded('dom') && class_exists('DOMDocument'));
	}
}
