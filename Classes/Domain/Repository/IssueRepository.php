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

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * IssueRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /*
     * initializeObject
     */
    public function initializeObject()
    {
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }
    
    /**
     * findAllToReleaseByTime
     *
     * @param int $toleranceReminder
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToReleaseByTime($toleranceReminder)
    {
        $query = $this->createQuery();
        $constraints = $query->equals('infoTstamp', 0);

        // Check for reminder
        if ($toleranceReminder > 0) {
            $constraints =
                $query->logicalOr(
                    $query->equals('infoTstamp', 0),
                    $query->logicalOr(
                        $query->logicalAnd(
                            $query->greaterThan('infoTstamp', 0),
                            $query->equals('reminderTstamp', 0),
                            $query->lessThan('infoTstamp', time() - $toleranceReminder)
                        ),
                        $query->logicalAnd(
                            $query->greaterThan('infoTstamp', 0),
                            $query->greaterThan('reminderTstamp', 0),
                            $query->lessThan('reminderTstamp', time() - $toleranceReminder)
                        )
                    )
                );
        }

        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->equals('status', 2),
                    $query->equals('status', 1)
                ),
                $query->equals('sentTstamp', 0),
                $query->equals('releaseTstamp', 0),
                $constraints
            )
        );

        return $query->execute();
        //===
    }

    /**
     *  findAllToApproveOrReleaseByBackendUser
     *
     * @param int $backendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToApproveOrReleaseByBackendUser($backendUser)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->in('status', array(1, 2)),
                $query->equals('releaseTstamp', 0),
                $query->logicalOr(
                    $query->contains('newsletter.approval', $backendUser),
                    $query->contains('approvals.topic.approvalStage1', $backendUser),
                    $query->contains('approvals.topic.approvalStage2', $backendUser)
                )
            )
        );

        return $query->execute();
        //===
    }


    /**
     * findAllToApproveOnStage1ByBackendUser
     *
     * @param int $backendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToApproveOnStage1ByBackendUser($backendUser)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 1),
                $query->equals('infoTstamp', 0),
                $query->equals('releaseTstamp', 0),
                $query->equals('approvals.allowedTstampStage1', 0),
                $query->logicalOr(
                    $query->contains('approvals.topic.approvalStage1', $backendUser),
                    $query->contains('newsletter.approval', $backendUser)
                )
            )
        );

        return $query->execute();
        //===
    }


    /**
     * findAllToApproveOnStage2ByBackendUser
     *
     * @param int $backendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToApproveOnStage2ByBackendUser($backendUser)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 1),
                $query->equals('infoTstamp', 0),
                $query->equals('releaseTstamp', 0),
                $query->logicalAnd(
                    $query->greaterThan('approvals.allowedTstampStage1', 0),
                    $query->equals('approvals.allowedTstampStage2', 0)
                ),
                $query->logicalOr(
                    $query->contains('approvals.topic.approvalStage2', $backendUser),
                    $query->contains('newsletter.approval', $backendUser)
                )
            )
        );

        return $query->execute();
        //===
    }


    /**
     *  findAllToSendByBackendUser
     *
     * @param int $backendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToSendByBackendUser($backendUser)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 2),
                $query->equals('releaseTstamp', 0),
                $query->contains('newsletter.approval', $backendUser)
            )
        );

        return $query->execute();
        //===
    }

    /**
     *  findAllToSend
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllToSend($limit = 5)
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 3),
                $query->equals('sentTstamp', 0)
                // $query->logicalNot($query->equals('recipients', ''))
            )
        );

        $query->setLimit($limit);

        return $query->execute();
        //===
    }


}