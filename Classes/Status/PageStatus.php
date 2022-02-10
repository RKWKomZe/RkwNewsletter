<?php
namespace RKW\RkwNewsletter\Status;

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

use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\Issue;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
 * PageStatus
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageStatus
{

    /**
     * @var string
     */
    const DRAFT = 'draft';

    /**
     * @var string
     */
    const APPROVAL_1 = 'stage1';

    /**
     * @var string
     */
    const APPROVAL_2 = 'stage2';

    /**
     * @var string
     */
    const RELEASE = 'release';

    /**
     * @var string
     */
    const SENDING = 'sent'; // no mistake!
    
    /**
     * @var int
     */
    const DONE = 'sent';
    
    
    /**
     * Returns current stage of the page based on the status of the issue and its approvals
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return string
     */
    public static function getStage (Approval $approval): string
    {
        $issueStage = IssueStatus::getStage($approval->getIssue());
        if ($issueStage == IssueStatus::APPROVAL) {

            $approvalStage = ApprovalStatus::getStage($approval);
            if ($approvalStage == ApprovalStatus::APPROVAL_STAGE1) {
                return self::APPROVAL_1;
            }
            
            return self::APPROVAL_2;
        }

        if ($issueStage == IssueStatus::RELEASE) {
            return self::RELEASE;
        }
        
        if ($issueStage == IssueStatus::SENDING) {
            return self::SENDING;
        }

        if ($issueStage == IssueStatus::DONE) {
            return self::DONE;
        }
        
        return self::DRAFT;        
    }
    
    
    /**
     * setPagePermissions
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param array $settings
     * @param bool
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function setPagePermissions (Issue $issue, array $settings): bool
    {

        
        
        
        $permissionNames = [
            'userId',
            'groupId',
            'user',
            'group',
            'everybody',
        ];
        
        $update = false;
        foreach ($permissionNames as $permissionName) {
            
            if (
                (isset($settings[$stage]))
                && (isset($settings[$stage][$permissionName]))
                && ($permission = $settings[$stage][$permissionName])
                && (self::validate($permission))
            ) {
                $setter = 'setPerms' . ucfirst($permissionName);
                $approval->getPage()->$setter($permission);
                $update = true;
            }
        }
        
        return $update;
    }
    
    
    
    /**
     * @param int $permission
     * @return bool
     */
    public static function validatePermissions (int $permission): bool
    {
        if (
            ($permission < Permission::NOTHING)
            || ($permission > Permission::ALL)
        ) {
            return false;
        }
          
        return true;
    }
    
}