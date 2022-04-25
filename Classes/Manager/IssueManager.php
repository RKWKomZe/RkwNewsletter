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
use RKW\RkwNewsletter\Status\ApprovalStatus;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
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
 * IssueManager
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueManager implements \TYPO3\CMS\Core\SingletonInterface
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
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\FileReferenceRepository
     * @inject
     */
    protected $fileReferenceRepository;

    
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    private $objectManager;
    
    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * Signal Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_FOR_SENDING_MAIL_RELEASE = 'sendMailRelease';

    
    /**
     * replaceTitlePlaceholder
     *
     * @param string $title
     * @return string
     */
    public function replaceTitlePlaceholders (string $title): string
    {
        $title = str_replace("{M}", date("m", time()), $title);
        $title = str_replace("{Y}", date("Y", time()), $title);

        return $title;
    }
    
    
    /**
     * Creates an issue
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param bool $isSpecial
     * @return \RKW\RkwNewsletter\Domain\Model\Issue
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createIssue(Newsletter $newsletter, bool $isSpecial = false): Issue
    {

        // check if persisted
        if ($newsletter->_isNew()) {
            throw new Exception('Newsletter is not persisted.', 1639058270);
        }
        
        // create issue and set title
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = GeneralUtility::makeInstance(Issue::class);
        $issue->setTitle(($isSpecial ? 'SPECIAL: ': '') . $this->replaceTitlePlaceholders($newsletter->getIssueTitle()));
        $issue->setStatus(0);
        $issue->setIsSpecial($isSpecial);

        // persist in order to get uid
        $this->issueRepository->add($issue);
        $newsletter->addIssue($issue);
        $this->newsletterRepository->update($newsletter);
        $this->persistenceManager->persistAll();
        
        $this->getLogger()->log(
            LogLevel::DEBUG, 
            sprintf(
                'Created issue with id=%s of newsletter with id=%s.', 
                $issue->getUid(),
                $newsletter->getUid()
            )
        );
        
        return $issue;
    }


    /**
     * Creates a page for the topic
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return \RKW\RkwNewsletter\Domain\Model\Pages
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @toDo add translation functionality when upgraded to TYPO3 9.5 and above
     */
    public function createPage(Newsletter $newsletter, Topic $topic, Issue $issue): Pages
    {

        // check if container-page exists
        if (! $topic->getContainerPage()) {
            throw new Exception('Container-page does not exist.', 1641967659);
        }

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = GeneralUtility::makeInstance(Pages::class);
        $page->setTxRkwnewsletterNewsletter($newsletter);
        $page->setTxRkwnewsletterTopic($topic);

        $page->setTitle($issue->getTitle());
        $page->setDokType(1);
        $page->setPid($topic->getContainerPage()->getUid());
        $page->setNoSearch(true);
        $page->setTxRkwnewsletterExclude(true);
        // $page->setSysLanguageUid($newsletter->getSysLanguageUid());

        $this->pagesRepository->add($page);
        $this->persistenceManager->persistAll();

        $issue->addPages($page);
        $this->issueRepository->update($issue);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::DEBUG, 
            sprintf(
                'Created page with id=%s and sysLanguageUid=%s for topic id=%s in parent page with id=%s of newsletter with id=%s.',
                $page->getUid(), 
                'null',
                $topic->getUid(), 
                $topic->getContainerPage()->getUid(),
                $newsletter->getUid()
            )
        );

        return $page;
    }


    /**
     * Creates a content element based on page-properties
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $sourcePage
     * @return \RKW\RkwNewsletter\Domain\Model\Content
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createContent (Newsletter $newsletter, Pages $page, Pages $sourcePage): Content
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content  = GeneralUtility::makeInstance(Content::class);
        
        $content->setPid($page->getUid());
        $content->setSysLanguageUid($newsletter->getSysLanguageUid());
        $content->setContentType('textpic');
        $content->setImageCols(1);

        $content->setHeader($sourcePage->getTxRkwnewsletterTeaserHeading() ?: $sourcePage->getTitle());
        $content->setBodytext($sourcePage->getTxRkwnewsletterTeaserText() ?: $sourcePage->getAbstract());
        $content->setHeaderLink($sourcePage->getTxRkwnewsletterTeaserLink() ?: 't3://page?uid=' . $sourcePage->getUid());
        
        // we need a loop here to work with persistence - what ever...
        foreach ($sourcePage->getTxRkwauthorsAuthorship() as $author) {
            $content->addTxRkwnewsletterAuthors($author);
            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Added author with id=%s to content of page with uid=%s of newsletter with id=%s.',
                    $author->getUid(),
                    $sourcePage->getUid(),
                    $newsletter->getUid()
                )
            );
        }

        // add object
        $this->contentRepository->add($content);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::DEBUG, 
            sprintf(
                'Added content-element with id=%s and sysLanguageUid=%s to page with uid=%s of newsletter with id=%s.', 
                $content->getUid(), 
                $content->getSysLanguageUid(), 
                $page->getUid(), 
                $newsletter->getUid()
            )
        );

        return $content;
    }


    /**
     * Creates a file reference for a content based on page-properties
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $fileReferenceSource
     * @param \RKW\RkwNewsletter\Domain\Model\Content $content
     * @return \RKW\RkwBasics\Domain\Model\FileReference
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createFileReference(FileReference $fileReferenceSource, Content $content): FileReference
    {

        // we switch to admin for BE-user for full file access - !!! BE CAREFUL WITH THIS !!!
        /** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUserAuthentication */
        $backendUserAuthentication = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $backendUserAuthentication->setWorkspace(0);
        
        $beUserTemp = $GLOBALS['BE_USER'];
        $GLOBALS['BE_USER'] = $backendUserAuthentication;
                
        /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReference */
        $fileReference = GeneralUtility::makeInstance(FileReference::class);
        $fileReference->setOriginalResource($fileReferenceSource->getOriginalResource());
        $fileReference->setFile($fileReferenceSource->getFile());
        
        $fileReference->setTableLocal($fileReferenceSource->getTableLocal());
        $fileReference->setTablenames('tt_content');
        $fileReference->setFieldName('image');
        $fileReference->setUidForeign($content->getUid());
        $fileReference->setPid($content->getPid());
        
        $this->fileReferenceRepository->add($fileReference);
        $this->persistenceManager->persistAll();

        // switch BE-user back to normal !!!
        $GLOBALS['BE_USER'] = $beUserTemp;
        
            $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Added fileReference with id=%s to content with uid=%s.',
                $fileReference->getUid(),
                $content->getUid()
            )
        );
    
        return $fileReference;
    }


    /**
     * Builds all contents for a topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function buildContents (Newsletter $newsletter, Topic $topic, Pages $page): bool
    {

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $sourcePagesList */
        $sourcePagesList = $this->pagesRepository->findByTopicNotIncluded($topic);
        if (count($sourcePagesList) > 0) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Pages $sourcePage */
            foreach ($sourcePagesList as $sourcePage) {

                // create content
                $content = $this->createContent($newsletter, $page, $sourcePage);

                // optional: add image
                try {
                    /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReference */
                    $fileReference = $sourcePage->getTxRkwnewsletterTeaserImage() ?: ($sourcePage->getTxRkwbasicsTeaserImage() ?: null);
                    if ($fileReference) {
                        $this->createFileReference($fileReference, $content);
                    }
                    
                } catch (\Exception $e) {
                    $this->getLogger()->log(
                        LogLevel::ERROR, 
                        sprintf(
                            'Can not add fileReference to content with id=%s of newsletter with id=%s. Error: %s',
                            $content->getUid(), 
                            $newsletter->getUid(), 
                            $e->getMessage()
                        )
                    );
                }
                
                // update timestamp
                $sourcePage->setTxRkwnewsletterIncludeTstamp(time());
                $this->pagesRepository->update($sourcePage);
            }
            
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Built contents of topic with id=%s of newsletter with id=%s.',
                    $topic->getUid(),
                    $newsletter->getUid()
                )
            );
            
            return true;
        } 
        
        $this->getLogger()->log(
            LogLevel::DEBUG, 
            sprintf(
                'No contents built for topic with id=%s of newsletter with id=%s.', 
                $topic->getUid(), 
                $newsletter->getUid()
            )
        );
        
        return false;
    }

    
    
    /**
     * Builds all pages for a topic and adds contents
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param array<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     * @return bool
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function buildPages (Newsletter $newsletter, Issue $issue, array $topics = []): bool
    {
        /** @var \RKW\RkwNewsletter\Manager\ApprovalManager $approvalManager */
        $approvalManager = $this->objectManager->get(ApprovalManager::class);
        
        if (
            (! $topics) 
            && ($newsletter->getTopic())
        ){
            $topics = $newsletter->getTopic()->toArray();
        }
        if (count($topics)) {
            foreach ($topics as $topic) {

                // Create page
                $page = $this->createPage($newsletter, $topic, $issue);

                // Build contents on that page
                $this->buildContents ($newsletter, $topic, $page);

                // Create approval for page
                $approvalManager->createApproval($topic, $issue, $page);

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Built page for topic with id=%s for issue with id=%s of newsletter with id=%s.',
                        $topic->getUid(),
                        $issue->getUid(),
                        $newsletter->getUid()
                    )
                );
            }

            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'No pages built for issue with id=%s of newsletter with id=%s. No topics defined for newsletter.',
                $issue->getUid(),
                $newsletter->getUid()
            )
        );

        return false;
    }

    
    /**
     * Builds an issue with all contents for the given newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param array<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     * @return bool
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function buildIssue (Newsletter $newsletter, array $topics = []): bool
    {

        // check for topics
        if (count($newsletter->getTopic())) {

            // create issue and set approval-status
            $issue = $this->createIssue($newsletter, intval($topics));
            try {
                $this->buildPages ($newsletter, $issue, $topics);
                $issue->setStatus(1);

            } catch (\Exception $e) {
                $issue->setStatus(99);
                $this->getLogger()->log(
                    LogLevel::ERROR,
                    sprintf(
                        'Error while trying to create an issue for newsletter with id=%s: %s',
                        $newsletter->getUid(),
                        $e->getMessage()
                    )
                );
            }

            // if topics are set, we have a manually created special-issue
            if (! count($topics)) {
                $newsletter->setLastIssueTstamp(time());
                $this->newsletterRepository->update($newsletter);
            } 

            $this->issueRepository->update($issue);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Built %s created issue for newsletter with id=%s.',
                    ($topics ? 'manually' : 'automatically'),
                    $newsletter->getUid()
                )
            );
            
            return true;
        } 

        $this->getLogger()->log(
            LogLevel::WARNING,
            sprintf(
                'No topics defined for newsletter with id=%s. No issue created.',
                $newsletter->getUid()
            )
        );
              
        return false;        
    }

    
    /**
     * Builds issues for all due newsletters
     *
     * @param int $tolerance
     * @param int $timestampNow
     * @return bool
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function buildAllIssues (int $tolerance = 0, int $timestampNow = 0): bool
    {

        $newsletterList = $this->newsletterRepository->findAllToBuildIssue($tolerance, $timestampNow);
        if (count($newsletterList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
            foreach ($newsletterList as $newsletter) {
                $this->buildIssue($newsletter);
            }

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Built issues for %s newsletters.',
                    count($newsletterList)
                )
            );
            
            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            'No issues built. No newsletter is due for a new issue.'
        );
        return false;
    }

    
    /**
     * Increases the level of the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval Issue $issue
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function increaseLevel (Issue $issue): bool
    {

        $stage = IssueStatus::getStage($issue);

        if (IssueStatus::increaseLevel($issue)) {
            $this->issueRepository->update($issue);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Increased level for issue id=%s in issue-stage %s.',
                    $issue->getUid(),
                    $stage
                )
            );

            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Did not increase level for issue id=%s.',
                $issue->getUid()
            )
        );

        return false;
    }
    

    /**
     * Increases the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function increaseStage (Issue $issue): bool
    {

        $stage = IssueStatus::getStage($issue);

        if (IssueStatus::increaseStage($issue)) {
            $this->issueRepository->update($issue);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Increased stage for issue id=%s in issue-stage %s.',
                    $issue->getUid(),
                    $stage
                )
            );

            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Did not increase stage for issue id=%s.',
                $issue->getUid()
            )
        );

        return false;
    }
    

    /**
     * Get the email-recipients for the approval based in the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return array
     */
    public function getMailRecipients(Issue $issue): array
    {

        $mailRecipients = [];
        $stage = IssueStatus::getStage($issue);

        if ($stage == IssueStatus::STAGE_RELEASE) {
            if (count($issue->getNewsletter()->getApproval()) > 0) {

                /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $beUser */
                foreach ($issue->getNewsletter()->getApproval()->toArray() as $beUser) {
                    if (GeneralUtility::validEmail($beUser->getEmail())) {
                        $mailRecipients[] = $beUser;
                    }
                }
            }

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Found %s recipients for issue id=%s in issue-stage %s.',
                    count($mailRecipients),
                    $issue->getUid(),
                    $stage
                )
            );
        }

        return $mailRecipients;
    }


    /**
     * Send info-mails or reminder-mails for outstanding confirmations
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return int
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function sendMails(Issue $issue): int
    {

        $stage = IssueStatus::getStage($issue);
        $level = IssueStatus::getLevel($issue);
        $isReminder = ($level == IssueStatus::LEVEL2);

        // get recipients - but only if stage and level match AND if valid recipients are found
        if (
              ($stage == IssueStatus::STAGE_RELEASE)
              && (count($recipients = $this->getMailRecipients($issue)))
        ) {

            if ($level != IssueStatus::LEVEL_DONE) {
                
                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    self::SIGNAL_FOR_SENDING_MAIL_RELEASE,
                    [$recipients, $issue, $isReminder]
                );

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Sending email for issue with id=%s in issue-stage %s and level %s.',
                        $issue->getUid(),
                        $stage,
                        $level
                    )
                );

                return 1;
            
            } else {
                
                // here we could implement an automatic approval
                // but we don't want this on here!
                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'Automatic confirmation for issue with id=%s in issue-stage %s and level %s triggered, but nothing done!',
                        $issue->getUid(),
                        $stage,
                        $level
                    )
                );
                
                return 2;
            }
        }    

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Issue with id=%s in issue-stage %s has no mail-recipients.',
                $issue->getUid(),
                $stage
            )
        );
        
        return 0;
    }



    /**
     * Check if all approvals are done and set status of the issue to "release" then
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processConfirmation (Issue $issue): bool
    {
        // only if status is "approval"
        if (
            ($issue->getStatus() != IssueStatus::STAGE_APPROVAL)
            && ($issue->getStatus() != IssueStatus::STAGE_RELEASE)
        ){
            return false;
        }
        
        // check if all approvals are done
        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        foreach ($issue->getApprovals() as $approval) {
            if (ApprovalStatus::getStage($approval) != ApprovalStatus::STAGE_DONE) {
                return false;
            }
        }
        
        // update if status is approval
        if ($issue->getStatus() == IssueStatus::STAGE_APPROVAL) {
            
            // update status to release
            $issue->setStatus(IssueStatus::STAGE_RELEASE);
            $this->issueRepository->update($issue);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Set stage to %s for issue with id=%s.',
                    IssueStatus::STAGE_RELEASE,
                    $issue->getUid()
                )
            );
        }

        // send mails and increase level for the next time
        if ($this->sendMails($issue) == 1) {
            $this->increaseLevel($issue);
            return true;
        }
        
        return false;       
    }

    /**
     * Check all issues if there is a release stage to check
     *
     * @param int $toleranceLevel2
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processAllConfirmations (int $toleranceLevel2): int
    {
        $issueList = $this->issueRepository->findAllForConfirmationByTolerance($toleranceLevel2);
        if (count($issueList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
            foreach ($issueList as $issue) {
                $this->processConfirmation($issue);
            }
        }

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Processed release status for %s issues.',
                count($issueList)
            )
        );
        
        return count($issueList);
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