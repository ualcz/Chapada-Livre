<?php

namespace Larapen\CodeFormatter\Facades;

use Illuminate\Support\Facades\Facade;

class CodeFormatter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'code.formatter';
    }
}
