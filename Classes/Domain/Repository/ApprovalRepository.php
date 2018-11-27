<?php

namespace RKW\RkwNewsletter\Domain\Repository;
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
 * ApprovalRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * findAllAutomaticApproveByTime
     *
     * @param int $toleranceApprovalStage1
     * @param int $toleranceApprovalStage2
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllForAutomaticApproveByTime($toleranceApprovalStage1, $toleranceApprovalStage2)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->lessThan('sentInfoTstampStage1', intval(time() - $toleranceApprovalStage1)),
                        $query->greaterThan('sentInfoTstampStage1', 0),
                        $query->equals('allowedTstampStage1', 0)
                    ),
                    $query->logicalAnd(
                        $query->lessThan('sentInfoTstampStage2', intval(time() - $toleranceApprovalStage2)),
                        $query->greaterThan('sentInfoTstampStage2', 0),
                        $query->greaterThan('allowedTstampStage1', 0),
                        $query->equals('allowedTstampStage2', 0)
                    )
                ),
                $query->equals('issue.status', 1),
                $query->equals('issue.sentTstamp', 0),
                $query->equals('issue.releaseTstamp', 0)
            )

        );

        return $query->execute();
        //===
    }


    /**
     * findAllAutomaticApproveByAdminsMissing
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllForAutomaticApproveByAdminsMissing()
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->equals('allowedTstampStage1', 0),
                        $query->lessThan('topic.approvalStage1', 1)
                    ),
                    $query->logicalAnd(
                        $query->greaterThan('allowedTstampStage1', 0),
                        $query->equals('allowedTstampStage2', 0),
                        $query->lessThan('topic.approvalStage2', 1)
                    )
                ),
                $query->equals('issue.status', 1),
                $query->equals('issue.sentTstamp', 0),
                $query->equals('issue.releaseTstamp', 0)
            )

        );

        return $query->execute();
        //===
    }


    /**
     * findAllOpenApprovals
     *
     * @param int $toleranceReminderStage1
     * @param int $toleranceReminderStage2
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllOpenApprovalsByTime($toleranceReminderStage1, $toleranceReminderStage2)
    {
        $query = $this->createQuery();
        $constraints = [];

        // Check for reminder settings on stage 1
        if (intval($toleranceReminderStage1) > 0) {
            $constraints[] =
                $query->logicalAnd(
                    $query->equals('allowedTstampStage1', 0),
                    $query->logicalOr(
                        $query->equals('sentInfoTstampStage1', 0),
                        $query->logicalAnd(
                            $query->lessThan('sentInfoTstampStage1', time() - $toleranceReminderStage1),
                            $query->equals('sentReminderTstampStage1', 0)
                        )
                    )
                );

        } else {
            $constraints[] =
                $query->logicalAnd(
                    $query->equals('allowedTstampStage1', 0),
                    $query->equals('sentInfoTstampStage1', 0)
                );
        }

        // Check for reminder settings on stage 2
        if (intval($toleranceReminderStage2) > 0) {
            $constraints[] =
                $query->logicalAnd(
                    $query->greaterThan('allowedTstampStage1', 0),
                    $query->equals('allowedTstampStage2', 0),
                    $query->logicalAnd(
                        $query->lessThan('sentInfoTstampStage2', time() - $toleranceReminderStage2),
                        $query->equals('sentReminderTstampStage2', 0)
                    )
                );

        } else {
            $constraints[] =
                $query->logicalAnd(
                    $query->greaterThan('allowedTstampStage1', 0),
                    $query->equals('allowedTstampStage2', 0),
                    $query->equals('sentInfoTstampStage2', 0)
                );
        }

        // Build query
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $constraints
                ),
                $query->equals('issue.status', 1),
                $query->equals('issue.sentTstamp', 0),
                $query->equals('issue.releaseTstamp', 0)
            )
        );

        return $query->execute();
        //===
    }

}