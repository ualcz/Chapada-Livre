<?php

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel\Buttons;

class Button
{
	public string $stack;
	public string $name;
	public string $type = 'view';
	public mixed $content;
	
	public function __construct($stack, $name, $type, $content)
	{
		$this->stack = $stack;
		$this->name = $name;
		$this->type = $type;
		$this->content = $content;
	}
}
