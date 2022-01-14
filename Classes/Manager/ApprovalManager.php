<?php
namespace RKW\RkwNewsletter\Manager;

use RKW\RkwBasics\Domain\Model\FileReference;
use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Exception;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * ApprovalManager
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalManager implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository;

    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

  

    /**
     * Creates an approval for the given topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @return \RKW\RkwNewsletter\Domain\Model\Approval
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createApproval (Topic $topic, Issue $issue, Pages $page): Approval
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = GeneralUtility::makeInstance(Approval::class);
        $approval->setTopic($topic);
        $approval->setPage($page);

        $this->approvalRepository->add($approval);
        $issue->addApprovals($approval);
        $this->issueRepository->update($issue);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Created approval for topic with id=%s of issue with id=%s.',
                $topic->getUid(),
                $issue->getUid()
            )
        );
        
        return $approval;
    }
    

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}