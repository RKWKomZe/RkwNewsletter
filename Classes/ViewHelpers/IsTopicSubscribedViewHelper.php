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
 * IsTopicSubscribedViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @toDo: rework and write tests
 */
class IsTopicSubscribedViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('topic', Topic::class, 'Topic to check for', true);
        $this->registerArgument('frontendUser', FrontendUser::class, 'Frontend-user', false, null);
    }

    /**
     * checks is user has subscribed to a topic
     *
     * @return boolean
     */
    public function render()
    {
        $frontendUser = $this->arguments['frontendUser'];
        $topic = $this->arguments['topic'];
        
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