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
 * TtContentRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /*
     * initializeObject
     */
    public function initializeObject()
    {
        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setRespectSysLanguage(false);
    }


    /**
     * findByPidAndLanguageUid
     *
     * @param int $pid
     * @param int $languageUid
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllByPidAndLanguageUid($pid, $languageUid = 0, $limit = 0)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('pid', $pid),
                $query->equals('sysLanguageUid', $languageUid)
            )
        );

        if ($limit) {
            $query->setLimit($limit);
        }

        return $query->execute();
        //====
    }


    /**
     * add
     * Workaround because extension of repository doesn't seem to work properly here
     *
     * @toDo: remove this work-around
     * @param \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement
     * @return void
     */
    public function add($ttContentElement)
    {

        $authorsList = array();
        if (count($ttContentElement->getTxRkwNewsletterAuthors())) {
            /** @var \RKW\RkwNewsletter\Domain\Model\Authors $author */
            foreach ($ttContentElement->getTxRkwNewsletterAuthors() as $author) {
                $authorsList[] = $author->getUid();
            }
        }


        $GLOBALS['TYPO3_DB']->exec_INSERTquery(
            'tt_content',
            array(
                'pid'                      => $ttContentElement->getPid(),
                'crdate'                   => time(),
                'CType'                    => $ttContentElement->getContentType(),
                'image'                    => 0,
                'imagecols'                => $ttContentElement->getImageCols(),
                'sys_language_uid'         => $ttContentElement->getSysLanguageUid(),
                'header'                   => $ttContentElement->getHeader(),
                'header_link'              => $ttContentElement->getHeaderLink(),
                'bodytext'                 => $ttContentElement->getBodytext(),
                'tx_rkwnewsletter_authors' => implode(',', $authorsList),

            )
        );

        $ttContentElement->setUid($GLOBALS['TYPO3_DB']->sql_insert_id());
    }

    /**
     * updateImage
     * Workaround because extension of repository doesn't seem to work properly here
     *
     * @toDo: remove this work-around
     * @param \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement
     * @return void
     */
    public function updateImage($ttContentElement)
    {
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
            'tt_content',
            'uid = ' . $ttContentElement->getUid(),
            [
                'image' => 1,
            ]
        );
    }

}