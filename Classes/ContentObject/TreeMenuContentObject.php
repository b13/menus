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

use B13\Menus\Compiler\TreeMenuCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Build a sitemap-like menu
 */
class TreeMenuContentObject extends AbstractContentObject
{
    protected TreeMenuCompiler $treeMenuCompiler;

    public function __construct(ContentObjectRenderer $cObj)
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            parent::__construct($cObj);
        } else {
            $this->setContentObjectRenderer($cObj);
        }
        $this->treeMenuCompiler = (GeneralUtility::makeInstance(ContentObjectServiceContainer::class))->getTreeMenuCompiler();
    }

    /**
     * @param array $conf
     * @return string
     */
    public function render($conf = [])
    {
        $tree = $this->treeMenuCompiler->compile($this->cObj, $conf);
        $content = $this->renderItems($tree, 0, $conf['renderObj.'] ?? []);
        return $this->cObj->stdWrap($content, $conf);
    }

    protected function renderItems(array $pages, int $level, array $renderConfiguration): string
    {
        // No definition for this level
        if (empty($renderConfiguration['level' . $level])) {
            return '';
        }
        $content = '';
        $cObjForItems = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        foreach ($pages as $page) {
            PageStateMarker::markStates($page, $level);
            if ($page['hasSubpages']) {
                $page['subpageContent'] = $this->renderItems($page['subpages'], $level++, $renderConfiguration);
            }
            $cObjForItems->start($page, 'pages');
            $content .= $cObjForItems->cObjGetSingle($renderConfiguration['level' . $level] ?? '', $renderConfiguration['level' . $level . '.'] ?? []);
        }
        return $content;
    }
}
