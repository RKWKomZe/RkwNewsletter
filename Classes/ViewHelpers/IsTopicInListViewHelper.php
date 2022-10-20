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

use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsTopicInListViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo rework and write tests
 */
class IsTopicInListViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('topicList', ObjectStorage::class, 'ObjectStorage with topics.', true);
        $this->registerArgument('topic', Topic::class, 'Topic to check for', true);
    }


    /**
     * checks is user has subscribed to a topic
     *
     * @return boolean
     */
    public function render(): bool
    {
        $topicList = $this->arguments['topicList'];
        $topic = $this->arguments['topic'];

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicFromList */
        foreach ($topicList as $topicFromList) {

            if ($topicFromList->getUid() == $topic->getUid()) {
                return true;
                //===
            }
        }

        return false;
    }
}
