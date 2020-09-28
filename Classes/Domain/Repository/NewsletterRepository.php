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

    /**
     * findAllToBuildIssue
     *
     * @param int $tolerance
     * @param int $dayOfMonth
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function findAllToBuildIssue($tolerance = 0, $dayOfMonth = 15)
    {

        $query = $this->createQuery();
        $query->statement(
            'SELECT * FROM tx_rkwnewsletter_domain_model_newsletter WHERE
            (
              (rythm = 1 AND WEEKOFYEAR(FROM_UNIXTIME(last_issue_tstamp)) < WEEKOFYEAR(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND))) 			
	          OR (rythm = 2 
	            AND (
                    (
                        MONTH(FROM_UNIXTIME(last_issue_tstamp)) < MONTH(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND)) 
                    )
                    OR (
                        MONTH(FROM_UNIXTIME(last_issue_tstamp)) = 12 AND YEAR(FROM_UNIXTIME(last_issue_tstamp)) < YEAR(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND))
                    )
                )
                AND DAY(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND)) >= ' . intval($dayOfMonth) . ' 
	          )
	          OR (rythm = 3 AND QUARTER(FROM_UNIXTIME(last_issue_tstamp)) < QUARTER(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND)) AND DAY(DATE_ADD(NOW(), INTERVAL +' . intval($tolerance) . ' SECOND)) >= ' . intval($dayOfMonth) . ')
            )' .
            \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_newsletter') .
            \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForVersioning('tx_rkwnewsletter_domain_model_newsletter')
        );

        return $query->execute();
    }


    /**
     * Returns all newsletters by type
     *
     * @param int $type
     * @return QueryResultInterface|array
     * @api
     */
    public function findAllByType($type = 0)
    {

        $query = $this->createQuery();
        $query->matching(
            $query->equals('type', $type)
        );

        return $query->execute();
    }
}