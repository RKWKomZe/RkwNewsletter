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
 * GetNewsletterTopicsWithSubscriptionsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetNewsletterTopicsWithSubscriptionsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Gets all topics of the issue with information about the subscriptions
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \Traversable $pages
     * @return array
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $pages)
    {

        $finalTopics = array();

        // at first we add the topics the user has already subscribed
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach ($pages as $page) {
            if ($page->getTxRkwnewsletterTopic()) {
                $finalTopics[$page->getTxRkwnewsletterTopic()->getUid()] = array(
                    'subscribed' => true,
                    'topic'      => $page->getTxRkwnewsletterTopic(),
                );
            }
        }

        // now we add everything else!
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($issue->getNewsletter()->getTopic() as $topic) {

            if (
                (!isset($finalTopics[$topic->getUid()]))
                && (!$topic->getIsSpecial())
            ) {
                $finalTopics[$topic->getUid()] = array(
                    'subscribed' => false,
                    'topic'      => $topic,
                );
            }
        }

        return $finalTopics;
    }
}