<?php

namespace ClarkWinkelmann\PagesGenerator;

use Flarum\Api\Controller\AbstractShowController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowPageController extends AbstractShowController
{
    public $serializer = PageSerializer::class;

    protected $pages;

    public function __construct(PageRepository $pages)
    {
        $this->pages = $pages;
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $path = (string)Arr::get($request->getQueryParams(), 'path');

        $page = $this->pages->get($path);

        if (!$page) {
            throw new ModelNotFoundException();
        }

        return $page;
    }
}
