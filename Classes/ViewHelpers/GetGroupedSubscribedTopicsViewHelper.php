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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetGroupedSubscribedTopics
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo rework and write tests
 */
class GetGroupedSubscribedTopicsViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('subscriptions', ObjectStorage::class, 'ObjectStorage with subscriptions.', true);
    }


    /**
     * returns a grouped list of topics
     *
     * @return array
     */
    public function render(): array
    {

        $subscriptions = $this->arguments['subscriptions'];
        $groupedTopics = array();

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($subscriptions as $topic) {

            if ($topic->getNewsletter()) {

                if (!$groupedTopics[$topic->getNewsletter()->getName()]) {
                    $groupedTopics[$topic->getNewsletter()->getName()] = array();
                }
                $groupedTopics[$topic->getNewsletter()->getName()][$topic->getName()] = $topic;
            }
        }

        // now sort by topic-name
        foreach ($groupedTopics as $newsletter => &$topics) {
            ksort($topics);
        }

        return $groupedTopics;
    }
}
