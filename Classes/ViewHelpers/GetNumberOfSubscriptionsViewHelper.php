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

/**
 * GetNumberOfSubscriptionsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetNumberOfSubscriptionsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Gets the number of subscribers for the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\Topic $topic = null)
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get('RKW\\RkwNewsletter\\Domain\\Repository\\FrontendUserRepository');

        if ($issue->getNewsletter()->getType() == 1) {
            return $frontendUserRepository->findAllSubscribers()->count();
        }

        return $frontendUserRepository->findAllSubscribersByIssue($issue, $topic)->count();
    }
}