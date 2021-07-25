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

    public function loadPath(string $path, array $components)
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

                $page->content = $this->parseComponents($formatter->render($xml), $components);
            } else {
                $page->content = $this->parseComponents($object->body(), $components);
            }

            $this->pages[$url] = $page;
        }
    }

    protected function parseComponents(string $html, array $components): array
    {
        $content = [];

        foreach (explode("\n", $html) as $line) {
            $mithril = $this->parseLine($line, $components);

            if ($mithril) {
                $content[] = $mithril;

                continue;
            }

            if (count($content) && $content[count($content) - 1]['type'] === 'html') {
                $content[count($content) - 1]['body'] .= "\n" . $line;
            } else {
                $content[] = [
                    'type' => 'html',
                    'body' => $line,
                ];
            }
        }

        return $content;
    }

    protected function parseLine(string $line, array $components): ?array
    {
        $line = str_replace([
            '&lt;',
            '&gt;',
        ], [
            '<',
            '>',
        ], $line);

        // Accept optional <p></p> around. This happens when inside markdown
        if (preg_match('~^\s*(?:<p>)?\s*<([a-zA-Z0-9_-]+)\s*(?:([a-zA-Z0-9]+)="([^"]+)")?\s*/?>\s*(?:</([a-zA-Z0-9_-]+)>)?\s*(?:</p>)?\s*$~', $line, $matches) !== 1) {
            return null;
        }

        $import = Arr::get($components, $matches[1]);

        // If it's not a registered Mithril component, skip
        if (!$import) {
            return null;
        }

        // If closing tag is different from opening tag, skip
        if ($matches[4] && $matches[4] !== $matches[1]) {
            return null;
        }

        return [
            'type' => 'mithril',
            'component' => $import,
            'attrs' => $matches[2] ? [
                $matches[2] => $matches[3],
            ] : [],
        ];
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
