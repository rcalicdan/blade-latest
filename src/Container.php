<?php

namespace Rcalicdan\Blade;

use Closure;
use Illuminate\Container\Container as BaseContainer;

class Container extends BaseContainer
{
    protected array $terminatingCallbacks = [];

    public function terminating(Closure $callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }

    public function terminate()
    {
        foreach ($this->terminatingCallbacks as $terminatingCallback) {
            $terminatingCallback();
        }
    }
    
    /**
     * Get the globally available instance of the container.
     * 
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }
}