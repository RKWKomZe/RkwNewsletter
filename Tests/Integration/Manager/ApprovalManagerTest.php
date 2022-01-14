<?php
namespace RKW\RkwNewsletter\Tests\Integration\Manager;

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
use RKW\RkwBasics\Domain\Model\FileReference;
use RKW\RkwBasics\Domain\Repository\FileReferenceRepository;
use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use RKW\RkwNewsletter\Domain\Repository\ContentRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use RKW\RkwNewsletter\Manager\ApprovalManager;
use RKW\RkwNewsletter\Manager\IssueManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * ApprovalManagerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalManagerTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ApprovalManagerTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Manager\ApprovalManager
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     */
    private $topicRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     */
    private $pagesRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     */
    private $approvalRepository;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                static::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->subject = $this->objectManager->get(ApprovalManager::class);

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function createApprovalCreatesApprovalAndAddsItToIssue()
    {

        /**
         * Scenario:
         *
         * Given a topic-object that is persisted
         * Given an issue-object that is persisted and belongs to that topic
         * Given a page-object that is persisted and belongs to the issue
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Approval is returned
         * Then the topic-property of this instance is set to the topic-object
         * Then the page-property of this instance is set to the page-object
         * Then the approval-object is persisted
         * Then the approval-object is added to the approvals-property of the issue-object
         * Then the changes on the issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $approval = $this->subject->createApproval($topic, $issue, $page);
        
        self::assertInstanceOf(Approval::class, $approval);
        self::assertEquals($topic, $approval->getTopic());
        self::assertEquals($page, $approval->getPage());

        /** @var  \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $approvals */
        $approvalsDb = $this->approvalRepository->findAll();
        self::assertCount(1, $approvalsDb);

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approvalDb = $approvalsDb->getFirst();
        self::assertEquals($approval, $approvalDb);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(10);

        self::assertCount(1, $issueDb->getApprovals());
        $issueDb->getApprovals()->rewind();
        self::assertEquals($approval, $issueDb->getApprovals()->current());

    }
    
    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
       parent::tearDown();
    }








}