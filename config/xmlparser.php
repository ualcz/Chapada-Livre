<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Throw on Error
	|--------------------------------------------------------------------------
	|
	| When enabled, the parser will throw an exception if all parsers fail.
	| When disabled, it will return an empty array instead.
	|
	*/
	'throw_on_error' => env('XML_PARSER_THROW_ON_ERROR', false),
	
	/*
	|--------------------------------------------------------------------------
	| Preserve Whitespace
	|--------------------------------------------------------------------------
	|
	| Whether to preserve whitespace in XML documents during parsing.
	|
	*/
	'preserve_whitespace' => env('XML_PARSER_PRESERVE_WHITESPACE', false),
	
	/*
	|--------------------------------------------------------------------------
	| Validate XML
	|--------------------------------------------------------------------------
	|
	| Whether to validate XML against DTD/Schema during parsing.
	| Warning: This may impact performance.
	|
	*/
	'validate' => env('XML_PARSER_VALIDATE', false),
];
