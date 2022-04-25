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
use RKW\RkwNewsletter\Domain\Model\BackendUser;

/**
 * ApprovalStatus
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalStatus
{
    /**
     * @var int
     */
    const STAGE1 = 1;

    /**
     * @var int
     */
    const STAGE2 = 2;

    /**
     * @var int
     */
    const STAGE_DONE = 3;
    

    /**
     * @var int
     */
    const LEVEL1 = 1;

    /**
     * @var int
     */
    const LEVEL2 = 2;

    /**
     * @var int
     */
    const LEVEL_DONE = 3;

    
    /**
     * Returns current stage of the approval based on timestamps
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return int
     */
    public static function getStage (Approval $approval): int
    {
        
        if ($approval->getAllowedTstampStage2()) {

            return self::STAGE_DONE;
            
        } else if ($approval->getAllowedTstampStage1()) {

            return self::STAGE2;
        }

        return self::STAGE1;
    }

   

    /**
     * Returns current mail-status of the approval based on timestamps
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return string
     */
    public static function getLevel (Approval $approval): string
    {

        if (self::getStage($approval) == self::STAGE1) {

            if (
                ($approval->getSentInfoTstampStage1() < 1)
                && ($approval->getSentReminderTstampStage1() < 1)
            ) {
                return self::LEVEL1;
            }
            
            if ($approval->getSentReminderTstampStage1() < 1) {
                return self::LEVEL2;
            }
        }

        if (self::getStage($approval) == self::STAGE2) {

            if (
                ($approval->getSentInfoTstampStage2() < 1)
                && ($approval->getSentReminderTstampStage2() < 1)
            ) {
                return self::LEVEL1;
            }

            if ($approval->getSentReminderTstampStage2() < 1) {
                return self::LEVEL2;
            }
        }
        
        return self::LEVEL_DONE;
    }

    
    /**
     * Increases the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return bool
     */
    public static function increaseStage (Approval $approval, BackendUser $backendUser = null): bool
    {

        $stage = self::getStage($approval);

        $update = false;
        if ($stage == self::STAGE1) {

            if ($backendUser) {
                $approval->setAllowedByUserStage1($backendUser);
            }
            $approval->setAllowedTstampStage1(time());
            $update = true;
        }

        if ($stage == self::STAGE2) {
            if ($backendUser) {
                $approval->setAllowedByUserStage2($backendUser);
            }
            $approval->setAllowedTstampStage2(time());
            $update = true;
        }
        
        return $update;
    }
    
    
    /**
     * Increases the level of the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval Approval $approval
     * @return bool
     */
    public static  function increaseLevel (Approval $approval): bool
    {

        $stage = self::getStage($approval);
        $level = self::getLevel($approval);

        $update = false;
        if ($stage == self::STAGE1) {
            if ($level == self::LEVEL1) {
                $approval->setSentInfoTstampStage1(time());
                $update = true;

            } else {
                if ($level == self::LEVEL2) {
                    $approval->setSentReminderTstampStage1(time());
                    $update = true;
                }
            }
        }

        if ($stage == self::STAGE2) {
            if ($level == self::LEVEL1) {
                $approval->setSentInfoTstampStage2(time());
                $update = true;
            } else {
                if ($level == self::LEVEL2) {
                    $approval->setSentReminderTstampStage2(time());
                    $update = true;
                }
            }
        }

        return $update;
    }


}