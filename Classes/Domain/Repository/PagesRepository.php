<?php

namespace RKW\RkwNewsletter\Domain\Repository;

use RKW\RkwBasics\Helper\QueryTypo3;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \RKW\RkwBasics\Helper\Common;

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
 * PagesRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function initializeObject()
    {
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(false);
    }


    /**
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTopicNotIncluded(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('doktype', 1),
                $query->equals('txRkwnewsletterNewsletter', $topic->getNewsletter()),
                $query->equals('txRkwnewsletterTopic', $topic->getUid()),
                $query->equals('txRkwnewsletterIncludeTstamp', 0),
                $query->equals('txRkwnewsletterExclude', 0),
                $query->equals('deleted', 0),
                $query->equals('hidden', 0),
                $query->lessThanOrEqual('starttime', time())
            )
        );

        return $query->execute();
        //===
    }


    /**
     *  findAllByIssueAndTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function findAllByIssueAndTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {

        $statement = 'SELECT DISTINCT 
            pages.*
            FROM pages 
            LEFT JOIN tx_rkwnewsletter_domain_model_topic 
                ON 
                    pages.tx_rkwnewsletter_topic = tx_rkwnewsletter_domain_model_topic.uid 
            WHERE 
                (
                    pages.tx_rkwnewsletter_issue = ' . intval($issue->getUid()) . '
                    AND pages.tx_rkwnewsletter_topic = ' . intval($topic->getUid()) . '
                ) 
                AND (
                    SELECT COUNT(tt_content.uid) 
                    FROM tt_content 
                    WHERE 
                        tt_content.pid = pages.uid
                        ' . QueryTypo3::getWhereClauseForEnableFields('tt_content') . '
                ) >= 1 
                '. QueryTypo3::getWhereClauseForEnableFields('pages') . '
                AND 
                (
                    1 = 1 ' . QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_topic') . '
                )
            LIMIT 1
        ';

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $query */
        $query = $this->createQuery();
        $query->statement($statement);

        return $query->execute();
        //===
    }


    /**
     *  findAllByIssueAndBackendUserAndSpecialTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @param bool $isSpecial
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException

    public function findAllByIssueAndBackendUserAndSpecialTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser, $isSpecial = false)
    {

        $settings = $this->getSettings();
        $ordering = 'tx_rkwnewsletter_domain_model_topic.sorting ASC';
        if ($settings['randomTopicOrder']) {
            $ordering = 'RAND()';
        }

        $statement = 'SELECT DISTINCT 
            pages.* 
            FROM pages 
                LEFT JOIN tx_rkwnewsletter_domain_model_topic 
                    ON pages.tx_rkwnewsletter_topic= tx_rkwnewsletter_domain_model_topic.uid 
            WHERE (
                pages.tx_rkwnewsletter_issue  = ' . intval($issue->getUid()) . '  
                AND tx_rkwnewsletter_domain_model_topic.is_special = ' . intval($isSpecial) . '
                AND (
                    SELECT COUNT(tt_content.uid) 
                    FROM tt_content 
                    WHERE 
                        tt_content.pid = pages.uid
                        ' . QueryTypo3::getWhereClauseForEnableFields('tt_content') . '
                ) >= 1 
                AND 
                (
                    FIND_IN_SET(' . intval($backendUser->getUid()) . ', tx_rkwnewsletter_domain_model_topic.approval_stage1) 
                    OR FIND_IN_SET(' . intval($backendUser->getUid()) . ', tx_rkwnewsletter_domain_model_topic.approval_stage2) 
                )
            ) 

            '. QueryTypo3::getWhereClauseForEnableFields('pages') . '
            AND 
            (
                1 = 1 ' . QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_topic') . '
            ) 
            ORDER BY ' . $ordering . '
        ';


        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
        $query = $this->createQuery();
        $query->statement($statement);

        return $query->execute();
        //===
    }
     */

    /**
     *  findAllByIssueAndSpecialTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param bool $isSpecial
     * @param string $pagesOrder
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function findAllByIssueAndSpecialTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $isSpecial = false, $pagesOrder = null)
    {

        $settings = $this->getSettings();
        $ordering = 'tx_rkwnewsletter_domain_model_topic.sorting ASC';
        if ($settings['randomTopicOrder']) {
            $ordering = 'RAND()';
        }
        if ($pagesOrder) {
            $ordering = 'field(pages.uid, ' . preg_replace('/[^0-9,]+/', '', $pagesOrder) .')';
        }

        $statement = 'SELECT DISTINCT 
            pages.*
            FROM pages 
            LEFT JOIN tx_rkwnewsletter_domain_model_topic 
                ON 
                    pages.tx_rkwnewsletter_topic = tx_rkwnewsletter_domain_model_topic.uid 
            WHERE 
                pages.tx_rkwnewsletter_issue = ' . intval($issue->getUid()) . ' 
                AND tx_rkwnewsletter_domain_model_topic.is_special = ' . intval($isSpecial) . '
                AND (
                    SELECT COUNT(tt_content.uid) 
                    FROM tt_content 
                    WHERE 
                        tt_content.pid = pages.uid
                        ' . QueryTypo3::getWhereClauseForEnableFields('tt_content') . '
                ) >= 1 
                '. QueryTypo3::getWhereClauseForEnableFields('pages') . '
                AND 
                (
                    1 = 1 ' . QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_topic') . '
                )
            ORDER BY ' . $ordering . '
        ';

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $query */
        $query = $this->createQuery();
        $query->statement($statement);

        return $query->execute();
    }



    /**
     *  findAllByIssueAndSubscription
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions
     * @param string $pagesOrder
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function findAllByIssueAndSubscription(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions, $pagesOrder = null)
    {

        $settings = $this->getSettings();
        $ordering = 'tx_rkwnewsletter_domain_model_topic.sorting ASC';
        if ($settings['randomTopicOrder']) {
            $ordering = 'RAND()';
        }
        if ($pagesOrder) {
            $ordering = 'field(pages.uid, ' . preg_replace('/[^0-9,]+/', '', $pagesOrder) .')';
        }

        $subscriptionsList = [];
        foreach ($subscriptions as $subscription) {
            $subscriptionsList[] = $subscription->getUid();
        }
        $subscriptionsString = '\'' . implode('\',\'', $subscriptionsList) . '\'';


        $statement = 'SELECT DISTINCT 
            pages.* 
            FROM pages 
            LEFT JOIN tx_rkwnewsletter_domain_model_topic 
                ON pages.tx_rkwnewsletter_topic = tx_rkwnewsletter_domain_model_topic.uid 
            WHERE 
                (
                    (
                        pages.tx_rkwnewsletter_issue = ' . intval($issue->getUid()) . '
                        AND pages.tx_rkwnewsletter_topic IN (' . $subscriptionsString . ')
                    ) 
                    AND tx_rkwnewsletter_domain_model_topic.is_special = 0
                ) 
                AND (
                    SELECT COUNT(tt_content.uid) 
                    FROM tt_content 
                    WHERE 
                        tt_content.pid = pages.uid
                        ' . QueryTypo3::getWhereClauseForEnableFields('tt_content') . '
                ) >= 1 
                '. QueryTypo3::getWhereClauseForEnableFields('pages') . '
                AND 
                (
                    1 = 1 ' . QueryTypo3::getWhereClauseForEnableFields('tx_rkwnewsletter_domain_model_topic') . '
                )
            ORDER BY ' . $ordering . '
        ';

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $query */
        $query = $this->createQuery();
        $query->statement($statement);

        return $query->execute();
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwnewsletter', $which);
    }
}