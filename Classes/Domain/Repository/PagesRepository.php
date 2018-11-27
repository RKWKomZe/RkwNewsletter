<?php

namespace RKW\RkwNewsletter\Domain\Repository;

use RKW\RkwBasics\Helper\QueryTypo3;

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
 * @package RKW_RkwMailer
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
     *  findAllByIssueAndBackendUserAndSpecialTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @param bool $isSpecial
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllByIssueAndBackendUserAndSpecialTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser, $isSpecial = false)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('txRkwnewsletterIssue', $issue),
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->contains('txRkwnewsletterTopic.approvalStage1', $backendUser),
                        $query->equals('txRkwnewsletterTopic.isSpecial', $isSpecial)
                    ),
                    $query->logicalAnd(
                        $query->contains('txRkwnewsletterTopic.approvalStage2', $backendUser),
                        $query->equals('txRkwnewsletterTopic.isSpecial', $isSpecial)
                    )
                )
            )
        );

        $query->setOrderings(
            array('txRkwnewsletterTopic.sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING)
        );

        return $query->execute();
        //===
    }

    /**
     *  findAllByIssueAndSpecialTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param bool $isSpecial
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllByIssueAndSpecialTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $isSpecial = false)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('txRkwnewsletterIssue', $issue),
                $query->equals('txRkwnewsletterTopic.isSpecial', $isSpecial)
            )
        );

        $query->setOrderings(
            array('txRkwnewsletterTopic.sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING)
        );

        return $query->execute();
        //===
    }

    /**
     *  findAllByIssueAndSubscriptionAndSpecialTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions
     * @param bool $isSpecial
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllByIssueAndSubscriptionAndSpecialTopic(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions, $isSpecial = false)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('txRkwnewsletterIssue', $issue),
                $query->in('txRkwnewsletterTopic', $subscriptions),
                $query->equals('txRkwnewsletterTopic.isSpecial', $isSpecial)
            )
        );

        $query->setOrderings(
            array('txRkwnewsletterTopic.sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING)
        );

        return $query->execute();
        //===
    }

}