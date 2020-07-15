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
 * IsTopicSubscribedViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsTopicSubscribedViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * checks is user has subscribed to a topic
     *
     * @param mixed $frontendUser
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return boolean
     */
    public function render($frontendUser, \RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {
        if ($frontendUser) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $userTopic */
            foreach ($frontendUser->getTxRkwnewsletterSubscription() as $userTopic) {

                if ($userTopic->getUid() == $topic->getUid()) {
                    return true;
                }
            }
        }

        return false;
    }
}