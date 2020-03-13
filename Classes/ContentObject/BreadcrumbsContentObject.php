<?php
declare(strict_types = 1);
namespace B13\Menus\ContentObject;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Compiler\BreadcrumbsCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Build a breadcrumbs navigation, no caching involved.
 */
class BreadcrumbsContentObject extends AbstractContentObject
{

    /**
     * @param array $conf
     * @return string|void
     */
    public function render($conf = [])
    {
        $pages = GeneralUtility::makeInstance(BreadcrumbsCompiler::class)->compile($this->cObj, $conf);
        $content = '';
        $cObjForItems = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $rootLevelCount = count($pages);
        foreach ($pages as $page) {
            PageStateMarker::markStates($page, $rootLevelCount--);
            $cObjForItems->start($page, 'pages');
            $content .= $cObjForItems->cObjGetSingle($conf['renderObj'], $conf['renderObj.']);
        }
        return $this->cObj->stdWrap($content, $conf);
    }
}
