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

use RKW\RkwBasics\Helper\QueryTypo3;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * NewsletterRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewsletterRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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
     * findAllToBuildIssue
     *
     * @param int $tolerance in seconds
     * @param int $dayOfMonth
     * @param int $currentTime
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @comment only used in backend
     */
    public function findAllToBuildIssue(int $tolerance = 0, int $currentTime = 0): QueryResultInterface
    {

        if (! $currentTime) {
            $currentTime = time();
        }
        
        $statement = 'SELECT * FROM tx_rkwnewsletter_domain_model_newsletter WHERE
            (
                (
                    rythm = 1 
                    AND WEEKOFYEAR(FROM_UNIXTIME(last_issue_tstamp)) < WEEKOFYEAR(FROM_UNIXTIME(' . ($currentTime + $tolerance) . '))
                ) 			
                OR (
                    rythm = 2 
                    AND DATEDIFF(FROM_UNIXTIME(' . ($currentTime + $tolerance) . '), FROM_UNIXTIME(last_issue_tstamp)) >= 30
                    AND (DAY(FROM_UNIXTIME(' . ($currentTime + $tolerance) . ')) >= day_for_sending) 
                )
                OR (
                    rythm = 3 
                    AND DATEDIFF(FROM_UNIXTIME(' . ($currentTime + $tolerance) . '), FROM_UNIXTIME(last_issue_tstamp)) >= (30*2)
                    AND (DAY(FROM_UNIXTIME(' . ($currentTime + $tolerance) . ')) >= day_for_sending) 
                )
                OR (
                    rythm = 5 
                    AND DATEDIFF(FROM_UNIXTIME(' . ($currentTime + $tolerance) . '), FROM_UNIXTIME(last_issue_tstamp)) >= (30*6)    
                    AND (DAY(FROM_UNIXTIME(' . ($currentTime + $tolerance) . ')) >= day_for_sending) 
                )
                OR (
                    rythm = 4 
                    AND (
                        (
                            QUARTER(FROM_UNIXTIME(last_issue_tstamp)) < QUARTER(FROM_UNIXTIME(' . ($currentTime + $tolerance). ')) 
                            AND QUARTER(FROM_UNIXTIME(last_issue_tstamp)) != QUARTER(FROM_UNIXTIME(' . ($currentTime) . '))
                        )
                        OR (
                            QUARTER(FROM_UNIXTIME(last_issue_tstamp)) > QUARTER(FROM_UNIXTIME(' . ($currentTime + $tolerance). '))  AND YEAR(FROM_UNIXTIME(last_issue_tstamp)) < YEAR(FROM_UNIXTIME(' . $currentTime . '))
                        )
                    )                    
                    AND (DAY(FROM_UNIXTIME(' . ($currentTime + $tolerance) . ')) >= day_for_sending)   
                )
            )' .
            QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_newsletter') .
            QueryTypo3::getWhereClauseForVersioning('tx_rkwnewsletter_domain_model_newsletter');

        $query = $this->createQuery();
        $query->statement($statement);
        return $query->execute();
    }


    /**
     * Returns all newsletters by type
     *
     * @param int $type
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllByType(int $type = 0): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->equals('type', $type)
        );

        return $query->execute();
    }
}