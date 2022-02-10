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
use RKW\RkwNewsletter\Status\PageStatus;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * PageStatusTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
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
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Status\PageStatus
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

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
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->subject = $this->objectManager->get(PageStatus::class);

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function validatePermissionsReturnsFalseOnValueBelowZero()
    {

        /**
         * Scenario:
         *
         * Given a value below zero
         * When the method is called
         * Then false is returned
         */
        
        self::assertFalse($this->subject::validatePermissions(-1));
    }

    
    /**
     * @test
     * @throws \Exception
     */
    public function validatePermissionsReturnsFalseOnValueAboveThirtyOne()
    {

        /**
         * Scenario:
         *
         * Given a value above 31
         * When the method is called
         * Then false is returned
         */
        self::assertFalse($this->subject::validatePermissions(32));
    }
    

    /**
     * @test
     * @throws \Exception
     */
    public function validatePermissionsReturnsTrueOnValueInValidRange()
    {

        /**
         * Scenario:
         *
         * Given a value in valid range
         * When the method is called
         * Then true is returned
         */
        self::assertTrue($this->subject::validatePermissions(17));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::DRAFT is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(10);
        
        self::assertEquals($this->subject::DRAFT, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::APPROVAL_1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(20);

        self::assertEquals($this->subject::APPROVAL_1, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has a value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::APPROVAL_2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(30);

        self::assertEquals($this->subject::APPROVAL_2, $this->subject::getStage($approval));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsStage2EvenWhenDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has a value for the allowedTstampStage1-property set
         * Given that approval-object has a value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::APPROVAL_2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(40);

        self::assertEquals($this->subject::APPROVAL_2, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::RELEASE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(50);

        self::assertEquals($this->subject::RELEASE, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::SENDING is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(60);

        self::assertEquals($this->subject::SENDING, $this->subject::getStage($approval));
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
         * Given a persisted approval-object
         * Given that approval-object belongs to the issue-object
         * Given that approval-object has no value for the allowedTstampStage1-property set
         * Given that approval-object has no value for the allowedTstampStage2-property set
         * When the method is called
         * Then $this->subject::DONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(70);

        self::assertEquals($this->subject::DONE, $this->subject::getStage($approval));
    }



    /**
     * @test
     * @throws \Exception
     */
    public function setPagePermissions()
    {

        /**
         * Scenario:
         *
         * Given a persisted approval-object in stage 1
         * Given no valid configuration for this stage
         * When the method is called
         * Then false is returned
         */
        
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = $this->approvalRepository->findByUid(10);
        
        $settings = [
            'stage2' => [
                    'userId' => 1,
                    'groupId'  => 1,
                    'user'  => 1,
                    'group'  => 1,
                    'everybody' => 1,
                ]
            ];
                
        self::assertFalse($this->subject::setPagePermissions($approval, $settings));
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