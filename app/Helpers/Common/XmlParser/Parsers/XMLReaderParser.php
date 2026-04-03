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

class XMLReaderParser implements ParserInterface
{
	private array $config;
	
	public function __construct(array $config = [])
	{
		$this->config = $config;
	}
	
	public function parse(string $xml): array
	{
		$reader = new \XMLReader();
		
		if (!$reader->XML($xml)) {
			throw new \RuntimeException('XMLReader parsing failed');
		}
		
		$result = $this->parseNode($reader);
		$reader->close();
		
		return $result;
	}
	
	private function parseNode(\XMLReader $reader): array
	{
		$tree = [];
		
		while ($reader->read()) {
			if ($reader->nodeType === \XMLReader::ELEMENT) {
				$name = $reader->name;
				$node = ['@attributes' => []];
				
				if ($reader->hasAttributes) {
					while ($reader->moveToNextAttribute()) {
						$node['@attributes'][$reader->name] = $reader->value;
					}
					$reader->moveToElement();
				}
				
				if (!$reader->isEmptyElement) {
					$innerXml = $reader->readInnerXml();
					if (trim($innerXml) !== '') {
						$node['@value'] = $innerXml;
					}
				}
				
				if (empty($node['@attributes'])) {
					unset($node['@attributes']);
				}
				
				if (isset($tree[$name])) {
					if (!isset($tree[$name][0])) {
						$tree[$name] = [$tree[$name]];
					}
					$tree[$name][] = $node;
				} else {
					$tree[$name] = $node;
				}
			}
		}
		
		return $tree;
	}
	
	public function getName(): string
	{
		return 'XMLReader';
	}
	
	public function isAvailable(): bool
	{
		return (extension_loaded('xmlreader') && class_exists('XMLReader'));
	}
}
