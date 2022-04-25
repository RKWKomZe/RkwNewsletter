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

use RKW\RkwNewsletter\Domain\Model\Issue;

/**
 * IssueStatus
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueStatus
{

    /**
     * @var int
     */
    const STAGE_DRAFT = 0;
    
    /**
     * @var int
     */
    const STAGE_APPROVAL = 1;

    /**
     * @var int
     */
    const STAGE_RELEASE = 2;

    /**
     * @var int
     */
    const STAGE_SENDING = 3;    
    
    /**
     * @var int
     */
    const STAGE_DONE = 4;

    
    /**
     * @var int
     */
    const LEVEL_NONE = 0;
    
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
     * Returns current stage of the issue based on timestamps and status
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return int
     */
    public static function getStage (Issue $issue): int
    {

        if ($issue->getStatus() == 1) {
            return self::STAGE_APPROVAL;
        }

        if ($issue->getStatus() == 2) {
            return self::STAGE_RELEASE;
        }

        if ($issue->getStatus() == 3) {
            return self::STAGE_SENDING;
        }

        if ($issue->getStatus() == 4) {
            return self::STAGE_DONE;
        }
        
        return self::STAGE_DRAFT;
    }


    /**
     * Returns current mail-status of the issue based on timestamps
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return string
     */
    public static function getLevel (Issue $issue): string
    {
        
        // Only relevant for stage "release"
        if (self::getStage($issue) != self::STAGE_RELEASE) {
            return self::LEVEL_NONE;
        }
        
        if (
            ($issue->getInfoTstamp() < 1)
            && ($issue->getReminderTstamp() < 1)
        ) {
            return self::LEVEL1;
        }

        if ($issue->getReminderTstamp() < 1) {
            return self::LEVEL2;
        }
        
        return self::LEVEL_DONE;
    }



    /**
     * Increases the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return bool
     */
    public static function increaseStage (Issue $issue): bool
    {

        $stage = self::getStage($issue);

        $update = false;
        if ($stage == self::STAGE_DRAFT) {
            $issue->setStatus(self::STAGE_APPROVAL);
            $update = true;
        }

        if ($stage == self::STAGE_APPROVAL) {
            $issue->setStatus(self::STAGE_RELEASE);
            $update = true;
        }

        if ($stage == self::STAGE_RELEASE) {
            $issue->setStatus(self::STAGE_SENDING);
            $issue->setReleaseTstamp(time());
            $update = true;
        }

        if ($stage == self::STAGE_SENDING) {
            $issue->setStatus(self::STAGE_DONE);
            $issue->setSentTstamp(time());
            $update = true;
        }
        
        return $update;
    }
    

    /**
     * Increases the level of the release stage - only available here!!!!
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return bool
     */
    public static function increaseLevel (Issue $issue): bool
    {

        // Only relevant for stage "release"
        if (self::getStage($issue) != self::STAGE_RELEASE) {
            return false;
        }

        $level = self::getLevel($issue);

        $update = false;
        if ($level == self::LEVEL1) {
            $issue->setInfoTstamp(time());
            $update = true;

        } else if ($level == self::LEVEL2) {
            $issue->setReminderTstamp(time());
            $update = true;
        }
      
        return $update;
    }


}