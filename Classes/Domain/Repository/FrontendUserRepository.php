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
 * FrontendUserRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {

        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint and enable fields
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * findOneByTxRkwnewsletterHash
     *
     * @var string $hash
     * @return \RKW\RkwNewsletter\Domain\Model\FrontendUser|NULL
     */
    public function findOneByTxRkwnewsletterHash($hash)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('TxRkwnewsletterHash', $hash)
        );

        return $query->execute()->getFirst();
    }


    /**
     * findAllSubscribersByIssue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllSubscribersByIssue(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwNewsletter\Domain\Model\Topic $topic = null)
    {

        $query = $this->createQuery();
        $constraint = $query->in('txRkwnewsletterSubscription.uid', $issue->getNewsletter()->getTopic());
        if ($topic) {
            $constraint = $query->contains('txRkwnewsletterSubscription', $topic);
        }

        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('txRkwnewsletterSubscription', 0),
                $constraint
            )
        );

        $query->setOrderings(
            ['txRkwnewsletterPriority' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]
        );

        return $query->execute();
    }


    /**
     * findAllSubscribers
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllSubscribers()
    {

        $query = $this->createQuery();
        $query->matching(
            $query->greaterThan('txRkwnewsletterSubscription', 0)
        );

        $query->setOrderings(
            ['txRkwnewsletterPriority' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]
        );

        return $query->execute();
    }


}
