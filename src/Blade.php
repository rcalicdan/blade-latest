<?php

namespace Rcalicdan\Blade;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;
use Rcalicdan\Blade\Container as BladeContainer;
use Illuminate\View\ComponentAttributeBag;

class Blade implements FactoryContract
{
    /**
     * @var Application
     */
    protected $container;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var BladeCompiler
     */
    private $compiler;

    public function __construct($viewPaths, string $cachePath, ?ContainerInterface $container = null)
    {
        $this->container = $container ?: BladeContainer::getInstance();

        $this->setupContainer((array) $viewPaths, $cachePath);
        (new ViewServiceProvider($this->container))->register();

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');

        // Register basic component directives
        $this->registerComponentDirectives();
    }

    public function render(string $view, array $data = [], array $mergeData = []): string
    {
        return $this->make($view, $data, $mergeData)->render();
    }

    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    public function compiler(): BladeCompiler
    {
        return $this->compiler;
    }

    public function directive(string $name, callable $handler)
    {
        $this->compiler->directive($name, $handler);
    }

    public function if($name, callable $callback)
    {
        $this->compiler->if($name, $callback);
    }

    public function exists($view): bool
    {
        return $this->factory->exists($view);
    }

    public function file($path, $data = [], $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    public function share($key, $value = null)
    {
        return $this->factory->share($key, $value);
    }

    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    public function creator($views, $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    public function addNamespace($namespace, $hints): self
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Register a class-based component.
     *
     * @param  string  $class
     * @param  string|null  $alias
     * @param  string  $prefix
     * @return $this
     */
    public function component($class, $alias = null, $prefix = '')
    {
        $this->compiler->component($class, $alias, $prefix);

        return $this;
    }

    /**
     * Register an anonymous component path.
     *
     * @param  string  $path
     * @param  string|null  $prefix
     * @return $this
     */
    public function anonymousComponentPath($path, $prefix = null)
    {
        $this->compiler->anonymousComponentPath($path, $prefix);

        return $this;
    }

    /**
     * Set the component path for component resolution.
     *
     * @param  string  $path
     * @return $this
     */
    public function withComponentPath(string $path)
    {
        $this->container->instance('view.component_path', $path);

        return $this;
    }

    public function __call(string $method, array $params)
    {
        return call_user_func_array([$this->factory, $method], $params);
    }

    protected function setupContainer(array $viewPaths, string $cachePath)
    {
        $this->container->bindIf('files', fn() => new Filesystem);
        $this->container->bindIf('events', fn() => new Dispatcher);
        $this->container->bindIf('config', fn() => new Repository([
            'view.paths' => $viewPaths,
            'view.compiled' => $cachePath,
            'view.component_path' => null,
        ]));

        // Register ComponentAttributeBag for component attributes
        $this->container->bind(ComponentAttributeBag::class, function () {
            return new ComponentAttributeBag;
        });

        $this->container->bindIf('blade.compiler', function ($app) use ($cachePath) {
            return new BladeCompiler($app['files'], $cachePath);
        });

        Container::setInstance($this->container);
        Facade::setFacadeApplication($this->container);
    }

    /**
     * Register basic component-related directives.
     *
     * @return void
     */
    protected function registerComponentDirectives()
    {
        // @props directive
        $this->directive('props', function ($expression) {
            return "<?php \$attributes = \$attributes->merge($expression); ?>";
        });

        // @aware directive
        $this->directive('aware', function ($expression) {
            return "<?php foreach({$expression} as \$__key => \$__value) { \$__aware[\$__key] = \$__value; } ?>";
        });

        // @slot directive
        $this->directive('slot', function ($expression) {
            return "<?php \$__env->slot{$expression}; ?>";
        });

        // @endslot directive
        $this->directive('endslot', function () {
            return '<?php $__env->endSlot(); ?>';
        });

        // @component directive
        $this->directive('component', function ($expression) {
            return "<?php \$__env->startComponent{$expression}; ?>";
        });

        // @endcomponent directive
        $this->directive('endcomponent', function () {
            return '<?php echo $__env->renderComponent(); ?>';
        });
    }
}
