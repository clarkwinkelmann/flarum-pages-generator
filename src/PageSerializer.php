<?php

namespace ClarkWinkelmann\PagesGenerator;

use Flarum\Api\Serializer\AbstractSerializer;

class PageSerializer extends AbstractSerializer
{
    protected $type = 'generator-page';

    /**
     * @param Page $page
     * @return array
     */
    protected function getDefaultAttributes($page): array
    {
        return [
            'path' => $page->path,
            'title' => $page->title,
            'content' => $page->content,
        ];
    }
}
