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
    const DRAFT = 0;
    
    /**
     * @var int
     */
    const APPROVAL = 1;

    /**
     * @var int
     */
    const RELEASE = 2;

    /**
     * @var int
     */
    const SENDING = 3;    
    
    /**
     * @var int
     */
    const DONE = 4;
    
    
    
    /**
     * Returns current stage of the issue based on timestamps and status
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return int
     */
    public static function getStage (Issue $issue): int
    {

        if ($issue->getStatus() == 1) {
            return self::APPROVAL;
        }

        if ($issue->getStatus() == 2) {
            return self::RELEASE;
        }

        if ($issue->getStatus() == 3) {
            return self::SENDING;
        }

        if ($issue->getStatus() == 4) {
            return self::DONE;
        }
        
        return self::DRAFT;
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
        if ($stage == self::DRAFT) {
            $issue->setStatus(self::APPROVAL);
            $update = true;
        }

        if ($stage == self::APPROVAL) {
            $issue->setStatus(self::RELEASE);
            $update = true;
        }

        if ($stage == self::RELEASE) {
            $issue->setStatus(self::SENDING);
            $issue->setReleaseTstamp(time());
            $update = true;
        }

        if ($stage == self::SENDING) {
            $issue->setStatus(self::DONE);
            $issue->setSentTstamp(time());
            $update = true;
        }
        
        return $update;
    }
    
}