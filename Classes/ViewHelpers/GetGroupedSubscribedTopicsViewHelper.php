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
 * GetGroupedSubscribedTopics
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetGroupedSubscribedTopicsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * returns a grouped list of topics
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions
     * @return array
     */
    public function render(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions)
    {

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
        //===
    }
}