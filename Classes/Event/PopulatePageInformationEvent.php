<?php

declare(strict_types=1);

namespace B13\Menus\Event;

class PopulatePageInformationEvent
{
    protected $page;

    public function __construct(array $page)
    {
        $this->page = $page;
    }

    public function getPage(): array
    {
        return $this->page;
    }

    public function setPage(array $page): void
    {
        $this->page = $page;
    }
}
