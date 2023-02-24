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
 * ApprovalRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalRepository extends AbstractRepository
{

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findAllOpenApprovals
     *
     * @param int $toleranceLevel2
     * @param int $toleranceLevel1
     * @param int $toleranceStage1
     * @param int $toleranceStage2
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findAllForConfirmationByTolerance(
        int $toleranceLevel1,
        int $toleranceLevel2,
        int $toleranceStage1 = 0,
        int $toleranceStage2 = 0
    ): QueryResultInterface {
        $query = $this->createQuery();
        $constraints = [];

        // Check for info/reminder settings on stage 1
        $constraints[] =
            $query->logicalAnd(
                $query->equals('allowedTstampStage1', 0),
                $query->logicalOr(
                    $query->equals('sentInfoTstampStage1', 0),
                    $query->logicalAnd(
                        $query->lessThan('sentInfoTstampStage1', time() - $toleranceLevel1),
                        $query->equals('sentReminderTstampStage1', 0)
                    )
                )
            );

        // Check for info/reminder on stage 2
        $constraints[] =
            $query->logicalAnd(
                $query->greaterThan('allowedTstampStage1', 0),
                $query->equals('allowedTstampStage2', 0),
                $query->logicalOr(
                    $query->equals('sentInfoTstampStage2', 0),
                    $query->logicalAnd(
                        $query->lessThan('sentInfoTstampStage2', time() - $toleranceLevel2),
                        $query->equals('sentReminderTstampStage2', 0)
                    )
                )
            );

        // Check for automatic approval on stage 1
        if ($toleranceStage1) {
            $constraints[] =
                $query->logicalAnd(
                    $query->lessThan('sentInfoTstampStage1', time() - $toleranceStage1),
                    $query->greaterThan('sentInfoTstampStage1', 0),
                    $query->greaterThan('sentReminderTstampStage1', 0),
                    $query->equals('allowedTstampStage1', 0)
                );
        }

        // Check for automatic approval on stage 2
        if ($toleranceStage2) {
            $constraints[] =
                $query->logicalAnd(
                    $query->lessThan('sentInfoTstampStage2', time() - $toleranceStage2),
                    $query->greaterThan('sentInfoTstampStage2', 0),
                    $query->greaterThan('sentReminderTstampStage2', 0),
                    $query->greaterThan('allowedTstampStage1', 0),
                    $query->equals('allowedTstampStage2', 0)
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
    }

}
