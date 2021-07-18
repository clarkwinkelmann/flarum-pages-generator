<?php

namespace ClarkWinkelmann\PagesGenerator;

use Flarum\Formatter\Formatter;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Finder\SplFileInfo;

class PageRepository
{
    protected $pages = [];

    public function loadPath(string $path)
    {
        /**
         * @var Filesystem $files
         */
        $files = resolve('files');

        /**
         * @var SplFileInfo[] $names
         */
        $names = $files->allFiles($path);

        foreach ($names as $file) {
            $folder = $file->getRelativePath();
            $filename = $file->getFilenameWithoutExtension();

            $url = ($folder ? '/' . $folder : '') . '/' . $filename;

            $object = YamlFrontMatter::parse(file_get_contents($file->getPathname()));

            $page = new Page();
            $page->id = $url;
            $page->path = $url;
            $page->title = $object->matter('title');

            if ($file->getExtension() === 'md') {
                /**
                 * @var Formatter $formatter
                 */
                $formatter = resolve(Formatter::class);

                $xml = $formatter->parse($object->body());

                $page->content = [
                    $formatter->render($xml),
                ];
            } else {
                $page->content = [
                    $object->body(),
                ];
            }

            $this->pages[$url] = $page;
        }
    }

    /**
     * @return Page[]
     */
    public function all(): array
    {
        return array_values($this->pages);
    }

    public function get(string $path): ?Page
    {
        return Arr::get($this->pages, $path);
    }
}
