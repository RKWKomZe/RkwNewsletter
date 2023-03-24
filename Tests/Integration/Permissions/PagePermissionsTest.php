<?php
namespace RKW\RkwNewsletter\Tests\Integration\Permissions;

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
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Permissions\PagePermissions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * PagePermissionsTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagePermissionsTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PagePermissionsTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/accelerator',
        'typo3conf/ext/persisted_sanitized_routing',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_newsletter'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
        'seo'
    ];


    /**
     * @var \RKW\RkwNewsletter\Permissions\PagePermissions|null
     */
    private ?PagePermissions $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository|null
     */
    private ?PagesRepository $pagesRepository;


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
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);

        $this->subject = $this->objectManager->get(PagePermissions::class);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setPermissionsReturnsFalseOnMissingStageConfiguration()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given no valid configuration for the stage "release" but for another stage defined via parameter
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        $settings = [
            'stage2' => [
                'userId' => 1,
                'groupId'  => 1,
                'user'  => 1,
                'group'  => 1,
                'everybody' => 1,
            ]
        ];

        self::assertFalse($this->subject->setPermissions($page, $settings));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setPermissionsReturnsFalseOnWrongConfigurationValues()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a valid configuration for the stage "release" defined via parameter
         * Given this configuration does not contain settings for userId and groupId
         * Given all configuration-values for page-permissions are beyond the allowed range
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        $settings = [
            'release' => [
                'user'  => 32,
                'group'  => 32,
                'everybody' => 32,
            ]
        ];

        self::assertFalse($this->subject->setPermissions($page, $settings));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setPermissionsReturnsTrue()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a valid configuration for the stage "release" defined via parameter
         * Given this configuration does not contain settings for userId and groupId
         * Given all configuration-values for page-permissions are in the allowed range
         * When the method is called
         * Then true is returned
         * Then the page-permissions are persisted as configured
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        $settings = [
            'release' => [
                'user'  => 31,
                'group'  => 16,
                'everybody' => 1,
            ]
        ];

        self::assertTrue($this->subject->setPermissions($page, $settings));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $page = $this->pagesRepository->findByUid(10);
        self::assertEquals(31, $page->getPermsUser());
        self::assertEquals(16, $page->getPermsGroup());
        self::assertEquals(1, $page->getPermsEverybody());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setPermissionsReturnsTrueWithoutCheckingUserIdGroupId()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a valid configuration for the stage "release" defined via parameter
         * Given this configuration contain a settings for userId and groupId
         * Given this settings have values higher than the allowed range for page-rights
         * When the method is called
         * Then true is returned
         * Then the user-id and group-id-property are persisted as configured
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        $settings = [
            'release' => [
                'userId' => 35,
                'groupId'  => 38
            ]
        ];

        self::assertTrue($this->subject->setPermissions($page, $settings));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $page = $this->pagesRepository->findByUid(10);
        self::assertEquals(35, $page->getPermsUserId());
        self::assertEquals(38, $page->getPermsGroupId());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setPermissionsReturnsTrueUsingTyposcriptConfiguration()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given a persisted topic-object
         * Given a persisted page-object
         * Given that page-object belongs to the issue-object
         * Given that page-object belongs to the topic-object
         * Given a valid configuration defined via typoscript
         * Given this configuration contain a settings for userId and groupId
         * Given this settings have values higher than the allowed range for page-rights
         * When the method is called
         * Then true is returned
         * Then the page-permissions are persisted as configured
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(10);

        self::assertTrue($this->subject->setPermissions($page));

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $page = $this->pagesRepository->findByUid(10);
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
    public function validatePermissionReturnsFalseOnValueBelowZero()
    {

        /**
         * Scenario:
         *
         * Given a value below zero
         * When the method is called
         * Then false is returned
         */

        self::assertFalse($this->subject->validatePermission(-1));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function validatePermissionReturnsFalseOnValueAboveThirtyOne()
    {

        /**
         * Scenario:
         *
         * Given a value above 31
         * When the method is called
         * Then false is returned
         */
        self::assertFalse($this->subject->validatePermission(32));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function validatePermissionReturnsTrueOnValueInValidRange()
    {

        /**
         * Scenario:
         *
         * Given a value in valid range
         * When the method is called
         * Then true is returned
         */
        self::assertTrue($this->subject->validatePermission(17));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPermissionSettingsReturnsConfiguration()
    {

        /**
         * Scenario:
         *
         * Given valid configuration
         * When the method is called
         * Then a configuration-array is returned
         */

        $expected = [
            'stage1' => [
                'userId' => '1',
                'groupId' => '1',
                'user' => '1',
                'group' => '1',
                'everybody' => '1',
            ],
            'stage2' => [
                'userId' => '2',
                'groupId' => '2',
                'user' => '2',
                'group' => '2',
                'everybody' => '2',
            ],
            'release' => [
                'userId' => '3',
                'groupId' => '3',
                'user' => '4',
                'group' => '4',
                'everybody' => '4',
            ],
            'sent' => [
                'userId' => '4',
                'groupId' => '4',
                'user' => '8',
                'group' => '8',
                'everybody' => '8',
            ]
        ];

        self::assertEquals($expected, $this->subject->getPermissionSettings());
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
