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
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Exception;

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
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @return string
     * @throws \RKW\RkwNewsletter\Exception
     */
    public static function getStage (Pages $page): string
    {
        $issueStage = IssueStatus::getStage($page->getTxRkwnewsletterIssue());
        if ($issueStage == IssueStatus::STAGE_APPROVAL) {
            
            /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
            $approval = self::getApproval($page->getTxRkwnewsletterIssue(), $page->getTxRkwnewsletterTopic());
            $approvalStage = ApprovalStatus::getStage($approval);
            
            if ($approvalStage == ApprovalStatus::STAGE2) {
                return self::APPROVAL_2;
            }

            if ($approvalStage == ApprovalStatus::STAGE_DONE) {
                return self::RELEASE;
            }
            
            return self::APPROVAL_1;
        }

        if ($issueStage == IssueStatus::STAGE_RELEASE) {
            return self::RELEASE;
        }
        
        if ($issueStage == IssueStatus::STAGE_SENDING) {
            return self::SENDING;
        }

        if ($issueStage == IssueStatus::STAGE_DONE) {
            return self::DONE;
        }
        
        return self::DRAFT;        
    }

    
    
    /**
     * Returns approval-object by given issue and topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \RKW\RkwNewsletter\Domain\Model\Approval
     * @throws \RKW\RkwNewsletter\Exception
     */
    public static function getApproval (Issue $issue, Topic $topic): Approval
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        foreach ($issue->getApprovals() as $approval) {
            if (
                ($approval->getTopic())
                && ($approval->getTopic()->getUid() == $topic->getUid())
            ) {
                return $approval;
            }
        }

        throw new Exception(
            'No approval found for given issue and topic',
            1644845316
        );
       
    }
    
}