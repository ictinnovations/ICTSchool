<?php

namespace LaravelLux\Html;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

class HtmlServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Supported Blade Directives
     *
     * @var array
     */
    protected array $directives = [
        'entities',
        'decode',
        'script',
        'style',
        'image',
        'favicon',
        'link',
        'secureLink',
        'linkAsset',
        'linkSecureAsset',
        'linkRoute',
        'linkAction',
        'mailto',
        'email',
        'ol',
        'ul',
        'dl',
        'meta',
        'tag',
        'open',
        'model',
        'close',
        'token',
        'label',
        'input',
        'text',
        'password',
        'hidden',
        'email',
        'tel',
        'number',
        'date',
        'datetime',
        'datetimeLocal',
        'time',
        'url',
        'file',
        'textarea',
        'select',
        'selectRange',
        'selectYear',
        'selectMonth',
        'getSelectOption',
        'checkbox',
        'radio',
        'reset',
        'image',
        'color',
        'submit',
        'button',
        'old'
    ];

    public function boot(): void
    {
        $this->registerPublishables();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/html-forms.php', 'html-forms');

        $this->registerHtmlBuilder();

        $this->registerFormBuilder();

        $this->app->alias('html', HtmlBuilder::class);
        $this->app->alias('form', FormBuilder::class);

        $this->registerBladeDirectives();
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder(): void
    {
        $this->app->singleton('html', function ($app) {
            return new HtmlBuilder($app['url'], $app['view']);
        });
    }

    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder(): void
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token(), $app['request']);

            return $form->setSessionStore($app['session.store']);
        });
    }

    /**
     * Register Blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $namespaces = [
                'Html' => get_class_methods(HtmlBuilder::class),
                'Form' => get_class_methods(FormBuilder::class),
            ];

            foreach ($namespaces as $namespace => $methods) {
                foreach ($methods as $method) {
                    if (in_array($method, $this->directives)) {
                        $snakeMethod = Str::snake($method);
                        $directive = strtolower($namespace).'_'.$snakeMethod;

                        $bladeCompiler->directive($directive, function ($expression) use ($namespace, $method) {
                            return "<?php echo $namespace::$method($expression); ?>";
                        });
                    }
                }
            }
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['html', 'form', HtmlBuilder::class, FormBuilder::class];
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/html-forms.php' => config_path('html-forms.php'),
        ], 'config');
    }
}
