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
 * IsNewsletterSubscriptionAllowedViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsNewsletterSubscriptionAllowedViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * checks whether a user must be logged in to see a newsletter (group restriction / logged in)
     *
     * @param mixed $frontendUser
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return boolean
     */
    public function render($frontendUser, \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
    {
        // if newsletter has no restrictions
        if (!$newsletter->getUsergroup()->toArray()) {
            return true;
        }

        // check access for logged-in users
        if ($frontendUser) {

            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup */
            foreach ($frontendUser->getUsergroup()->toArray() as $userGroup) {

                /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $newsletterGroup */
                foreach ($newsletter->getUsergroup()->toArray() as $newsletterGroup) {

                    if ($userGroup->getUid() === $newsletterGroup->getUid()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}