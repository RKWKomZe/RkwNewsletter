<?php
namespace RKW\RkwNewsletter\Tests\Integration\Mailing;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwMailer\Cache\MailCache;
use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use RKW\RkwMailer\Domain\Repository\QueueRecipientRepository;
use RKW\RkwMailer\Utility\QueueMailUtility;
use RKW\RkwNewsletter\Domain\Model\FrontendUser;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Repository\BackendUserRepository;
use RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository;
use RKW\RkwNewsletter\Mailing\MailProcessor;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;


/**
 * MailProcessorTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailProcessorTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailProcessorTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter',
        'typo3conf/ext/rkw_registration',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Mailing\MailProcessor
     */
    private $subject;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     */
    private $queueMailRepository;

    /**
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     */
    private $queueRecipientRepository;
    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     */
    private $topicRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     */
    private $backendUserRepository;
    
    /**
     * @var \RKW\RkwMailer\Cache\MailCache
     */
    private $mailCache;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository queueMailRepository */
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);

        /** @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository queueRecipientRepository */
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository issueRepository */
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository topicRepository */
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);
        
        /** @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository backendUserRepository */
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);

        /** @var \RKW\RkwNewsletter\Mailing\MailProcessor $subject */
        $this->subject = $this->objectManager->get(MailProcessor::class);

        $this->mailCache = $this->objectManager->get(MailCache::class);
    }
   
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueThrowsExceptionIfMissingNewsletterObject()
    {
        /**
         * Scenario:
         *
         * Given a persisted issue-object Y that has no newsletter-object set
         * When method is called with the issue-object Y as parameter
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650541234
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650541234);
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        $this->subject->setIssue($issue);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueThrowsExceptionIfNonPersistedObject()
    {
        /**
         * Scenario:
         *
         * Given a new newsletter-object X
         * Given a new issue-object Y that belongs to newsletter-object X
         * When method is called with the issue-object Y as parameter
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650541236
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650541236);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = GeneralUtility::makeInstance(Issue::class);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = GeneralUtility::makeInstance(Newsletter::class);
        $issue->setNewsletter($newsletter);

        $this->subject->setIssue($issue);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setIssueThrowsExceptionIfConfigurationMissing()
    {
        /**
         * Scenario:
         *
         * Given a new newsletter-object X
         * Given that newsletter-object has no basic configuration-parameters set
         * Given a new issue-object Y that belongs to newsletter-object X
         * When method is called with the issue-object Y as parameter
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1651215173
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1651215173);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check270.xml');
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(270);

        $this->subject->setIssue($issue);

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsBasicConfigurationOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the setSettingsPid-property of the queueMail-object is set to the value set in the newsletter-object X
         * Then the issue-property of the queueMail-object is set to the title-property of the issue-object Y
         * Then the category-property of the queueMail-object is set the value "rkwNewsletter"
         * Then the returnPath-property of the queueMail-object is set to the returnPath-value set in the newsletter-object X
         * Then the replyToAddress-property of the queueMail-object is set to the replayMail-value set in the newsletter-object X
         * Then the replyToName-property of the queueMail-object is set to the senderName-value set in the newsletter-object X
         * Then the fromAddress-property of the queueMail-object is set to the senderMail-value set in the newsletter-object X
         * Then the fromName-property of the queueMail-object is set to the senderName-value set in the newsletter-object X
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getUid());
        self::assertEquals(2, $queueMail->getSettingsPid());
        self::assertEquals('rkwNewsletter', $queueMail->getCategory());
        self::assertEquals('return@testen.de', $queueMail->getReturnPath());
        self::assertEquals('reply@testen.de', $queueMail->getReplyToAddress());
        self::assertEquals('Test', $queueMail->getReplyToName());
        self::assertEquals('test@testen.de', $queueMail->getFromAddress());
        self::assertEquals('Test', $queueMail->getFromName());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsDefaultTemplatePathsOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given that newsletter-object has no settingsPid set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the layoutPaths-property of the queueMail-object contains the basic layout-paths
         * Then the layoutPaths-property of the queueMail-object contains the layout-path of the default-template
         * Then the layoutPaths-property of the queueMail-object contains the layout-paths of the set template of mewsletter-object X
         * Then the partialPaths-property of the queueMail-object contains the basic partial-paths
         * Then the partialPaths-property of the queueMail-object contains the partial-path of the default-template
         * Then the partialPaths-property of the queueMail-object contains the partial-paths of the set template of mewsletter-object X
         */
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $expectedLayoutPaths = [
            'EXT:rkw_newsletter/Resources/Private/Layouts/Newsletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}',
            'EXT:rkw_newsletter/Resources/Private/Layouts/Newsletter/Default',
            'EXT:rkw_newsletter/Resources/Private/Layouts/Newsletter/Managementletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}/Default',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}/Managementletter'
        ];
        $expectedPartialPaths = [
            'EXT:rkw_newsletter/Resources/Private/Partials/Newsletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}',
            'EXT:rkw_newsletter/Resources/Private/Partials/Newsletter/Default',
            'EXT:rkw_newsletter/Resources/Private/Partials/Newsletter/Managementletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}/Default',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}/Managementletter'
        ];

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getUid());
        self::assertEquals($expectedLayoutPaths, $queueMail->getLayoutPaths());
        self::assertEquals($expectedPartialPaths, $queueMail->getPartialPaths());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsTemplatePathsOfMailServiceBasedOnSettingsPid()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the layoutPaths-property of the queueMail-object contains the basic layout-paths
         * Then the layoutPaths-property of the queueMail-object contains the layout-path of the default-template
         * Then the layoutPaths-property of the queueMail-object contains the layout-paths of the set template of mewsletter-object X
         * Then the partialPaths-property of the queueMail-object contains the basic partial-paths
         * Then the partialPaths-property of the queueMail-object contains the partial-path of the default-template
         * Then the partialPaths-property of the queueMail-object contains the partial-paths of the set template of mewsletter-object X
         */
        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Check21.xml');
        $this->setUpFrontendRootPage(
            21,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Page21.typoscript',
            ]
        );
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(21);

        $expectedLayoutPaths = [
            'test/Resources/Private/Layouts/Newsletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}',
            'test/Resources/Private/Layouts/Newsletter/Default',
            'test/Resources/Private/Layouts/Newsletter/Managementletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}/Default',
            '{$plugin.tx_rkwnewsletter.view.newsletter.layoutRootPath}/Managementletter'
        ];
        $expectedPartialPaths = [
            'test/Resources/Private/Partials/Newsletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}',
            'test/Resources/Private/Partials/Newsletter/Default',
            'test/Resources/Private/Partials/Newsletter/Managementletter',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}/Default',
            '{$plugin.tx_rkwnewsletter.view.newsletter.partialRootPath}/Managementletter'
        ];

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getUid());
        self::assertEquals($expectedLayoutPaths, $queueMail->getLayoutPaths());
        self::assertEquals($expectedPartialPaths, $queueMail->getPartialPaths());
        self::assertEquals('Managementletter', $queueMail->getHtmlTemplate());
        self::assertEquals('Managementletter', $queueMail->getPlaintextTemplate());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsDefaultTemplate()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given that newsletter-object has no template set 
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the plaintextTemplate-property of the queueMail-object is set to the default template
         * Then the htmlTemplate-property of the queueMail-object is set to the default template
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);
        $issue->getNewsletter()->setTemplate('');

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals('Default', $queueMail->getHtmlTemplate());
        self::assertEquals('Default', $queueMail->getPlaintextTemplate());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsDefinedTemplate()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given that newsletter-object a template set that is not the default-template
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the plaintextTemplate-property of the queueMail-object is set to the default template
         * Then the htmlTemplate-property of the queueMail-object is set to the default template
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals('Managementletter', $queueMail->getHtmlTemplate());
        self::assertEquals('Managementletter', $queueMail->getPlaintextTemplate());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueDoesNotStartPipeliningOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then the pipeline-property of the queueMail-object is not set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByUid(1);

        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getUid());
        self::assertEquals(0, $queueMail->getPipeline());
    }
    
    
    /**
     * @test
     * @throws \Exception
     */
    public function setIssueDoesNotSetQueueMailProperty()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has no queueMail-object set
         * When method is called with the issue-object Y as parameter
         * Then a new queueMail-object is created
         * Then this new queueMail-object is added to the mailService
         * Then this queueMail-Object is not added to the issue-object Y
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        $this->subject->setIssue($issue);

        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getUid());
        self::assertNull($issue->getQueueMail());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueUsesExistingQueueMailForMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue-object Y has a queueMail-object Z set
         * When method is called with the issue-object Y as parameter
         * Then no new queueMail-object is created
         * Then the existing queueMail-object Z is passed to the mailService
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        $this->subject->setIssue($issue);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMails = $this->queueMailRepository->findAll();

        self::assertCount(1, $queueMails);
        self::assertEquals(50, $this->subject->getMailService()->getQueueMail()->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsIssueAndTopicsForContentLoader()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given three persisted topic-objects A, B, C that belong to the newsletter-object X
         * Given for topic-object A there is a page-object W that belongs to the issue-object Y
         * Given for topic-object B there is a page-object X that belongs to the issue-object Y
         * Given for topic-object C there is a page-object Y that belongs to the issue-object Y
         * When method is called with the issue-object Y as parameter
         * Then the issue Y is set to the contentLoader
         * Then the contentLoader has three topics set
         * Then the contentLoader has the topics A, B and C set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        $this->subject->setIssue($issue);

        self::assertEquals($issue, $this->subject->getContentLoader()->getIssue());

        $topics = $this->subject->getContentLoader()->getTopics();
        $topicsArray = $topics->toArray();
        self::assertCount(3, $topicsArray);
        self::assertEquals(90, $topicsArray[0]->getUid());
        self::assertEquals(91, $topicsArray[1]->getUid());
        self::assertEquals(92, $topicsArray[2]->getUid());

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setRecipientsThrowsExceptionIfNoIssueSet()
    {
        /**
         * Scenario:
         *
         * Given setIssue is not called before
         * When method is called
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650541235
         */

        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650541235);

        $this->subject->setRecipients();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setRecipientsReturnsTrueAndAddsRecipients()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted newsletter-object Y
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given this issue-object Y has no startTstamp-property set
         * Given two topic-objects A and B that belong to the newsletter-object X
         * Given two topic-objects C and D that belong to the newsletter-object Y
         * Given two frontendUser-objects K and L that have subscribed to the topic C at first
         * Given two frontendUser-objects K and L that have subscribed to the topic A in the second step
         * Given three frontendUser-objects K, M and N that have subscribed to the topic B
         * Given the frontendUser-object N has the priority-property set to true
         * Given one frontendUser-objects P that has not subscribed to a topic
         * Given one frontendUser-objects Q that has subscribed to a topic C
         * Given setIssue with the issue Y is called before
         * Given the status of the queueMail-object of the mailService is DRAFT (default)
         * When method is called
         * Then true is returned
         * Then the recipients-property of the issue Y returns an array
         * Then this array contains four items
         * Then this array contains the ids of the frontendUsers K, L, M and N
         * Then the frontendUser N is returned first
         * Then this recipient-list is persisted
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);
        $this->subject->setIssue($issue);
        
        self::assertTrue($this->subject->setRecipients());

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();
        
        $result = $this->subject->getIssue()->getRecipients();
        self::assertInternalType('array', $result);
        self::assertCount(4, $result);

        self::assertEquals(63, $result[0]);
        self::assertEquals(60, $result[1]);
        self::assertEquals(61, $result[2]);
        self::assertEquals(62, $result[3]);
        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setRecipientsReturnsTrueAndResetsRecipientList()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted newsletter-object Y
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given this issue-object Y has no startTstamp-property set
         * Given two topic-objects A and B that belong to the newsletter-object X
         * Given two topic-objects C and D that belong to the newsletter-object Y
         * Given two frontendUser-objects K and L that have subscribed to the topic C at first
         * Given two frontendUser-objects K and L that have subscribed to the topic A in the second step
         * Given three frontendUser-objects K, M and N that have subscribed to the topic B
         * Given the frontendUser-object N has the priority-property set to true
         * Given one frontendUser-objects P that has not subscribed to a topic
         * Given one frontendUser-objects Q that has subscribed to a topic C
         * Given setIssue with the issue Y is called before
         * Given the status of the queueMail-object of the mailService is DRAFT (default)
         * Given the method has been called before
         * When method is called
         * Then true is returned
         * Then the recipients-property of the issue Y returns an array
         * Then this array contains four items
         * Then this array contains the ids of the frontendUsers K, L, M and N
         * Then the frontendUser N is returned first
         * Then this recipient-list is persisted
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);
        $this->subject->setIssue($issue);
        $this->subject->setRecipients();

        self::assertTrue($this->subject->setRecipients());

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $result = $this->subject->getIssue()->getRecipients();
        self::assertInternalType('array', $result);
        self::assertCount(4, $result);

        self::assertEquals(63, $result[0]);
        self::assertEquals(60, $result[1]);
        self::assertEquals(61, $result[2]);
        self::assertEquals(62, $result[3]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setRecipientsReturnsFalseIfIssueAlreadyStartedWithSending()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted newsletter-object Y
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given this issue-object Y has no startTstamp-property set
         * Given two topic-objects A and B that belong to the newsletter-object X
         * Given two topic-objects C and D that belong to the newsletter-object Y
         * Given two frontendUser-objects K and L that have subscribed to the topic C at first
         * Given two frontendUser-objects K and L that have subscribed to the topic A in the second step
         * Given three frontendUser-objects K, M and N that have subscribed to the topic B
         * Given the frontendUser-object N has the priority-property set to true
         * Given one frontendUser-objects P that has not subscribed to a topic
         * Given one frontendUser-objects Q that has subscribed to a topic C
         * Given setIssue with the issue Y is called before
         * Given the status of the queueMail-object is SENDING
         * When method is called
         * Then false is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);
        $issue->setStartTstamp(time());
        $this->subject->setIssue($issue);
        
        self::assertFalse($this->subject->setRecipients());

    }
    
    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getSubscriptionHashReturnsExistingHash()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this frontendUser-object has a subscription hash set
         * When method is called with the frontendUser-object as parameter
         * Then a string is returned
         * Then this string is the stored hash from the database
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(70);
        
        $result = $this->subject->getSubscriptionHash($frontendUser);
        
        self::assertInternalType('string', $result);
        self::assertEquals('testhash', $result);
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getSubscriptionHashReturnsNewHash()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this frontendUser-object has no subscription hash set
         * When method is called with the frontendUser-object as parameter
         * Then a string is returned
         * Then a new hash is generated
         * Then this hash is persisted in the database
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(80);

        $result = $this->subject->getSubscriptionHash($frontendUser);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(80);
        
        self::assertEquals($result, $frontendUser->getTxRkwnewsletterHash());

    }
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsThrowsExceptionIfNoIssueSet()
    {
        /**
         * Scenario:
         *
         * Given setIssue is not called before
         * When method is called 
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650549470
         */

        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650549470);
   
        $this->subject->setTopics(new ObjectStorage());

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsSetsGivenTopicsToContentLoader()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given three persisted topic-objects A, B, C that belong to the newsletter-object X
         * Given for topic-object A there is a page-object W that belongs to the issue-object Y
         * Given for topic-object B there is a page-object X that belongs to the issue-object Y
         * Given for topic-object C there is a page-object Y that belongs to the issue-object Y
         * When method is called with the topics A and C as objectStorage
         * Then the contentLoader has two topics set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(92);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);
        $objectStorage->attach($topic2);
        
        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        $topics = $this->subject->getContentLoader()->getTopics();
        self::assertCount(2, $topics);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsSetsAllTopicsOfIssueToContentLoader()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given three persisted topic-objects A, B, C that belong to the newsletter-object X
         * Given for topic-object A there is a page-object W that belongs to the issue-object Y
         * Given for topic-object B there is a page-object X that belongs to the issue-object Y
         * Given for topic-object C there is a page-object Y that belongs to the issue-object Y
         * When method is called without parameter
         * Then the contentLoader has three topics set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        $this->subject->setIssue($issue);
        $this->subject->setTopics();

        $topics = $this->subject->getContentLoader()->getTopics();
        self::assertCount(3, $topics);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsSetsNoTopicsToContentLoader()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given three persisted topic-objects A, B, C that belong to the newsletter-object X
         * Given for topic-object A there is a page-object W that belongs to the issue-object Y
         * Given for topic-object B there is a page-object X that belongs to the issue-object Y
         * Given for topic-object C there is a page-object Y that belongs to the issue-object Y
         * When method is called without an empty objectStorage as parameter
         * Then the contentLoader has no topics set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        $topics = $this->subject->getContentLoader()->getTopics();
        self::assertCount(0, $topics);

    }
    
    //=============================================
    
    /**
     * @test
     * @throws \Exception
     */
    public function getSubjectThrowsExceptionIfNoIssueSet()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given setIssue is not set before
         * When method is called with the frontendUser as parameter
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650549470
         */

        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650549470);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(110);
    
        $this->subject->getSubject($frontendUser);

    }

  
    /**
     * @test
     * @throws \Exception
     */
    public function getSubjectReturnsCombinedSubject()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all basic configuration-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given none of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setIssue with the issue Y is called before
         * Given setTopics is called with topic B before
         * When method is called 
         * Then a string is returned
         * Then this string begins with the issue-title
         * Then this string ends with the headline of the first content of topic B which is no editorial
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(100);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicOne */
        $topic = $this->topicRepository->findByUid(101);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);
        
        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        $result = $this->subject->getSubject();
        self::assertEquals('Test – Content 71.2', $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailThrowsExceptionIfNoIssueSet()
    {
        /**
         * Scenario:
         *
         * Given setIssue is not called before
         * When method is called
         * Then an exception is thrown
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650608449
         */

        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650608449);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(110);

        $this->subject->sendMail($frontendUser);

    }
    

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailReturnsFalseIfNoContentsAvailable()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check120.xml');


        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(120);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(120);

        $this->subject->setIssue($issue);
        self::assertFalse($this->subject->sendMail($frontendUser));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailReturnsTrueIfContentsAvailable()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic B
         * Given that frontendUser has a valid email set
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then true is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check130.xml');


        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(130);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(130);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailReturnsFalseIfEmailMissing()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic B
         * Given that frontendUser has no valid email set
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');


        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(140);

        $this->subject->setIssue($issue);
        self::assertFalse($this->subject->sendMail($frontendUser));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailAddsMarkerToQueueRecipient()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic B
         * Given that frontendUser has a valid email set
         * Given that frontendUser has a subscription-hash set
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then true is returned
         * Then a queueRecipient-object is created
         * Then this queueRecipient-object has a marker-array with four items set
         * Then this queueRecipient-object has the issue-object Y set as reduced marker
         * Then this queueRecipient-object has the topic-object B set as reduced marker-array
         * Then this queueRecipient-object has the subscription-hash of the frontendUser set as string-marker
         * Then this queueRecipient-object has the settings of rkwNewsletter set as normal marker-array
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(150);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(150);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);
        
        $marker = $queueRecipient->getMarker();
        self::assertInternalType('array', $marker);
        self::assertCount(4, $marker);

        self::assertEquals('RKW_MAILER_NAMESPACES RKW\RkwNewsletter\Domain\Model\Issue:150', $marker['issue']);
        self::assertEquals('RKW_MAILER_NAMESPACES_ARRAY RKW\RkwNewsletter\Domain\Model\Topic:151', $marker['topics']);
        self::assertEquals('HashMeIfYouCan', $marker['hash']);
        self::assertInternalType('array', $marker['settings']);
        self::assertEquals('302400', $marker['settings']['reminderApprovalStage1']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersDefaultEditorial()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains the default editorial 
         * Then the mailCache for html contains the default editorial
         * Then the mailCache for plaintext does not contain the editorial of topic A
         * Then the mailCache for html does not contain  the editorial of topic A
         * Then the mailCache for plaintext does not contain the editorial of topic B
         * Then the mailCache for html does not contain the editorial of topic B   
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(160);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);
        
        self::assertContains(
            'untenstehend finden Sie Neuigkeiten zu Projekten, Veröffentlichungen und Veranstaltungen des RKW Kompetenzzentrums', 
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'untenstehend finden Sie Neuigkeiten zu Projekten, Veröffentlichungen und Veranstaltungen des RKW Kompetenzzentrums',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 160',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 160',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 161',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 161',
            $this->mailCache->getHtmlBody($queueRecipient)
        );        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersTopicEditorial()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A only
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext does not contain the default editorial
         * Then the mailCache for html does not contain the default editorial
         * Then the mailCache for plaintext contains the editorial of topic A
         * Then the mailCache for html contains the editorial of topic A
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check170.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(170);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(170);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertNotContains(
            'untenstehend finden Sie Neuigkeiten zu Projekten, Veröffentlichungen und Veranstaltungen des RKW Kompetenzzentrums',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'untenstehend finden Sie Neuigkeiten zu Projekten, Veröffentlichungen und Veranstaltungen des RKW Kompetenzzentrums',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Test the editorial 170',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Test the editorial 170',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersContents()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains all contents of topic A
         * Then the mailCache for html contains all contents of topic A
         * Then the mailCache for plaintext does not contain the editorial of topic A
         * Then the mailCache for html does not contain the editorial of topic A
         * Then the mailCache for plaintext contains all contents of topic B
         * Then the mailCache for html contains all contents of topic B
         * Then the mailCache for plaintext does not contain the editorial of topic B
         * Then the mailCache for html does not contain the editorial of topic B 
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(160);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            '',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 160',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 160',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.3',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.3',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.4',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 160.4',
            $this->mailCache->getHtmlBody($queueRecipient)
        );        
        

        self::assertNotContains(
            'Test the editorial 161',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Test the editorial 161',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 161.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 161.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 161.3',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 161.3',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersContentsOfSpecialTopicForAllRecipients()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted topic-object C that belongs to the newsletter-object X
         * Given topic B is marked as special
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and C
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains all contents of topic B
         * Then the mailCache for html contains all contents of topic B
         * Then the mailCache for plaintext contains the editorial of topic B
         * Then the mailCache for html contains the editorial of topic B
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(240);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(240);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            'Test the editorial 241',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Test the editorial 241',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 241.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 241.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 241.3',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 241.3',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersSubscriptionList()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains the text for the subscription-list
         * Then the mailCache for html contains the text for the subscription-list
         * Then the mailCache for plaintext contains the label of topic A
         * Then the mailCache for html contains the label of topic A
         * Then the mailCache for plaintext contains the label of topic B
         * Then the mailCache for html contains the label of topic B
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(160);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            'Sie haben folgende Themen abonniert',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Sie haben folgende Themen abonniert',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Topic 160',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Topic 160',
            $this->mailCache->getHtmlBody($queueRecipient)
        );     
        self::assertContains(
            'Topic 161',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Topic 161',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersSubscriptionLink()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains the subscription-pid
         * Then the mailCache for html contains the subscription-pid
         * Then the mailCache for plaintext contains the subscription-hash
         * Then the mailCache for html contains the subscription-hash
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(160);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

         self::assertContains(
             'http%3A%2F%2Fwww.rkw-kompetenzzentrum.rkw.local%2Findex.php%3Fid%3D2%26',
             $this->mailCache->getPlaintextBody($queueRecipient)
         );
        self::assertContains(
            'http%3A%2F%2Fwww.rkw-kompetenzzentrum.rkw.local%2Findex.php%3Fid%3D2%26',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    
        self::assertContains(
            '%26tx_rkwnewsletter_subscription%255Bhash%255D%3DHashMeIfYouCan',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            '%26tx_rkwnewsletter_subscription%255Bhash%255D%3DHashMeIfYouCan',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersDefaultSalutation()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has no names set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains default salutation
         * Then the mailCache for html contains efault salutation
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(160);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            'Hallo,',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Hallo,',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailRendersCustomSalutation()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser
         * Given that frontendUser has subscribed to topic A and B
         * Given that frontendUser has a valid email set
         * Given the frontendUser has a first-name set
         * Given the frontendUser has a last-name set
         * Given the frontendUser has a gender set
         * Given that frontendUser has a subscription-hash set
         * Given that frontendUser has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * When method is called with the frontendUser as parameter
         * Then false is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains default salutation
         * Then the mailCache for html contains efault salutation
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(180);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(180);

        $this->subject->setIssue($issue);
        self::assertTrue($this->subject->sendMail($frontendUser));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            'Herr Mustermann,',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Herr Mustermann,',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }
    
    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailThrowsExceptionIfNoIssueSet()
    {
        /**
         * Scenario:
         *
         * Given setIssue is not called before
         * When method is called
         * Then an exception is thrown
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1650629464
         */

        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1650629464);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(190);

        $this->subject->sendTestMail($backendUser);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailReturnsFalseIfNoContentsAvailable()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setTopic with topic A is called before
         * Given setIssue with the issue Y is called before
         * When method is called with a valid email-address as parameter
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check200.xml');


        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(200);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(200);
        
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);
        
        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertFalse($this->subject->sendTestMail('test@rkw.de'));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailReturnsTrueIfContentsAvailable()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setTopic with topic B is called before
         * Given that backendUser has a valid email set
         * Given setIssue with the issue Y is called before
         * When method is called with a valid email-address as parameter
         * Then true is returned
         */
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(210);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(211);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);
        
        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->sendTestMail('test@rkw.de'));
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailReturnsFalseIfEmailInvalid()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted backendUser
         * Given setTopic with topic B is called before
         * Given setIssue with the issue Y is called before
         * When method is called with an invalid email-address as parameter
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check220.xml');


        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(220);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(221);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        
        self::assertFalse($this->subject->sendTestMail('test'));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailAddsMarkerToQueueRecipient()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setTopic with topic B is called before
         * Given setIssue with the issue Y is called before
         * When method is called with a valid email-address as parameter
         * Then true is returned
         * Then a queueRecipient-object is created
         * Then this queueRecipient-object has a marker-array with three items set
         * Then this queueRecipient-object has the issue-object Y set as reduced marker
         * Then this queueRecipient-object has the topic-object B set as reduced marker-array
         * Then this queueRecipient-object has no subscription-hash as marker set
         * Then this queueRecipient-object has the settings of rkwNewsletter set as normal marker-array
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(210);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(211);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->sendTestMail('test@rkw.de'));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        $marker = $queueRecipient->getMarker();
        self::assertInternalType('array', $marker);
        self::assertCount(3, $marker);

        self::assertEquals('RKW_MAILER_NAMESPACES RKW\RkwNewsletter\Domain\Model\Issue:210', $marker['issue']);
        self::assertEquals('RKW_MAILER_NAMESPACES_ARRAY RKW\RkwNewsletter\Domain\Model\Topic:211', $marker['topics']);
        self::assertNull($marker['hash']);
        self::assertInternalType('array', $marker['settings']);
        self::assertEquals('302400', $marker['settings']['reminderApprovalStage1']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailRendersDummySalutation()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setTopic with topic B is called before
         * Given setIssue with the issue Y is called before
         * When method is called with a valid email-address as parameter
         * Then true is returned
         * Then a queueRecipient-object is created
         * Then the mailCache for plaintext contains default salutation
         * Then the mailCache for html contains efault salutation
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(210);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(211);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->sendTestMail('test@rkw.de'));

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        // we have to trigger the rendering manually here - for testing only!
        $this->subject->getMailService()->getMailer()->renderTemplates(
            $this->subject->getMailService()->getQueueMail(),
            $queueRecipient
        );
        
        self::assertContains(
            'Hallo Prof. Dr. Dr. Musterfrau,',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Hallo Prof. Dr. Dr. Musterfrau,',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
    }
    
    
    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsSetsStartTsstamp()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then the startTstamp-property of the issue-object Y is set to the current time
         * Then the sentTstamp-property of the issue-object Y is not set
         * Then this changes to issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        self::assertTrue($this->subject->sendMails(2));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        
        self::assertGreaterThanOrEqual(time() - 5, $issue->getStartTstamp());
        self::assertEquals(0, $issue->getSentTstamp());
    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsSetsSentTsstampAndStatusDone()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 4
         * Then true is returned
         * When the method is called with limit-parameter 4
         * Then false is returned
         * Then the pipeline-property of the queueMail-object is set to false
         * Then the startTstamp-property of the issue-object Y is set to the current time
         * Then the sentTstamp-property of the issue-object Y is set to the current time
         * Then the status-property of the issue-object Y is set to IssueStatus::STAGE_DONE
         * Then the lastSentTstamp-property of the newsletter-object X is set to the current time
         * Then this changes to issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        // --------------------------------
        // First call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(4));

        // --------------------------------
        // Second call
        // --------------------------------
        self::assertFalse($this->subject->sendMails(4));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        
        self::assertGreaterThanOrEqual(time() - 5, $issue->getStartTstamp());
        self::assertGreaterThanOrEqual(time() - 5, $issue->getSentTstamp());
        self::assertEquals(IssueStatus::STAGE_DONE, $issue->getStatus());
        self::assertGreaterThanOrEqual(time() - 5, $issue->getNewsletter()->getLastSentTstamp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsSetsTypeOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then the type-property of the queueMail-object is set to 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        self::assertTrue($this->subject->sendMails(2));
        self::assertEquals(1, $this->subject->getMailService()->getQueueMail()->getType());
    }
    
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsStartsPipeliningOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then the pipeline-property of the queueMail-object is set to true
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        self::assertTrue($this->subject->sendMails(2));
        self::assertTrue($this->subject->getMailService()->getQueueMail()->getPipeline());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsStopsPipeliningOfMailService()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 4
         * Then true is returned
         * Then the pipeline-property of the queueMail-object is set to true
         * When the method is called with limit-parameter 2
         * Then false is returned
         * Then the pipeline-property of the queueMail-object is set to false
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        // --------------------------------
        // First call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(4));
        self::assertTrue($this->subject->getMailService()->getQueueMail()->getPipeline());

        // --------------------------------
        // Second call
        // --------------------------------
        self::assertFalse($this->subject->sendMails(4));
        self::assertFalse($this->subject->getMailService()->getQueueMail()->getPipeline());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsRemovesRecipientsEvenIfNonExistent()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then the recipient-property of the issue-object is an array
         * Then this array has two items
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then the recipient-property of the issue-object is an array
         * Then this array is empty
         * Then this changes to issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        // --------------------------------
        // First call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        
        self::assertInternalType('array', $issue->getRecipients());
        self::assertCount(2, $issue->getRecipients());

        // --------------------------------
        // Second call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        
        self::assertInternalType('array', $issue->getRecipients());
        self::assertCount(0, $issue->getRecipients());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReusesQueueMail()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given that issue has four fictive recipients in the recipient-property set
         * Given setIssue with the issue Y is called before
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then no new queueMail-object is created
         * Then the queueMail-object of the mailService is set to the corresponding issue
         * Then this changes to issue-object are persisted
         * When the method is called with limit-parameter 2
         * Then true is returned
         * Then no new queueMail-object is created
         * Then the same queueMail-object of the mailService is used again
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $this->subject->setIssue($issue);

        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->subject->getMailService()->getQueueMail();
        $count = $this->queueMailRepository->findAll()->count();

        // --------------------------------
        // First call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));
        self::assertCount($count, $this->queueMailRepository->findAll()->toArray());

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        self::assertEquals($queueMail->getUid(), $issue->getQueueMail()->getUid());
        self::assertEquals($queueMail->getUid(), $this->subject->getMailService()->getQueueMail()->getUid());

        // --------------------------------
        // Second call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));
        self::assertCount($count, $this->queueMailRepository->findAll());

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        self::assertEquals($queueMail->getUid(), $issue->getQueueMail()->getUid());
        self::assertEquals($queueMail->getUid(), $this->subject->getMailService()->getQueueMail()->getUid());

    }

    

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsRendersContentsAccordingToSubscriptions()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given a persisted frontendUser 1
         * Given that frontendUser 1 has subscribed to topic A and B
         * Given that frontendUser 1 has a subscription-hash set
         * Given that frontendUser 1 has "de" set as language-key
         * Given a persisted frontendUser 2
         * Given that frontendUser 2 has subscribed to topic A
         * Given that frontendUser 2 has a subscription-hash set
         * Given that frontendUser 2 has "de" set as language-key
         * Given a persisted frontendUser 3
         * Given that frontendUser 3 has subscribed to topic B
         * Given that frontendUser 3 has a subscription-hash set
         * Given that frontendUser 3 has "de" set as language-key
         * Given a persisted frontendUser 4
         * Given that frontendUser 4 has subscribed to topic B
         * Given that frontendUser 4 has a subscription-hash set
         * Given that frontendUser 4 has "de" set as language-key
         * Given setIssue with the issue Y is called before
         * Given setRecipients is called before
         * When method is called with limit 2
         * Then true is returned
         * Then two queueRecipient-object One and Two are created for the frontendUsers 1 and 2
         * Then the contents of topic A and B are rendered for the queueRecipient-object One
         * Then the contents of topic A only are rendered for the queueRecipient-object Two
         * When method is called with limit 1 a second time
         * Then true is returned
         * Then one queueRecipient-object is created for the frontendUser 3
         * Then the contents of topic B are rendered for the queueRecipient-object
         * When method is called with limit 2 a third time
         * Then true is returned
         * Then one queueRecipient-object is created created for the frontendUser 4
         * Then the contents of topic B are rendered for the queueRecipient-object
         * When method is called with limit 2 a fourth time
         * Then false is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check230.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(230);
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
        self::assertCount(0, $this->queueMailRepository->findAll());
                
        $this->subject->setIssue($issue);
        $this->subject->setRecipients();

        // --------------------------------
        // First call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));
        self::assertCount(2, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);
        
        self::assertContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );        
        
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(2);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );

        // --------------------------------
        // Second call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(1));
        self::assertCount(3, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(3);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        
        // --------------------------------
        // third call
        // --------------------------------
        self::assertTrue($this->subject->sendMails(2));
        self::assertCount(4, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(4);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );

        // --------------------------------
        // Fourth call
        // --------------------------------
        self::assertFalse($this->subject->sendMails(2));
        
    }

    //=============================================

    
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendTestMailsWorksThroughRecipients()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given that newsletter-object has all relevant mail-parameters set
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setIssue with the issue Y is called before         
         * Given setTopic with topic B is called before
         * Given three email-addresses
         * Given one of the three email-addresses is invalid
         * When method is called with this email-addresses as comma-separated string
         * Then true is returned
         * Then two queueRecipient-objects
         * Then the contents of topic B only are rendered for the two queueRecipient-objects
         * Then the pipeline-property of the mailService is set to false
         * Then the status-property of the issue Y is not set to STAGE_DONE
         * Then the sentTstamp-property of the issue Y is zero
         * Then the lastSendTimestamp-property of the newsletter X is zero
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(250);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(251);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->sendTestMails('test,test@rkw.de,test1@rkw.de'));
        self::assertCount(2, $this->queueRecipientRepository->findAll());

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(1);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        // we have to trigger the rendering manually here - for testing only!
        $this->subject->getMailService()->getMailer()->renderTemplates(
            $this->subject->getMailService()->getQueueMail(),
            $queueRecipient
        );
        
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );

        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findByUid(2);
        self::assertInstanceOf(QueueRecipient::class, $queueRecipient);

        // we have to trigger the rendering manually here - for testing only!
        $this->subject->getMailService()->getMailer()->renderTemplates(
            $this->subject->getMailService()->getQueueMail(),
            $queueRecipient
        );
        
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertNotContains(
            'Content 230.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getPlaintextBody($queueRecipient)
        );
        self::assertContains(
            'Content 231.2',
            $this->mailCache->getHtmlBody($queueRecipient)
        );
        
        self::assertEquals(false, $this->subject->getMailService()->getQueueMail()->getPipeline());
        self::assertNotEquals(IssueStatus::STAGE_DONE, $issue->getStatus());
        self::assertEquals(0, $issue->getSentTstamp());
        self::assertEquals(0, $issue->getNewsletter()->getLastSentTstamp());
    }

    //=============================================
    

    /**
     * TearDown
     */
    protected function tearDown()
    {
        $this->mailCache->clearCache();
        parent::tearDown();
    }

}