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
use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use RKW\RkwNewsletter\Manager\ApprovalManager;
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        
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
     * @test
     * @throws \Exception
     */
    public function increaseLevelSetsFirstLevelForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage1-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(80);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(80);
        self::assertGreaterThan(0, $approvalDb->getSentInfoTstampStage1());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelSetSecondLevelForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * When the method is called
         * Then true is returned
         * Then the sentTeminderTstampStage1-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(90);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(90);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage1());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(100);

        $result = $this->subject->increaseLevel($approval);
        self::assertFalse($result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelSetsFirstLevelForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(110);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(110);
        self::assertGreaterThan(0, $approvalDb->getSentInfoTstampStage2());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelSetSecondLevelForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * When the method is called
         * Then true is returned
         * Then the sentReminderTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(120);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(120);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage2());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check130.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(130);

        $result = $this->subject->increaseLevel($approval);
        self::assertFalse($result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage1-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(140);

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(140);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(150);

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(150);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsFalseForStagesAboveStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has a value for the allowedTstampStage2-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(160);

        $result = $this->subject->increaseStage($approval);
        self::assertFalse($result);

    }
    
    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalForStageReturnsEmptyArray()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has no approval-admins set
         * When the method is called
         * Then an empty array is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(20);

        self::assertEmpty($this->subject->getMailRecipientsForApproval($approval));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalReturnsRecipientsForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the two allowedTstampStage-properties set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has two approval-be-users for stage 1 set
         * Given that topic-object has two approval-be-users for stage 2 set
         * When the method is called
         * Then an array is returned
         * Then this array contains the two be-users for stage 1
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(30);

        $result = $this->subject->getMailRecipientsForApproval($approval);
        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(30, $result[0]->getUid());
        self::assertEquals(31, $result[1]->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalReturnsRecipientsForStage1AndChecksForEmail()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the two allowedTstampStage-properties set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has two approval-be-users for stage 1 set
         * Given one of the approval-be-users for stage 1 has an invalid email-address set
         * Given that topic-object has two approval-be-users for stage 2 set
         * When the method is called
         * Then an array is returned
         * Then this array contains the one be-user for stage 1
         * Then the be-user with the wrong email-address is not included in the array
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(40);

        $result = $this->subject->getMailRecipientsForApproval($approval);
        self::assertInternalType('array', $result);
        self::assertCount(1, $result);
        self::assertEquals(40, $result[0]->getUid());
        self::assertEquals('test@rkw.de', $result[0]->getEmail());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalReturnsRecipientsForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has the allowedTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has two approval-be-users for stage 1 set
         * Given that topic-object has two approval-be-users for stage 2 set
         * When the method is called
         * Then an array is returned
         * Then this array contains the two be-users for stage 2
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(50);

        $result = $this->subject->getMailRecipientsForApproval($approval);
        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(52, $result[0]->getUid());
        self::assertEquals(53, $result[1]->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalReturnsRecipientsForStage2AndChecksForEmail()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has the allowedTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has two approval-be-users for stage 1 set
         * Given that topic-object has two approval-be-users for stage 2 set
         * Given one of the approval-be-users for stage 2 has an invalid email-address set
         * When the method is called
         * Then an array is returned
         * Then this array contains the one be-user for stage 2
         * Then the be-user with the wrong email-address is not included in the array
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(60);

        $result = $this->subject->getMailRecipientsForApproval($approval);
        self::assertInternalType('array', $result);
        self::assertCount(1, $result);
        self::assertEquals(62, $result[0]->getUid());
        self::assertEquals('test2@rkw.de', $result[0]->getEmail());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsForApprovalReturnsEmptyArrayOnHigherStages()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has the allowedTstampStage1-property set
         * Given the approval-object has the allowedTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has two approval-be-users for stage 1 set
         * Given that topic-object has two approval-be-users for stage 2 set
         * When the method is called
         * Then an empty array is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(70);

        $result = $this->subject->getMailRecipientsForApproval($approval);
        self::assertEmpty($result);
    }
    //=============================================
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsOneForStage1Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check170.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(170);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsOneForStage1Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(180);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(1, $result);
        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsTwoForStage1Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(190);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(2, $result);
        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsZeroIfNoRecipientsForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has no approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 0 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(200);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(0, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsOneForStage2Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(210);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsOneForStage2Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 1 is returned
         *
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check220.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(220);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsTwoForStage2Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then the value 2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check230.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(230);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(2, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsZeroIfNoRecipientsForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has no approval-be-user for stage 2 set
         * When the method is called
         * Then the value 0 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(240);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(0, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsForApprovalReturnsZeroStageDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has a value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has no approval-be-user for stage 2 set
         * When the method is called
         * Then the value 0 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check270.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(270);

        $result = $this->subject->sendMailsForApproval($approval);
        self::assertEquals(0, $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsTrueAndIncreasesLevelForStage1Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage1-property is set
         * Then the sentReminderTstampStage1-property is not set
         * Then the allowTstampStage1-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check170.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(170);

        $result = $this->subject->processApproval($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(170);
        self::assertGreaterThan(0, $approvalDb->getSentInfoTstampStage1());
        self::assertEquals(0, $approvalDb->getSentReminderTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsTrueAndIncreasesLevelForStage1Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then true is returned
         * Then the sentReminderTstampStage1-property is set
         * Then the allowTstampStage1-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(180);

        $result = $this->subject->processApproval($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(180);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsFalseAndIncreasesStageForStage1Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage1-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(190);

        $result = $this->subject->processApproval($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(190);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsFalseAndIncreasesStageIfNoRecipientsForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has no approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage1-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(200);

        $result = $this->subject->processApproval($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(200);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsTrueAndIncreasesLevelForStage2Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage2-property is set
         * Then the sentReminderTstampStage2-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(210);

        $result = $this->subject->processApproval($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(210);
        self::assertGreaterThan(0, $approvalDb->getSentInfoTstampStage2());
        self::assertEquals(0, $approvalDb->getSentReminderTstampStage2());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsTrueAndIncreasesLevelForStage2Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then true is returned
         * Then the sentReminderTstampStage2-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check220.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(220);

        $result = $this->subject->processApproval($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(220);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage2());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsFalseAndIncreasesStageForStage2Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check230.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(230);

        $result = $this->subject->processApproval($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(230);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processApprovalReturnsFalseAndIncreasesStageIfNoRecipientsForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has no approval-be-user for stage 2 set
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(240);

        $result = $this->subject->processApproval($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(240);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForInfoMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForReminderMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value older than 600 seconds from now
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(250);
        $approval->setSentInfoTstampStage1(time() - 601);
        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfNotDueForReminderMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value not older than 600 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(250);
        $approval->setSentInfoTstampStage1(time() - 5);
        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfNotDueForAutomaticApprovalForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value not older than 1200 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(250);
        $approval->setSentInfoTstampStage1(time());
        $approval->setSentReminderTstampStage1($approval->getSentInfoTstampStage1() + 5);
        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForAutomaticApprovalForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value older than 1200 seconds from now
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(250);
        $approval->setSentInfoTstampStage1(time() - 1201);
        $approval->setSentReminderTstampStage1($approval->getSentInfoTstampStage1() + 5);

        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfDueForAutomaticApprovalButNoToleranceSetForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given the tolerance-parameter for the stage1 has been set to 0 seconds
         * Given the tolerance-parameter for the stage2 has been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value older than 1200 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(250);
        $approval->setSentInfoTstampStage1(time() - 1201);
        $approval->setSentReminderTstampStage1($approval->getSentInfoTstampStage1() + 5);

        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 0, 1200);
        self::assertEquals(0, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForInfoMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object as no value for the sentInfoTstampStage2-property set
         * Given the approval-object as no value for the sentReminderTstampStage2-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        $result = $this->subject->processAllApprovals(1200,1200, 600, 600);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForReminderMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object as no value for the sentReminderTstampStage2-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage2-property is set to a value older than 600 seconds from now
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(260);
        $approval->setSentInfoTstampStage2(time() - 601);
        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(1, $result);

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfNotDueForReminderMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage1-property is set to a value not older than 600 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(260);
        $approval->setSentInfoTstampStage2(time() - 5);
        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }
    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfNotDueForAutomaticApprovalForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage2-property is set to a value not older than 1200 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(260);
        $approval->setSentInfoTstampStage2(time() - 5);
        $approval->setSentReminderTstampStage2($approval->getSentInfoTstampStage2() + 5);

        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsOneIfDueForAutomaticApprovalForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object a value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * Given the sentInfoTstampStage2-property is set to a value older than 1200 seconds from now
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(260);
        $approval->setSentInfoTstampStage2(time() - 1201);
        $approval->setSentReminderTstampStage2($approval->getSentInfoTstampStage2() + 5);

        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 1200);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllApprovalsReturnsNoneIfDueForAutomaticApprovalButNoToleranceSetForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status 1
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object a value for the sentReminderTstampStage1-property set
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given the tolerance-parameter for the stage1 has been set to 1200 seconds
         * Given the tolerance-parameter for the stage2 has been set to 0 seconds
         * Given the sentInfoTstampStage2-property is set to a value older than 1200 seconds from now
         * When the method is called
         * Then zero is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(260);
        $approval->setSentInfoTstampStage2(time() - 1201);
        $approval->setSentReminderTstampStage2($approval->getSentInfoTstampStage2() + 5);

        $this->approvalRepository->update($approval);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $result = $this->subject->processAllApprovals(600, 600, 1200, 0);
        self::assertEquals(0, $result);
    }
    
    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
       //parent::tearDown();
    }








}