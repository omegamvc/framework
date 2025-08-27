<?php

declare(strict_types=1);

namespace Omega\View;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Response;
use Omega\Support\Vite;
use function array_merge;

class ViewServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws DependencyException
     * @throws NotFoundException|
     * @throws InvalidDefinitionException
     */
    public function boot(): void
    {
        $this->registerViteResolver();
        $this->registerViewResolver();
    }

    protected function registerViteResolver(): void
    {
        $this->app->set('vite.gets', fn (): Vite => new Vite(get_path('path.public'), '/build/'));
        $this->app->set('vite.location', fn (): string => get_path('path.public') . '/build/manifest.json');
        $this->app->set('vite.hasManifest', fn (): bool => file_exists($this->app->get('vite.location')));
    }

    /**
     * @throws NotFoundException
     * @throws DependencyException|
     * @throws InvalidDefinitionException
     */
    protected function registerViewResolver(): void
    {
        $globalTemplateVar = [
            'vite_has_manifest' => $this->app->get('vite.hasManifest'),
            'vite_hmr_script'   => $this->app->get('vite.gets')->isRunningHRM() ? $this->app->get('vite.gets')->getHmrScript() : '',
        ];
        $extensions = $this->app->get('config')['VIEW_EXTENSIONS'] ?? [];

        $this->app->set(TemplatorFinder::class, fn () => new TemplatorFinder(get_path('paths.view'), $extensions));
        $this->app->set('view.instance', fn () => new Templator($this->app->get(TemplatorFinder::class), get_path('path.compiled_view_path')));
        $this->app->set(
            'view.response',
            fn () => fn (string $view, array $data = []): Response => new Response(
                $this->app->get('view.instance')->render($view, array_merge($data, $globalTemplateVar))
            )
        );
    }
}
