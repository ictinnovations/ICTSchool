<?php

namespace LaravelLux\Html;

use Illuminate\Support\Facades\Facade;

/**
 * @see FormBuilder
 */
class FormFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'form';
    }
}
