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
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * IssueRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueRepository extends AbstractRepository
{
    /*
     * initializeObject
     */
    public function initializeObject()
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findAllForConfirmationByTolerance
     *
     * @param int $toleranceLevel2
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findAllForConfirmationByTolerance(int $toleranceLevel2): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(

                // status is approval or release
                $query->logicalOr(
                    $query->equals('status', 2),
                    $query->equals('status', 1)
                ),

                // nor released nor sent
                $query->equals('sentTstamp', 0),
                $query->equals('releaseTstamp', 0),

                // Check level 1 and level 2
                $query->logicalOr(
                    $query->equals('infoTstamp', 0),
                    $query->logicalOr(
                        $query->logicalAnd(
                            $query->greaterThan('infoTstamp', 0),
                            $query->equals('reminderTstamp', 0),
                            $query->lessThan('infoTstamp', time() - $toleranceLevel2)
                        ),
                        $query->logicalAnd(
                            $query->greaterThan('infoTstamp', 0),
                            $query->greaterThan('reminderTstamp', 0),
                            $query->lessThan('reminderTstamp', time() - $toleranceLevel2)
                        )
                    )
                )
            )
        );

        return $query->execute();
    }



    /**
     * findAllToApproveOnStage1
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: only used in backend module
     */
    public function findAllToApproveOnStage1(): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 1),
                $query->equals('approvals.allowedTstampStage1', 0)
            )
        );

        return $query->execute();
    }


    /**
     * findAllToApproveOnStage2
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: only used in backend module
     */
    public function findAllToApproveOnStage2(): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 1),
                $query->logicalAnd(
                    $query->greaterThan('approvals.allowedTstampStage1', 0),
                    $query->equals('approvals.allowedTstampStage2', 0)
                )
            )
        );

        return $query->execute();
    }


    /**
     * findAllForTestSending
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: only used in backend module
     */
    public function findAllForTestSending(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->in('status', array(1, 2)),
                $query->equals('releaseTstamp', 0)
            )
        );

        return $query->execute();
    }


    /**
     * findAllToStartSending
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: only used in backend module
     */
    public function findAllToStartSending(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('status',2),
                $query->logicalAnd(
                    $query->greaterThan('approvals.allowedTstampStage1', 0),
                    $query->greaterThan('approvals.allowedTstampStage2', 0)
                )
            )
        );

        return $query->execute();
    }


    /**
     * findAllToSend
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: only used in command controller
     */
    public function findAllToSend(int $limit = 5): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('status', 3),
                $query->greaterThan('releaseTstamp', 0),
                $query->equals('sentTstamp', 0)
            )
        );

        $query->setLimit($limit);

        return $query->execute();
    }











    /**
     *  findAllToSendByBackendUser
     *
     * @param int $backendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllToSendByBackendUser($backendUser): QueryResultInterface
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
    }



}
