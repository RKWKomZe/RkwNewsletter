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

use RKW\RkwNewsletter\Domain\Model\Issue;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * FrontendUserRepository
 *
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
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findSubscriptionsByTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function findSubscriptionsByTopic(Topic $topic): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->contains('txRkwnewsletterSubscription', $topic)
        );

        // we take the uid here in order to be able to work with offset properly!
        $query->setOrderings(
            ['uid' => QueryInterface::ORDER_ASCENDING]
        );
        
        return $query->execute();
    }


    /**
     * findSubscriptionsByNewsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @comment implicitly tested
     */
    public function findSubscriptionsByNewsletter(Newsletter $newsletter): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('txRkwnewsletterSubscription', 0),
                $query->in('txRkwnewsletterSubscription', $newsletter->getTopic())
            )
        );

        // we take the uid here in order to be able to work with offset properly!
        $query->setOrderings(
            ['uid' => QueryInterface::ORDER_ASCENDING]
        );

        return $query->execute();
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
    


}
