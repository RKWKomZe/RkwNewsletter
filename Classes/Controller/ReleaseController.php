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
use RKW\RkwNewsletter\Domain\Repository\BackendUserRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Mailing\MailProcessor;
use RKW\RkwNewsletter\Manager\ApprovalManager;
use RKW\RkwNewsletter\Manager\IssueManager;
use RKW\RkwNewsletter\Status\IssueStatus;
use RKW\RkwNewsletter\Validation\EmailValidator;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ReleaseController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ReleaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected NewsletterRepository $newsletterRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected IssueRepository $issueRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected BackendUserRepository $backendUserRepository;


    /**
     * @var \RKW\RkwNewsletter\Manager\IssueManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected IssueManager $issueManager;


    /**
     * @var \RKW\RkwNewsletter\Manager\ApprovalManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ApprovalManager $approvalManager;


    /**
     * @var \RKW\RkwNewsletter\Mailing\MailProcessor
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected MailProcessor $mailProcessor;


    /**
     * @var \RKW\RkwNewsletter\Validation\EmailValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected EmailValidator $emailValidator;


    /**
     * Show a list of all outstanding confirmations
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function confirmationListAction(): void
    {
        $issuesOpenApprovalStage1List = $this->issueRepository->findAllToApproveOnStage1();
        $issuesOpenApprovalStage2List = $this->issueRepository->findAllToApproveOnStage2();
        $issuesReadyToStart = $this->issueRepository->findAllToStartSending();

        $this->view->assignMultiple(
            [
                'issuesOpenApprovalStage1List' => $issuesOpenApprovalStage1List,
                'issuesOpenApprovalStage2List' => $issuesOpenApprovalStage2List,
                'issuesReadyToStart' => $issuesReadyToStart
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
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("approval")
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

        $this->redirect('confirmationList');
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


        $this->redirect('confirmationList');
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

        $this->redirect('createIssueList');
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

        $issues = $this->issueRepository->findAllForTestSending();
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
     * @param \RKW\RkwNewsletter\Domain\Model\Issue  $issue
     * @param string $emails
     * @param \RKW\RkwNewsletter\Domain\Model\Topic|null $topic
     * @param string $title
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \RKW\RkwNewsletter\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("issue")
     */
    public function testSendAction(Issue $issue, string $emails, Topic $topic = null, string $title = ''): void {

        $emailArray = GeneralUtility::trimExplode(',', $emails);
        foreach ($emailArray as $email) {

            /** @var \TYPO3\CMS\Extbase\Error\Result $validateEmail */
            $validateEmail = $this->emailValidator->email($email);
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

        // update title, and persist it
        $issue->setTitle($title);
        $this->issueRepository->update($issue);

        // set issue and topics
        $this->mailProcessor->setIssue($issue);
        $this->mailProcessor->setTopics();

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        if ($topic) {
            $objectStorage->attach($topic);
            $this->mailProcessor->setTopics($objectStorage);
        }

        // send mail
        if ($this->mailProcessor->sendTestMails($emails)) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'releaseController.message.testMailSent',
                    'rkw_newsletter'
                )
            );
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'releaseController.error.testMail',
                    'rkw_newsletter'
                ),
                '',
                FlashMessage::ERROR
            );
        }

        $this->redirect('testList');
    }


    /**
     * action sendList
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue|null $confirmIssue
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendListAction(Issue $confirmIssue = null): void
    {

        $issues = $this->issueRepository->findAllToStartSending();
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
     * @param \RKW\RkwNewsletter\Domain\Model\Issue|null $issue
     * @param string $title
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("issue")
     */
    public function sendConfirmAction(Issue $issue = null, string $title = ''): void
    {

        // check for issue
        if (! $issue) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'releaseController.error.selectIssue',
                    'rkw_newsletter'
                ),
                '',
                FlashMessage::ERROR
            );

            $this->redirect('sendList');
        }

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
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("issue")
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

}
