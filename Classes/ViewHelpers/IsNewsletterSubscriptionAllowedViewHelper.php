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
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsNewsletterSubscriptionAllowedViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo rework and write tests
 */
class IsNewsletterSubscriptionAllowedViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('newsletter', Newsletter::class, 'Newsletter-object.', true);
        $this->registerArgument('frontendUser', FrontendUser::class, 'Frontend-user', false, null);
    }


    /**
     * Checks whether a user must be logged in to see a newsletter (group restriction / logged in)
     *
     * @return boolean
     */
    public function render(): bool
    {
        $newsletter = $this->arguments['newsletter'];
        $frontendUser = $this->arguments['frontendUser'];

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
