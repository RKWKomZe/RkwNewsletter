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

use RKW\RkwNewsletter\Domain\Model\Pages;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * ContentRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ContentRepository extends AbstractRepository
{
    /*
     * initializeObject
     */
    public function initializeObject()
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setRespectSysLanguage(false);
    }


    /**
     * findByPageAndLanguageUid
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param int $languageUid
     * @param int $limit
     * @param bool $includeEditorials
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * comment: implicitly tested
     */
    public function findByPageAndLanguage(
        Pages $page,
        int $languageUid = 0,
        int $limit = 0,
        bool $includeEditorials = false
    ): QueryResultInterface {

        $query = $this->createQuery();
        $constraints = [
            $query->equals('pid', $page),
            $query->equals('sysLanguageUid', $languageUid)
        ];

        if (! $includeEditorials) {
            $constraints[] = $query->equals('txRkwnewsletterIsEditorial', 0);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        $query->setOrderings(
            array(
                'sorting' => QueryInterface::ORDER_ASCENDING,
            )
        );

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }


    /**
     * countByPageAndLanguageUid
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param int $languageUid
     * @param bool $includeEditorials
     * @return int
     * comment: implicitly tested
     */
    public function countByPageAndLanguage(
        Pages $page,
        int $languageUid = 0,
        bool $includeEditorials = false
    ): int {

        $query = $this->createQuery();
        $constraints = [
            $query->equals('pid', $page),
            $query->equals('sysLanguageUid', $languageUid)
        ];

        if (! $includeEditorials) {
            $constraints[] = $query->equals('txRkwnewsletterIsEditorial', 0);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute()->count();
    }


    /**
     * countByPagesAndLanguageUid
     *
     * @param array<\RKW\RkwNewsletter\Domain\Model\Pages> $pages
     * @param int $languageUid
     * @param bool $includeEditorials
     * @return int
     * comment: implicitly tested
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countByPagesAndLanguage(
        array $pages,
        int $languageUid = 0,
        bool $includeEditorials = false
    ): int {

        $query = $this->createQuery();
        $constraints = [
            $query->in('pid', $pages),
            $query->equals('sysLanguageUid', $languageUid)
        ];

        if (! $includeEditorials) {
            $constraints[] = $query->equals('txRkwnewsletterIsEditorial', 0);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        $query->setOrderings(
            array(
                'sorting' => QueryInterface::ORDER_ASCENDING,
            )
        );

        return $query->execute()->count();
    }


    /**
     * findOneEditorialsByPagesAndLanguage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param int $languageUid
     * @return \RKW\RkwNewsletter\Domain\Model\Content|null
     * comment: implicitly tested
     */
    public function findOneEditorialByPageAndLanguage(
        Pages $page,
        int $languageUid = 0
    ) {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('pid', $page),
                $query->equals('sysLanguageUid', $languageUid),
                $query->equals('txRkwnewsletterIsEditorial', 1)
            )
        );

        return $query->execute()->getFirst();
    }


}
