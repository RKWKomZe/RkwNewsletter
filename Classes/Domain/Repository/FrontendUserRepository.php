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

use RKW\RkwNewsletter\Domain\Model\FrontendUser;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * FrontendUserRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends AbstractRepository
{

    /**
     * initializeObject
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findSubscriptionsByTopic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findSubscriptionsByTopic(Topic $topic): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->contains('txRkwnewsletterSubscription', $topic)
        );

        $query->setOrderings(
            ['txRkwnewsletterPriority' => QueryInterface::ORDER_DESCENDING]
        );

        return $query->execute();
    }


    /**
     * findSubscriptionsByNewsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findSubscriptionsByNewsletter(Newsletter $newsletter): QueryResultInterface
    {

        $query = $this->createQuery();

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $constrains = [];
        foreach ($newsletter->getTopic() as $topic) {
            $constrains[] = $query->contains('txRkwnewsletterSubscription', $topic);
        }

        if ($constrains) {
            $query->matching(
                $query->logicalAnd(
                    $query->greaterThan('txRkwnewsletterSubscription', 0),
                    $query->logicalOr($constrains)
                )
            );
        } else {
            $query->matching(
                $query->greaterThan('txRkwnewsletterSubscription', 0)
            );
        }

        $query->setOrderings(
            ['txRkwnewsletterPriority' => QueryInterface::ORDER_DESCENDING]
        );

        return $query->execute();
    }


    /**
     * findOneByTxRkwnewsletterHash
     *
     * @var string $hash
     * @return \RKW\RkwNewsletter\Domain\Model\FrontendUser|null
     */
    public function findOneByTxRkwnewsletterHash(string $hash):? FrontendUser
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('TxRkwnewsletterHash', $hash)
        );

        return $query->execute()->getFirst();
    }
}
