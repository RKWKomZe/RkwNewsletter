<?php
namespace RKW\RkwNewsletter\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwNewsletter\Domain\Repository\TtContentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
/**
 * QueueMailRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContentRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter',
    ];
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TtcontentRepository
     */
    private $subject = null;
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;
    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/Fixtures/Database/TtContentRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/TtContentRepository/TtContent.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Tests/Functional/Utility/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(TtContentRepository::class);
    }


    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidReturnsOneContentOfGivenPageWithDefaultLanguageUid()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(1);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(1, $result->getPid());
        self::assertEquals(0, $result->getSysLanguageUid());

    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidWithNonMatchingLanguageUidReturnsNull()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(1, 1);

        self::assertNull($result);

    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidWithMatchingLanguageUidReturnsOneContentOfGivenPageWithMatchingLanguageUid()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(2, 1);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(2, $result->getPid());
        self::assertEquals(1, $result->getSysLanguageUid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidReturnsOneContentWhichIsNotAnEditorialByDefault()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(3);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(5, $result->getUid());
        self::assertEquals(3, $result->getPid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidAndIncludeEditorialTrueReturnsOneContentWhichAnEditorial()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(3, 0, true);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(4, $result->getUid());
        self::assertEquals(3, $result->getPid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsFirstContentWhichHasAnHeader()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(4);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(7, $result->getUid());
        self::assertNotEmpty($result->getHeader());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsOnlyContentWhichHasAnHeader()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(5);

        self::assertNull($result);
    }


    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsFirstContentSortedByOrdering()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(6);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(11, $result->getUid());

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}