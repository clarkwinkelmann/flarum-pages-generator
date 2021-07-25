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
    protected $components = [];

    public function source(string $path): self
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * @param string $tag The HTML tag that should be converted to a Mithril component
     * @param string $import A javascript expression to access the component class (will be run with eval)
     * @return $this
     */
    public function mithrilComponent(string $tag, string $import): self
    {
        $this->components[$tag] = $import;

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
            $pages->loadPath($path, $this->components);
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
            $frontend->route($page->path, 'generated-route.' . $page->id, function (Document $document) use ($page) {
                $document->title = $page->title;
                $document->content = implode("\n\n", array_map(function (array $content): string {
                    if ($content['type'] === 'html') {
                        return $content['body'];
                    }

                    // We are not rendering anything for Mithril blocks in no-js
                    return '';
                }, $page->content));
            });
        }

        $frontend->extend($container, $extension);

        (new Routes('api'))->get('/generated-route', 'generated-route', ShowPageController::class)->extend($container, $extension);
    }
}
