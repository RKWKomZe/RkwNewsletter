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
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 *  IssueStatusTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueStatusTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/IssueStatusTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_newsletter'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Status\IssueStatus|null
     */
    private ?IssueStatus $subject = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    private ?IssueRepository $issueRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


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
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);
        $this->subject = $this->objectManager->get(IssueStatus::class);

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
         * When the method is called
         * Then $this->subject::STAGE_DRAFT is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        self::assertEquals($this->subject::STAGE_DRAFT, $this->subject::getStage($issue));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getStageReturnsApproval()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * When the method is called
         * Then $this->subject::STAGE_APPROVAL is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        self::assertEquals($this->subject::STAGE_APPROVAL, $this->subject::getStage($issue));
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
         * When the method is called
         * Then $this->subject::STAGE_RELEASE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        self::assertEquals($this->subject::STAGE_RELEASE, $this->subject::getStage($issue));
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
         * When the method is called
         * Then $this->subject::STAGE_SENDING is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        self::assertEquals($this->subject::STAGE_SENDING, $this->subject::getStage($issue));
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
         * When the method is called
         * Then $this->subject::STAGE_DONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        self::assertEquals($this->subject::STAGE_DONE, $this->subject::getStage($issue));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsNoneOnWrongStage()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "draft"
         * When the method is called
         * Then $this->subject::LEVEL_NONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);

        self::assertEquals($this->subject::LEVEL_NONE, $this->subject::getLevel($issue));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has no value for the infoTimestamp-property set
         * Given that issue-object has no value for the reminderTimestamp-property set
         * When the method is called
         * Then $this->subject::LEVEL1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        self::assertEquals($this->subject::LEVEL1, $this->subject::getLevel($issue));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevel2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has a value for the infoTimestamp-property set
         * Given that issue-object has no value for the reminderTimestamp-property set
         * When the method is called
         * Then $this->subject::LEVEL2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        self::assertEquals($this->subject::LEVEL2, $this->subject::getLevel($issue));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getLevelReturnsLevelDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has a value for the infoTimestamp-property set
         * Given that issue-object has a value for the reminderTimestamp-property set
         * When the method is called
         * Then $this->subject::LEVEL2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        self::assertEquals($this->subject::LEVEL_DONE, $this->subject::getLevel($issue));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForDraft()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 0
         * When the method is called
         * Then true is returned
         * Then the status of the issue-object is set to $this->subject::STAGE_APPROVAL
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::STAGE_APPROVAL, $issue->getStatus());
        self::assertEquals(0, $issue->getReleaseTstamp());
        self::assertEquals(0, $issue->getSentTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForApproval()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 1
         * When the method is called
         * Then true is returned
         * Then the status of the issue-object is set to $this->subject::STAGE_RELEASE
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::STAGE_RELEASE, $issue->getStatus());
        self::assertEquals(0, $issue->getReleaseTstamp());
        self::assertEquals(0, $issue->getSentTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForRelease()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 2
         * When the method is called
         * Then true is returned
         * Then the status of the issue-object is set to $this->subject::STAGE_SENDING
         * Then the releaseTstamp-property is set to the current time
         * Then the sentTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::STAGE_SENDING, $issue->getStatus());
        self::assertGreaterThan(0, $issue->getReleaseTstamp());
        self::assertEquals(0, $issue->getSentTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForSending()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 3
         * When the method is called
         * Then true is returned
         * Then the status of the issue-object is set to $this->subject::STAGE_DONE
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is set to the current time
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::STAGE_DONE, $issue->getStatus());
        self::assertEquals(0, $issue->getReleaseTstamp());
        self::assertGreaterThan(0, $issue->getSentTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsFalseForDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the status-property set to 4
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        $result = $this->subject->increaseStage($issue);
        self::assertFalse($result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseOnWrongStage()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "draft"
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);

        self::assertFalse($this->subject::increaseLevel($issue));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsTrueForLevel0()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has no value for the infoTimestamp-property set
         * Given that issue-object has no value for the reminderTimestamp-property set
         * When the method is called
         * Then true is returned
         * Then the infoTstamp-property is set
         * Then the reminderTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        self::assertTrue($this->subject::increaseLevel($issue));

        self::assertGreaterThan(0, $issue->getInfoTstamp());
        self::assertEquals(0, $issue->getReminderTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsTrueForLevel1()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has a value for the infoTimestamp-property set
         * Given that issue-object has no value for the reminderTimestamp-property set
         * When the method is called
         * Then true is returned
         * Then the infoTstamp-property is set
         * Then the reminderTstamp-property is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        self::assertTrue($this->subject::increaseLevel($issue));

        self::assertGreaterThan(0, $issue->getInfoTstamp());
        self::assertGreaterThan(0, $issue->getReminderTstamp());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseForLevel2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given that issue-object has the stage "release"
         * Given that issue-object has a value for the infoTimestamp-property set
         * Given that issue-object has a value for the reminderTimestamp-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        self::assertFalse($this->subject::increaseLevel($issue));
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
