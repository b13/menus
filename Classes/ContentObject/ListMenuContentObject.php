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

use B13\Menus\Compiler\ListMenuCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Build a menu out of a fixed list of items
 */
class ListMenuContentObject extends AbstractContentObject
{
    protected ListMenuCompiler $listMenuCompiler;

    public function __construct(ContentObjectRenderer $cObj)
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            parent::__construct($cObj);
        } else {
            $this->setContentObjectRenderer($cObj);
        }
        $this->listMenuCompiler = (GeneralUtility::makeInstance(ContentObjectServiceContainer::class))->getListMenuCompiler();
    }

    /**
     * @param array $conf
     * @return string
     */
    public function render($conf = [])
    {
        $pages = $this->listMenuCompiler->compile($this->cObj, $conf);
        $content = $this->renderItems($pages, $conf);
        return $this->cObj->stdWrap($content, $conf);
    }

    protected function renderItems(array $pages, array $conf): string
    {
        $content = '';
        $cObjForItems = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        foreach ($pages as $page) {
            PageStateMarker::markStates($page);
            $cObjForItems->start($page, 'pages');
            $content .= $cObjForItems->cObjGetSingle($conf['renderObj'] ?? '', $conf['renderObj.'] ?? []);
        }
        return $content;
    }
}
