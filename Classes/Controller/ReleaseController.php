<?php

namespace RKW\RkwNewsletter\Controller;
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

use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Mailing\ContentLoader;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ReleaseController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ReleaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_FOR_SENDING_MAIL_TEST = 'sendTestMail';


    /**
     * newsletterRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;

    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;

    /**
     * approvalRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository;

    /**
     * topicRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository;

    /**
     * pagesRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * backendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * Mail Service
     *
     * @var \RKW\RkwMailer\Service\MailService
     * @inject
     */
    protected $mailService;

    /**
     * IssueManager
     *
     * @var \RKW\RkwNewsletter\Manager\IssueManager
     * @inject
     */
    protected $issueManager;

    /**
     * ApprovalManager
     *
     * @var \RKW\RkwNewsletter\Manager\ApprovalManager
     * @inject
     */
    protected $approvalManager;


    /**
     * Validation Helper
     *
     * @var \RKW\RkwNewsletter\Helper\Validator
     * @inject
     */
    protected $validatorHelper;



  
    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction(): void
    {
        $issuesOpenApprovalStage1List = $this->issueRepository->findAllToApproveOnStage1();
        $issuesOpenApprovalStage2List = $this->issueRepository->findAllToApproveOnStage2();

        $this->view->assignMultiple(
            [
                'issuesOpenApprovalStage1List' => $issuesOpenApprovalStage1List,
                'issuesOpenApprovalStage2List' => $issuesOpenApprovalStage2List
            ]
        );
    }


    /**
     * action approve
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @ignorevalidation $approval
     */
    public function approveAction(Approval $approval): void
    {

        if ($this->approvalManager->increaseStage($approval)) {

            $this->addFlashMessage(LocalizationUtility::translate(
                'releaseController.message.approvalSuccessful',
                'rkw_newsletter'
            ), '', FlashMessage::OK);

        } else {
            $this->addFlashMessage(LocalizationUtility::translate(
                'releaseController.error.unexpected',
                'rkw_newsletter'
            ), '', FlashMessage::ERROR);
        }

        $this->redirect('list');
    }


    /**
     * action defer
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function deferAction(Issue $issue): void
    {

        $issue->setStatus(98);
        $this->issueRepository->update($issue);

        $this->addFlashMessage(LocalizationUtility::translate(
            'releaseController.message.issueDeferredSuccessfully',
            'rkw_newsletter'
        ), '', FlashMessage::OK);


        $this->redirect('list');
    }


    /**
     * action createIssueList
     *
     * @return void
     */
    public function createIssueListAction(): void
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(intval($GLOBALS['BE_USER']->user['uid']));

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $newsletters */
        $newsletters = $this->newsletterRepository->findAll();
        $this->view->assignMultiple(
            [
                'newsletters'  => $newsletters,
                'backendUser' => $backendUser,
            ]
        );
    }


    /**
     * action createIssue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @param \RKW\RkwNewsletter\Domain\Model\Topic|null $topic
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createIssueAction(Newsletter $newsletter, Topic $topic = null): void
    {

        // take all topics - or the one given
        $topicArray = $newsletter->getTopic()->toArray();
        if ($topic) {
            $topicArray = [$topic];
        }

        $this->issueManager->buildIssue($newsletter,$topicArray);
        
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'releaseController.message.issueCreated',
                'rkw_newsletter'
            )
        );

        $this->redirect("createIssueList");
    }
    

    /**
     * action testList
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function testListAction(): void
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(intval($GLOBALS['BE_USER']->user['uid']));

        $issues = $this->issueRepository->findAllToApproveOrRelease();
        $this->view->assignMultiple(
            [
                'issues'      => $issues,
                'backendUser' => $backendUser,
            ]
        );
    }


    /**
     * action test
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param string $emails
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @param string $title
     * @param int myTopicsOnly
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @ignorevalidation $issue
     */
    public function testAction(Issue $issue, string $emails, Topic $topic = null, string $title = '') {

        $emailArray = GeneralUtility::trimExplode(',', $emails);
        foreach ($emailArray as $email) {
            $validateEmail = $this->validatorHelper->email($email);
            if ($validateEmail->hasErrors()) {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'releaseController.error.emailIncorrect',
                        'rkw_newsletter',
                        [$email]
                    ),
                    '',
                    FlashMessage::ERROR
                );

                $this->forward("testList");
            }
        }

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user['uid']);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $contentLoader */
        $contentLoader = $this->objectManager->get(ContentLoader::class, $issue);
        
        $topics = [];
        if ($topic) {
            $topics[] = $topic;
        } else {
            $topics = $contentLoader->getTopics();
            shuffle($topics);
        }
        
        // get first content-element for subject
        $contentLoader->setTopics($topics);
        $firstHeadline = $contentLoader->getFirstHeadline();
        $subject = ($title ?: $issue->getTitle()) . ($firstHeadline ? (' – '. $firstHeadline) : '');
   
        // send mail
        $this->getSignalSlotDispatcher()->dispatch(
            __CLASS__, 
            self::SIGNAL_FOR_SENDING_MAIL_TEST, 
            [
                $issue,
                $backendUser, 
                $emailArray,
                $topics, 
                $subject
            ]
        );

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'releaseController.message.testMailSent',
                'rkw_newsletter'
            )
        );

        $this->redirect("testList");
    }
    
    

    /**
     * action sendList
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $confirmIssue
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendListAction($confirmIssue = null)
    {

        $issues = $this->issueRepository->findAllToSendByBackendUser(intval($GLOBALS['BE_USER']->user['uid']));
        $this->view->assignMultiple(
            [
                'issues'          => $issues,
                'confirmIssue'    => $confirmIssue,
                'backendUserId'   => intval($GLOBALS['BE_USER']->user['uid']),
                'backendUserName' => $GLOBALS['BE_USER']->user['realName'],
            ]
        );
    }

    /**
     * action sendConfirm
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param string $title
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @ignorevalidation $issue
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendConfirmAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue = null, $title = null)
    {

        // check for issue
        if (! $issue) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'releaseController.error.selectIssue',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );

            $this->redirect('sendList');
            //===
        }

        /*
        // check title
        if (
            ($title == $issue->getTitle())
            || (
                (strlen($title ) < 40)
                || (strlen($title ) > 60)
            )
        ) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'releaseController.warning.checkTitle',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING
            );
        }*/

        // set title
        $issue->setTitle($title);
        $this->issueRepository->update($issue);


        $this->view->assignMultiple(
            [
                'issue' => $issue,
            ]
        );

    }


    /**
     * action send
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param string $title
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @ignorevalidation $issue
     * @throws \Exception
     */
    public function sendAction(Issue $issue, string $title = ''): void
    {
        if ($issue->getStatus() == IssueStatus::STAGE_RELEASE) {
            
            if ($title) {
                $issue->setTitle($title);
                $this->issueRepository->update($issue);
            }

            $this->issueManager->increaseStage($issue);
            
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'releaseController.message.sendingStarted',
                    'rkw_newsletter'
                )
            );
        }
        
        $this->redirect('sendList');
    }


    /**
     * Returns SignalSlotDispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        if (!$this->signalSlotDispatcher) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        }

        return $this->signalSlotDispatcher;
        //===
    }
}