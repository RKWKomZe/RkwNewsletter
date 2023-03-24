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
use RKW\RkwNewsletter\Domain\Model\BackendUser;
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
 * @copyright RKW Kompetenzzentrum
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
        'typo3conf/ext/core_extended',
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/accelerator',
        'typo3conf/ext/persisted_sanitized_routing',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_newsletter'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
        'seo'
    ];


    /**
     * @var \RKW\RkwNewsletter\Manager\ApprovalManager|null
     */
    private ?ApprovalManager $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository|null
     */
    private ?TopicRepository $topicRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    private ?IssueRepository $issueRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository|null
     */
    private ?PagesRepository $pagesRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository|null
     */
    private ?ApprovalRepository $approvalRepository;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/constants.typoscript',
                'EXT:postmaster/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
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

        // For Mail-Interface
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'service@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToAddress'] = 'reply@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] = 'bounces@mein.rkw.de';
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
    public function increaseLevelReturnsTrueAndSetsFirstLevelForStage1()
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
    public function increaseLevelReturnsTrueAndSetSecondLevelForStage1()
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
    public function increaseLevelReturnsTrueAndSetsFirstLevelForStage2()
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
    public function increaseLevelReturnsTrueAndSetSecondLevelForStage2()
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
         * Given no backendUser is logged in
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage1-property is set
         * Then the allowedByUserStage1-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(140);

        $GLOBALS['BE_USER'] = new \TYPO3\CMS\Core\Authentication\BackendUserAuthentication();
        $GLOBALS['BE_USER']->user = [];

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(140);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertNull($approvalDb->getAllowedByUserStage1());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage1AndSetsBackendUser()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * Given the method is called via backend and thus a backendUser is logged in
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage1-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(140);

        $GLOBALS['BE_USER'] = new \TYPO3\CMS\Core\Authentication\BackendUserAuthentication();
        $GLOBALS['BE_USER']->user= [];
        $GLOBALS['BE_USER']->user['uid'] = 140;

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(140);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertInstanceOf(BackendUser::class, $approvalDb->getAllowedByUserStage1());
        self::assertEquals(140, $approvalDb->getAllowedByUserStage1()->getUid());

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
         * Given no backendUser is logged in
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage2-property is set
         * Then the allowedByUserStage2-property is not set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(150);

        $GLOBALS['BE_USER'] = new \TYPO3\CMS\Core\Authentication\BackendUserAuthentication();
        $GLOBALS['BE_USER']->user = [];

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(150);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());
        self::assertNull($approvalDb->getAllowedByUserStage2());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage2AndSetsBackendUser()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given no backendUser is logged in
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage2-property is set
         * Then the allowedByUserStage2-property is set
         * Then the changes to the approval-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(150);

        $GLOBALS['BE_USER'] = new \TYPO3\CMS\Core\Authentication\BackendUserAuthentication();
        $GLOBALS['BE_USER']->user= [];
        $GLOBALS['BE_USER']->user['uid'] = 150;

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(150);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());
        self::assertInstanceOf(BackendUser::class, $approvalDb->getAllowedByUserStage2());
        self::assertEquals(150, $approvalDb->getAllowedByUserStage2()->getUid());
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
    public function getMailRecipientsReturnsEmptyArray()
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

        self::assertEmpty($this->subject->getMailRecipients($approval));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipientsForStage1()
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

        $result = $this->subject->getMailRecipients($approval);
        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertEquals(30, $result[0]->getUid());
        self::assertEquals(31, $result[1]->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipientsForStage1AndChecksForEmail()
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

        $result = $this->subject->getMailRecipients($approval);
        self::assertIsArray( $result);
        self::assertCount(1, $result);
        self::assertEquals(40, $result[0]->getUid());
        self::assertEquals('test@rkw.de', $result[0]->getEmail());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipientsForStage2()
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

        $result = $this->subject->getMailRecipients($approval);
        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertEquals(52, $result[0]->getUid());
        self::assertEquals(53, $result[1]->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipientsForStage2AndChecksForEmail()
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

        $result = $this->subject->getMailRecipients($approval);
        self::assertIsArray( $result);
        self::assertCount(1, $result);
        self::assertEquals(62, $result[0]->getUid());
        self::assertEquals('test2@rkw.de', $result[0]->getEmail());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsEmptyArrayOnHigherStages()
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

        $result = $this->subject->getMailRecipients($approval);
        self::assertEmpty($result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStage1Level1()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStage1Level2()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsTwoForStage1LevelDone()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(2, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsZeroIfNoRecipientsForStage1()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(0, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStage2Level1()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStage2Level2()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsTwoForStage2LevelDone()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(2, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsZeroIfNoRecipientsForStage2()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsZeroForStageDone()
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

        $result = $this->subject->sendMails($approval);
        self::assertEquals(0, $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStage1Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given the approval-object has a page defined in the page-property
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage1-property is set
         * Then the sentReminderTstampStage1-property is not set
         * Then the allowTstampStage1-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check170.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(170);

        $result = $this->subject->processConfirmation($approval);
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(170);
        self::assertEquals(1, $page->getPermsUserId());
        self::assertEquals(1, $page->getPermsGroupId());
        self::assertEquals(1, $page->getPermsUser());
        self::assertEquals(1, $page->getPermsGroup());
        self::assertEquals(1, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStage1Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then true is returned
         * Then the sentReminderTstampStage1-property is set
         * Then the allowTstampStage1-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(180);

        $result = $this->subject->processConfirmation($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(180);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(180);
        self::assertEquals(1, $page->getPermsUserId());
        self::assertEquals(1, $page->getPermsGroupId());
        self::assertEquals(1, $page->getPermsUser());
        self::assertEquals(1, $page->getPermsGroup());
        self::assertEquals(1, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseAndIncreasesStageForStage1Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage1-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(190);

        $result = $this->subject->processConfirmation($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(190);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(190);
        self::assertEquals(2, $page->getPermsUserId());
        self::assertEquals(2, $page->getPermsGroupId());
        self::assertEquals(2, $page->getPermsUser());
        self::assertEquals(2, $page->getPermsGroup());
        self::assertEquals(2, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseAndIncreasesStageIfNoRecipientsForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has no approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage1-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(200);

        $result = $this->subject->processConfirmation($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(200);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage1());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(200);
        self::assertEquals(2, $page->getPermsUserId());
        self::assertEquals(2, $page->getPermsGroupId());
        self::assertEquals(2, $page->getPermsUser());
        self::assertEquals(2, $page->getPermsGroup());
        self::assertEquals(2, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStage2Level0()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then true is returned
         * Then the sentInfoTstampStage2-property is set
         * Then the sentReminderTstampStage2-property is not set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(210);

        $result = $this->subject->processConfirmation($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(210);
        self::assertGreaterThan(0, $approvalDb->getSentInfoTstampStage2());
        self::assertEquals(0, $approvalDb->getSentReminderTstampStage2());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(210);
        self::assertEquals(2, $page->getPermsUserId());
        self::assertEquals(2, $page->getPermsGroupId());
        self::assertEquals(2, $page->getPermsUser());
        self::assertEquals(2, $page->getPermsGroup());
        self::assertEquals(2, $page->getPermsEverybody());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStage2Level1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then true is returned
         * Then the sentReminderTstampStage2-property is set
         * Then the allowTstampStage2-property is not set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check220.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(220);

        $result = $this->subject->processConfirmation($approval);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(220);
        self::assertGreaterThan(0, $approvalDb->getSentReminderTstampStage2());
        self::assertEquals(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(220);
        self::assertEquals(2, $page->getPermsUserId());
        self::assertEquals(2, $page->getPermsGroupId());
        self::assertEquals(2, $page->getPermsUser());
        self::assertEquals(2, $page->getPermsGroup());
        self::assertEquals(2, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseAndIncreasesStageForStage2Level2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has one approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check230.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(230);

        $result = $this->subject->processConfirmation($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(230);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(230);
        self::assertEquals(3, $page->getPermsUserId());
        self::assertEquals(3, $page->getPermsGroupId());
        self::assertEquals(4, $page->getPermsUser());
        self::assertEquals(4, $page->getPermsGroup());
        self::assertEquals(4, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseAndIncreasesStageIfNoRecipientsForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object belongs to this issue-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted topic-object that belongs to the approval-object
         * Given that topic-object has one approval-be-user for stage 1 set
         * Given that topic-object has no approval-be-user for stage 2 set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * When the method is called
         * Then false is returned
         * Then the allowTstampStage2-property is set
         * Then the changes to the approval-object are persisted
         * Then the permissions of the page are set according to the configuration of the new status
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(240);

        $result = $this->subject->processConfirmation($approval);
        self::assertFalse($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approvalDb */
        $approvalDb = $this->approvalRepository->findByUid(240);
        self::assertGreaterThan(0, $approvalDb->getAllowedTstampStage2());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(240);
        self::assertEquals(3, $page->getPermsUserId());
        self::assertEquals(3, $page->getPermsGroupId());
        self::assertEquals(4, $page->getPermsUser());
        self::assertEquals(4, $page->getPermsGroup());
        self::assertEquals(4, $page->getPermsEverybody());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForInfoMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has no value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForReminderMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfNotDueForReminderMailForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has no value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfNotDueForAutomaticConfirmationForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForAutomaticConfirmationForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfDueForAutomaticConfirmationButNoToleranceSetForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has no value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage1-property set
         * Given the approval-object has a value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 0, 1200);
        self::assertEquals(0, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForInfoMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object as no value for the sentInfoTstampStage2-property set
         * Given the approval-object as no value for the sentReminderTstampStage2-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
         * Given both tolerance-parameters for the level have been set to 600 seconds
         * Given both tolerance-parameters for the stage have been set to 1200 seconds
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        $result = $this->subject->processAllConfirmations(1200,1200, 600, 600);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForReminderMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object as no value for the sentReminderTstampStage2-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfNotDueForReminderMailForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has no value for the sentReminderTstampStage2-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfNotDueForAutomaticConfirmationForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given the approval-object has no value for the allowedTstampStage2-property set
         * Given the approval-object has a value for the sentInfoTstampStage2-property set
         * Given the approval-object has a value for the sentReminderTstampStage2-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfDueForAutomaticConfirmationForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object a value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 1200);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfDueForAutomaticConfirmationButNoToleranceSetForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the status "approval"
         * Given a persisted approval-object
         * Given the approval-object a value for the allowedTstampStage1-property set
         * Given the approval-object no value for the allowedTstampStage2-property set
         * Given the approval-object a value for the sentInfoTstampStage2-property set
         * Given the approval-object a value for the sentReminderTstampStage1-property set
         * Given a persisted page-object
         * Given the page-object refers the issue-object
         * Given the page-object refers the topic-object
         * Given the page-object belongs to the approval-object
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

        $result = $this->subject->processAllConfirmations(600, 600, 1200, 0);
        self::assertEquals(0, $result);
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
