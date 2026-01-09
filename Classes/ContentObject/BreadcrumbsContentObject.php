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

use B13\Menus\Domain\Repository\MenuRepository;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * Build a breadcrumbs navigation, no caching involved.
 */
class BreadcrumbsContentObject extends AbstractContentObject
{
    protected MenuRepository $menuRepository;

    public function __construct(ContentObjectRenderer $cObj)
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            parent::__construct($cObj);
        }
        $this->menuRepository = (GeneralUtility::makeInstance(ContentObjectServiceContainer::class))->getMenuRepository();
    }

    /**
     * @param array $conf
     * @return string
     */
    public function render($conf = [])
    {
        $rootLine = $this->getRootline();
        $pages = $this->menuRepository->getBreadcrumbsMenu($rootLine, $conf);
        $content = '';
        $cObjForItems = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $rootLevelCount = count($pages);
        foreach ($pages as $page) {
            PageStateMarker::markStates($page, $rootLevelCount--);
            $cObjForItems->start($page, 'pages');
            $content .= $cObjForItems->cObjGetSingle($conf['renderObj'] ?? '', $conf['renderObj.'] ?? []);
        }
        return $this->cObj->stdWrap($content, $conf);
    }

    protected function getRootline(): array
    {
        if ((new Typo3Version())->getMajorVersion() < 13) {
            return $GLOBALS['TSFE']->rootLine;
        }
        $request = $this->cObj->getRequest();
        /** @var PageInformation $pageInformation */
        $pageInformation = $request->getAttribute('frontend.page.information');
        return $pageInformation->getRootLine();
    }
}
