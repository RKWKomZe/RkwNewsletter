<?php

namespace RKW\RkwNewsletter\ViewHelpers;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * GetNewsletterContentsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetNewsletterContentsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper implements CompilableInterface
{

    /**
     * Gets all contents of the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param int limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\Pages $page, $limit = 0)
    {
        return static::renderStatic(
            array(
                'issue' => $issue,
                'page'  => $page,
                'limit' => $limit,
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
        //===
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $issue = $arguments['issue'];
        $page = $arguments['page'];
        $limit = $arguments['limit'];

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \RKW\RkwNewsletter\Domain\Repository\TtContentRepository $ttContentRepository */
        $ttContentRepository = $objectManager->get('RKW\\RkwNewsletter\\Domain\\Repository\\TtContentRepository');

        // get language of newsletter
        $language = $issue->getNewsletter()->getSysLanguageUid();

        return $ttContentRepository->findAllByPidAndLanguageUid($page->getUid(), $language, $limit);
        //===
    }

}