<?php
namespace RKW\RkwNewsletter\Tests\Integration\Status;

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
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use RKW\RkwNewsletter\Status\PageStatus;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * PageStatusTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageStatusTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PageStatusTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_newsletter'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Status\PageStatus|null
     */
    private ?PageStatus $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    private ?IssueRepository $issueRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository|null
     */
    private ?TopicRepository $topicRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository|null
     */
    private ?PagesRepository $pagesRepository = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/constants.typoscript',
                'EXT:postmaster/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                static::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);

        $this->subject = $this->objectManager->get(PageStatus::class);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsDraft()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 0
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::DRAFT is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        self::assertEquals($this->subject::DRAFT, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::APPROVAL_1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(20);

        self::assertEquals($this->subject::APPROVAL_1, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has a value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::APPROVAL_2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(30);

        self::assertEquals($this->subject::APPROVAL_2, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsReleaseWhenStage2Done()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has a value for the allowedTstampStage1-property set
         * Given that approval-object has a value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::RELEASE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(40);

        self::assertEquals($this->subject::RELEASE, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsRelease()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 2
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::RELEASE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(50);

        self::assertEquals($this->subject::RELEASE, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsSending()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 3
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::SENDING is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(60);

        self::assertEquals($this->subject::SENDING, $this->subject::getStage($page));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 4
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::DONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(70);

        self::assertEquals($this->subject::DONE, $this->subject::getStage($page));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getApprovalThrowsExceptionOnNonMatchingTopic()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given a persisted topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object does not belong to the topic-object
         * When the method is called
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1644845316
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1644845316);

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(80);

        $this->subject::getApproval($issue, $topic);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getApprovalThrowsExceptionOnNonExistingApproval()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given a persisted topic-object
         * Given no persisted approval-object
         * When the method is called
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1644845316
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1644845316);

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(90);

        $this->subject::getApproval($issue, $topic);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getApprovalReturnsApprovalObject()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given a persisted topic-object
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object belongs to the topic-object
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Domain\Model\Approval is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(100);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(100);

        self::assertInstanceOf(
            \RKW\RkwNewsletter\Domain\Model\Approval::class,
            $this->subject::getApproval($issue, $topic)
        );
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
