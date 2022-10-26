<?php

declare(strict_types=1);
namespace B13\Menus\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to render a tree-based menu of pages and subpages.
 */
abstract class AbstractMenu implements DataProcessorInterface
{
    protected ContentDataProcessor $contentDataProcessor;

    public function __construct(ContentDataProcessor $contentDataProcessor)
    {
        $this->contentDataProcessor = $contentDataProcessor;
    }

    protected function processAdditionalDataProcessors(array &$page, array $processorConfiguration): array
    {
        if (isset($page['subpages']) && is_array($page['subpages'])) {
            foreach ($page['subpages'] as &$item) {
                $this->processAdditionalDataProcessors($item, $processorConfiguration);
            }
        }

        /** @var ContentObjectRenderer $recordContentObjectRenderer */
        $recordContentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $recordContentObjectRenderer->start($page, 'pages');
        $page = $this->contentDataProcessor->process($recordContentObjectRenderer, $processorConfiguration, $page);
        return $page;
    }
}
