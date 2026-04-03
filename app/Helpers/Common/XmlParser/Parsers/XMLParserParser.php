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

class XMLParserParser implements ParserInterface
{
	private array $config;
	private array $stack = [];
	private array $result = [];
	
	public function __construct(array $config = [])
	{
		$this->config = $config;
	}
	
	public function parse(string $xml): array
	{
		$this->stack = [];
		$this->result = [];
		
		$parser = xml_parser_create();
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'startElement', 'endElement');
		xml_set_character_data_handler($parser, 'characterData');
		
		if (!xml_parse($parser, $xml, true)) {
			$error = xml_error_string(xml_get_error_code($parser));
			xml_parser_free($parser);
			throw new \RuntimeException('xml_parser parsing failed: ' . $error);
		}
		
		xml_parser_free($parser);
		
		return $this->result;
	}
	
	private function startElement($parser, string $name, array $attributes): void
	{
		$element = ['@attributes' => $attributes];
		$this->stack[] = &$element;
	}
	
	private function endElement($parser, string $name): void
	{
		$element = array_pop($this->stack);
		
		if (empty($element['@attributes'])) {
			unset($element['@attributes']);
		}
		
		if (empty($this->stack)) {
			$this->result[$name] = $element;
		} else {
			$parent = &$this->stack[count($this->stack) - 1];
			
			if (isset($parent[$name])) {
				if (!isset($parent[$name][0])) {
					$parent[$name] = [$parent[$name]];
				}
				$parent[$name][] = $element;
			} else {
				$parent[$name] = $element;
			}
		}
	}
	
	private function characterData($parser, string $data): void
	{
		$data = trim($data);
		if ($data !== '' && !empty($this->stack)) {
			$element = &$this->stack[count($this->stack) - 1];
			
			if (!isset($element['@value'])) {
				$element['@value'] = '';
			}
			$element['@value'] .= $data;
		}
	}
	
	public function getName(): string
	{
		return 'xml_parser';
	}
	
	public function isAvailable(): bool
	{
		return (extension_loaded('xml') && function_exists('xml_parser_create'));
	}
}
