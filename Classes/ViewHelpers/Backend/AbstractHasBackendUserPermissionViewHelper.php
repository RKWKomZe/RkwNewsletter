<?php
namespace RKW\RkwNewsletter\ViewHelpers\Backend;

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

use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Topic;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * AbstractHasBackendUserPermissionViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractHasBackendUserPermissionViewHelper extends AbstractViewHelper
{

    /**
     * Checks permissions
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Topic|null $topic
     * @param int $approvalStage
     * @param bool $allApprovals
     * @return bool
     */
    protected static function checkPermissions(
        Issue $issue,
        bool $allApprovals = false,
        Topic $topic = null, 
        int $approvalStage = 1
    ): bool {

        /** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $beUserAuthentication */
        $beUserAuthentication = ($GLOBALS['BE_USER'] ?: null);
        $backendUserId = intval($GLOBALS['BE_USER']->user['uid']);

        if ($backendUserId) {

            if (
                ($beUserAuthentication instanceof BackendUserAuthentication)
                && ($beUserAuthentication->isAdmin())
            ){
                return true;
            }

            // first check if user is allowed by issue
            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
            foreach ($issue->getNewsletter()->getApproval() as $backendUser) {

                if ($backendUser->getUid() == $backendUserId) {
                    return true;
                }
            }

            // then check if he is allowed by approval for a specific topic and stage
            if ($topic) {

                $getter = 'getApprovalStage' . $approvalStage;

                /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
                foreach ($topic->$getter() as $backendUser) {
                    if ($backendUser->getUid() == $backendUserId) {
                        return true;
                    }
                }

            // check if any approval-permissions match!    
            } else if ($allApprovals) {

                foreach([1,2] as $stage) {

                    $getter = 'getApprovalStage' . $stage;

                    /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
                    foreach($issue->getNewsletter()->getTopic() as $topic) {

                        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
                        foreach ($topic->$getter() as $backendUser) {
                            if ($backendUser->getUid() == $backendUserId) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

}