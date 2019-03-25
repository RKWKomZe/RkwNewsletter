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
 * CheckBackendUserIsTopicOwnerViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsBackendUserAllowedApprovalViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * checks if backendUser can approve at a given stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @param int $backendUserId
     * @param int $stage
     * @return bool
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Approval $approval, $backendUserId, $stage)
    {

        // 1. Check by stages
        $getter = 'getApprovalStage' . intval($stage);

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        foreach ($approval->getTopic()->$getter() as $backendUser) {
            if ($backendUser->getUid() == $backendUserId) {
                return true;
                //===
            }
        }

        // 2. Final admin of newsletter is allowed to approve everything
        if (count($approval->getTopic()->$getter()) > 0) {

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
            foreach ($approval->getIssue()->getNewsletter()->getApproval() as $backendUser) {

                if ($backendUser->getUid() == $backendUserId) {
                    return true;
                    //===
                }
            }
        }

        return false;
        //===
    }
}