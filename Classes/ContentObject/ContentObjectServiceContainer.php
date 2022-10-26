<?php

declare(strict_types=1);

namespace B13\Menus\ContentObject;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Compiler\LanguageMenuCompiler;
use B13\Menus\Compiler\ListMenuCompiler;
use B13\Menus\Compiler\TreeMenuCompiler;
use B13\Menus\Domain\Repository\MenuRepository;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Public Service, holds private services for ContentObjects
 */
class ContentObjectServiceContainer implements SingletonInterface
{
    protected LanguageMenuCompiler $languageMenuCompiler;
    protected MenuRepository $menuRepository;
    protected ListMenuCompiler $listMenuCompiler;
    protected TreeMenuCompiler $treeMenuCompiler;

    public function __construct(
        LanguageMenuCompiler $languageMenuCompiler,
        MenuRepository $menuRepository,
        ListMenuCompiler $listMenuCompiler,
        TreeMenuCompiler $treeMenuCompiler
    ) {
        $this->languageMenuCompiler = $languageMenuCompiler;
        $this->menuRepository = $menuRepository;
        $this->listMenuCompiler = $listMenuCompiler;
        $this->treeMenuCompiler = $treeMenuCompiler;
    }

    public function getLanguageMenuCompiler(): LanguageMenuCompiler
    {
        return $this->languageMenuCompiler;
    }

    public function getMenuRepository(): MenuRepository
    {
        return $this->menuRepository;
    }

    public function getListMenuCompiler(): ListMenuCompiler
    {
        return $this->listMenuCompiler;
    }

    public function getTreeMenuCompiler(): TreeMenuCompiler
    {
        return $this->treeMenuCompiler;
    }
}
