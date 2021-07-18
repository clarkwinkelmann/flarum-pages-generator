<?php

namespace ClarkWinkelmann\PagesGenerator;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extend\Frontend;
use Flarum\Extend\Routes;
use Flarum\Extension\Extension;
use Flarum\Frontend\Document;
use Illuminate\Contracts\Container\Container;

class Pages implements ExtenderInterface
{
    protected $paths = [];

    public function source(string $path): self
    {
        $this->paths[] = $path;

        return $this;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        $container->singleton(PageRepository::class);

        /**
         * @var PageRepository $pages
         */
        $pages = $container->make(PageRepository::class);

        foreach ($this->paths as $path) {
            $pages->loadPath($path);
        }

        $frontend = (new Frontend('forum'))
            ->js(__DIR__ . '/../js/dist/forum.js')
            ->css(__DIR__ . '/../resources/less/forum.less')
            ->content(function (Document $document) use ($pages) {
                $document->payload['generatorPages'] = array_map(function (Page $page) {
                    return $page->path;
                }, $pages->all());
            });

        foreach ($pages->all() as $page) {
            $frontend->route($page->path, 'generated-route.' . $page->id);
        }

        $frontend->extend($container, $extension);

        (new Routes('api'))->get('/generated-route', 'generated-route', ShowPageController::class)->extend($container, $extension);
    }
}
