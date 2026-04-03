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

namespace App\Helpers\Common;

use App\Helpers\Common\XmlParser\Parsers\DOMParser;
use App\Helpers\Common\XmlParser\Parsers\SimpleXMLParser;
use App\Helpers\Common\XmlParser\Parsers\XMLReaderParser;
use App\Helpers\Common\XmlParser\Parsers\XMLParserParser;
use App\Helpers\Common\XmlParser\Contracts\ParserInterface;
use App\Helpers\Common\XmlParser\Exceptions\XmlParseException;

/**
 * XML Parser with multiple fallback strategies
 *
 * Priority order:
 * 1. DOMDocument (most robust, full DOM access)
 * 2. SimpleXML (simple and efficient)
 * 3. XMLReader (memory efficient for large files)
 * 4. xml_parser (basic, widely available)
 */
class XmlParser
{
	private array $parsers = [];
	private ?ParserInterface $lastSuccessfulParser = null;
	private array $config = [];
	
	public function __construct(array $config = [])
	{
		$this->config = array_merge([
			'throw_on_error'      => false,
			'preserve_whitespace' => false,
			'validate'            => false,
		], $config);
		
		$this->registerDefaultParsers();
	}
	
	/**
	 * Register default parsers in priority order
	 */
	private function registerDefaultParsers(): void
	{
		if (extension_loaded('dom') && class_exists('DOMDocument')) {
			$this->registerParser(new DOMParser($this->config));
		}
		
		if (extension_loaded('simplexml') && function_exists('simplexml_load_string')) {
			$this->registerParser(new SimpleXMLParser($this->config));
		}
		
		if (extension_loaded('xmlreader') && class_exists('XMLReader')) {
			$this->registerParser(new XMLReaderParser($this->config));
		}
		
		if (extension_loaded('xml') && function_exists('xml_parser_create')) {
			$this->registerParser(new XMLParserParser($this->config));
		}
	}
	
	/**
	 * Register a custom parser
	 */
	public function registerParser(ParserInterface $parser): self
	{
		$this->parsers[] = $parser;
		
		return $this;
	}
	
	/**
	 * Parse XML string to array
	 */
	public function parse(string $xml): array
	{
		$lastException = null;
		
		foreach ($this->parsers as $parser) {
			try {
				$result = $parser->parse($xml);
				$this->lastSuccessfulParser = $parser;
				
				return $result;
			} catch (\Exception $e) {
				$lastException = $e;
				continue;
			}
		}
		
		if ($this->config['throw_on_error']) {
			throw new XmlParseException(
				'All XML parsers failed. Last error: ' . ($lastException ? $lastException->getMessage() : 'Unknown'),
				0,
				$lastException
			);
		}
		
		return [];
	}
	
	/**
	 * Parse XML with XPath query
	 */
	public function parseWithXPath(string $xml, string $query, array $namespaces = []): array
	{
		foreach ($this->parsers as $parser) {
			try {
				if (method_exists($parser, 'parseWithXPath')) {
					$result = $parser->parseWithXPath($xml, $query, $namespaces);
					$this->lastSuccessfulParser = $parser;
					
					return $result;
				}
			} catch (\Exception $e) {
				continue;
			}
		}
		
		return [];
	}
	
	/**
	 * Get the name of the last successful parser
	 */
	public function getLastParser(): ?string
	{
		return $this->lastSuccessfulParser?->getName();
	}
	
	/**
	 * Check if a specific parser is available
	 */
	public function hasParser(string $name): bool
	{
		foreach ($this->parsers as $parser) {
			if ($parser->getName() === $name) {
				return true;
			}
		}
		
		return false;
	}
}
