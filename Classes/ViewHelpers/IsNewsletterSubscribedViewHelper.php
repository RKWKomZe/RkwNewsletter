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
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsNewsletterSubscribedViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @toDo: rework and write tests
 */
class IsNewsletterSubscribedViewHelper extends AbstractViewHelper
{
    /**
     * checks is user has subscribed to a topic
     *
     * @param mixed $frontendUser
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return boolean
     */
    public function render($frontendUser, \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
    {
        if ($frontendUser) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
            foreach ($frontendUser->getTxRkwnewsletterSubscription() as $topic) {

                if ($topic->getNewsletter() instanceof \RKW\RkwNewsletter\Domain\Model\Newsletter) {
                    if ($topic->getNewsletter()->getUid() == $newsletter->getUid()) {
                        return true;
                        //===
                    }
                }
            }
        }

        return false;
        //===
    }
}