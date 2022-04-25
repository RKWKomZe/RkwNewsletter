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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * HasBackendUserPermissionViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class HasBackendUserPermissionViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('issue', \RKW\RkwNewsletter\Domain\Model\Issue::class, 'Check permissions for this issue.', true);
        $this->registerArgument('topic', \RKW\RkwNewsletter\Domain\Model\Topic::class, 'Check permissions for this topic (optional).', false, null);
        $this->registerArgument('approvalStage', 'int', 'Approval level to check. (optional, default:1)', false, null);
    }


    /**
     * checks if backendUser can approve/release at a given level
     *
     * @return int
     */
    public function render(): int
    {
        $issue = $this->arguments['issue'];
        $topic = is_object($this->arguments['topic']) ? $this->arguments['topic'] : null;
        $backendUserId = intval($GLOBALS['BE_USER']->user['uid']);
        $approvalStage = intval($this->arguments['approvalStage']) ?: 1;
        
        /** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $beUserAuthentication */
        $beUserAuthentication = ($GLOBALS['BE_USER'] ?: null);

        if ($backendUserId) {

            if (
                ($beUserAuthentication instanceof BackendUserAuthentication)
                && ($beUserAuthentication->isAdmin())
            ){
                return 1;
            }
            
            // first check if user is allowed by issue
            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
            foreach ($issue->getNewsletter()->getApproval() as $backendUser) {

                if ($backendUser->getUid() == $backendUserId) {
                    return 1;
                }
            }

            // then check if he is allowed by approval
            if ($topic) {

                $getter = 'getApprovalStage' . $approvalStage;

                /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
                foreach ($topic->$getter() as $backendUser) {
                    if ($backendUser->getUid() == $backendUserId) {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }

}