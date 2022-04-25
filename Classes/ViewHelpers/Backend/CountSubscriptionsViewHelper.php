<?php

namespace RKW\RkwNewsletter\ViewHelpers\Backend;

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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Topic;

/**
 * CountSubscriptionsViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CountSubscriptionsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{


    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('newsletter', \RKW\RkwNewsletter\Domain\Model\Newsletter::class, 'Count the subscribers of this newsletter.', true);
        $this->registerArgument('topic', \RKW\RkwNewsletter\Domain\Model\Topic::class, 'Count the subscribers of this topic (optional).', false, null);
    }


    /**
     * Gets the number of subscribers for the newsletter
     *
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function render(): int
    {

        $newsletter = $this->arguments['newsletter'];
        $topic = is_object($this->arguments['topic']) ? $this->arguments['topic'] : null;

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

        if ($topic) {
            return $frontendUserRepository->findSubscriptionsByTopic($topic)->count();
        }

        return $frontendUserRepository->findSubscriptionsByNewsletter($newsletter)->count();
    }
}