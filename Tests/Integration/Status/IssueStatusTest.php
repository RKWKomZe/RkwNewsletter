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
 * @copyright Rkw Kompetenzzentrum
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
     * @var \RKW\RkwNewsletter\Status\IssueStatus
     */
    private $subject;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;
    
    
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
         * Then $this->subject::DRAFT is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        self::assertEquals($this->subject::DRAFT, $this->subject::getStage($issue));
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
         * Then $this->subject::APPROVAL is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        self::assertEquals($this->subject::APPROVAL, $this->subject::getStage($issue));
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
         * Then $this->subject::RELEASE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        self::assertEquals($this->subject::RELEASE, $this->subject::getStage($issue));
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
         * Then $this->subject::SENDING is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        self::assertEquals($this->subject::SENDING, $this->subject::getStage($issue));
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
         * Then $this->subject::DONE is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        self::assertEquals($this->subject::DONE, $this->subject::getStage($issue));
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
         * Then the status of the issue-object is set to $this->subject::APPROVAL
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is not set 
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::APPROVAL, $issue->getStatus());
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
         * Then the status of the issue-object is set to $this->subject::RELEASE
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::RELEASE, $issue->getStatus());
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
         * Then the status of the issue-object is set to $this->subject::SENDING
         * Then the releaseTstamp-property is set to the current time
         * Then the sentTstamp-property is not set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::SENDING, $issue->getStatus());
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
         * Then the status of the issue-object is set to $this->subject::DONE
         * Then the releaseTstamp-property is not set
         * Then the sentTstamp-property is set to the current time
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);
        self::assertEquals($this->subject::DONE, $issue->getStatus());
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
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
    
}