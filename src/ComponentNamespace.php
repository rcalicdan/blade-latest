<?php

namespace Rcalicdan\Blade;

use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

class ComponentNamespace
{
    /**
     * The component path.
     *
     * @var string|null
     */
    protected $path;
    
    /**
     * The blade compiler instance.
     *
     * @var \Illuminate\View\Compilers\BladeCompiler
     */
    protected $compiler;
    
    /**
     * Create a new component namespace instance.
     *
     * @param  string|null  $path
     * @param  \Illuminate\View\Compilers\BladeCompiler  $compiler
     * @return void
     */
    public function __construct($path, BladeCompiler $compiler)
    {
        $this->path = $path;
        $this->compiler = $compiler;
    }
    
    /**
     * Resolve a class name from the component.
     *
     * @param  string  $component
     * @return string
     */
    public function resolveClassNameForComponent($component)
    {
        if (is_null($this->path)) {
            return $component;
        }
        
        $componentName = Str::studly(implode('_', explode('-', $component)));
        $namespace = $this->path;
        
        if (Str::contains($component, '.')) {
            $parts = explode('.', $component);
            $componentName = Str::studly(implode('_', explode('-', array_pop($parts))));
            $namespace .= '\\' . implode('\\', array_map(function ($part) {
                return Str::studly($part);
            }, $parts));
        }
        
        return $namespace . '\\' . $componentName;
    }
}