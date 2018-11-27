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
 * IsTopicInListViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsTopicInListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * checks is user has subscribed to a topic
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $topicList
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return boolean
     */
    public function render(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $topicList, \RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicFromList */
        foreach ($topicList as $topicFromList) {

            if ($topicFromList->getUid() == $topic->getUid()) {
                return true;
                //===
            }
        }


        return false;
        //===
    }
}