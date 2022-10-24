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
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use RKW\RkwNewsletter\Domain\Repository\BackendUserRepository;
use RKW\RkwNewsletter\Status\ApprovalStatus;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 *  ApprovalStatusTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalStatusTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ApprovalStatusTest/Fixtures';

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
     * @var \RKW\RkwNewsletter\Status\ApprovalStatus
     */
    private $subject;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     */
    private $approvalRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     */
    private $backendUserRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;



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
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);
        $this->subject = $this->objectManager->get(ApprovalStatus::class);

    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has a value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::STAGE3 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(10);

        self::assertEquals($this->subject::STAGE_DONE, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object has a value for the allowedTstampStage1-property set
         * When the method is called
         * Then $this->subject::STAGE2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(20);

        self::assertEquals($this->subject::STAGE2, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object has none of the allowedTstampStage-properties set
         * When the method is called
         * Then $this->subject::STAGE1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(30);

        self::assertEquals($this->subject::STAGE1, $this->subject::getStage($approval));
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel1ForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has none of the allowedTstampStage-properties set
         * Given that approval-object has no sentInfoTstampStage1-property set
         * When the method is called
         * Then $this->subject::LEVEL1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(40);

        self::assertEquals($this->subject::LEVEL1, $this->subject::getLevel($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel2ForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has none of the allowedTstampStage-properties set
         * Given that approval-object has a value for the sentInfoTstampStage1-property set
         * When the method is called
         * Then $this->subject::LEVEL2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(50);

        self::assertEquals($this->subject::LEVEL2, $this->subject::getLevel($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevelDoneForStage1()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has none of the allowedTstampStage-properties set
         * Given that approval-object has a value for the sentReminderTstampStage1-property set
         * When the method is called
         * Then $this->subject::LEVEL_DONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(60);

        self::assertEquals($this->subject::LEVEL_DONE, $this->subject::getLevel($approval));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel1ForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has a value for of the allowedTstampStage1-property set
         * Given that approval-object has no sentInfoTstampStage2-property set
         * When the method is called
         * Then $this->subject::LEVEL1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(70);

        self::assertEquals($this->subject::LEVEL1, $this->subject::getLevel($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel2ForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has a value for of the allowedTstampStage1-property set
         * Given that approval-object has a value for the sentInfoTstampStage2-property set
         * When the method is called
         * Then $this->subject::LEVEL2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(80);

        self::assertEquals($this->subject::LEVEL2, $this->subject::getLevel($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevelDoneForStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has a value for of the allowedTstampStage1-property set
         * Given that approval-object has a value for the sentReminderTstampStage1-property set
         * When the method is called
         * Then $this->subject::LEVEL2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(90);

        self::assertEquals($this->subject::LEVEL_DONE, $this->subject::getLevel($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevelDoneForStagesAboveStage2()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given that approval-object has a value for of the allowedTstampStage2-property set
         * Given that approval-object has none of the sentInfoTstampStage-properties set
         * Given that approval-object has none of the sentReminderTstampStage-properties set
         * When the method is called
         * Then $this->subject::LEVEL0 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(100);

        self::assertEquals($this->subject::LEVEL_DONE, $this->subject::getLevel($approval));
    }


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
         * Then the reminderTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(110);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getSentInfoTstampStage1());
        self::assertEquals(0, $approval->getSentReminderTstampStage1());
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
         * Then the sentReminderTstampStage1-property is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(120);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getSentReminderTstampStage1());
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check130.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(130);

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
         * Then the sentReminderTstampStage2-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(140);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getSentInfoTstampStage2());
        self::assertEquals(0, $approval->getSentReminderTstampStage2());

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
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(150);

        $result = $this->subject->increaseLevel($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getSentReminderTstampStage2());
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(160);

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
         * Given no backendUser
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage1-property is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check170.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(170);

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getAllowedTstampStage1());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage1AndSetsBeUser()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has none of the allowedTstampStage-properties set
         * Given a persisted backendUser
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage1-property is set
         * Then the allowedByUserStage1-property is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(200);

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(200);

        $result = $this->subject->increaseStage($approval, $backendUser);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getAllowedTstampStage1());
        self::assertEquals($backendUser, $approval->getAllowedByUserStage1());
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
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(180);

        $result = $this->subject->increaseStage($approval);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getAllowedTstampStage2());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStage2AndSetsBeUser()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object
         * Given the approval-object has a value for the allowedTstampStage1-property set
         * Given a persisted backendUser
         * When the method is called
         * Then true is returned
         * Then the allowedTstampStage2-property is set
         * Then the allowedByUserStage2-property is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(210);

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(210);

        $result = $this->subject->increaseStage($approval, $backendUser);
        self::assertTrue($result);

        self::assertGreaterThan(0, $approval->getAllowedTstampStage2());
        self::assertEquals($backendUser, $approval->getAllowedByUserStage2());
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(190);

        $result = $this->subject->increaseStage($approval);
        self::assertFalse($result);

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
