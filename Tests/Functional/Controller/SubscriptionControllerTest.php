<?php
namespace RKW\RkwNewsletter\Tests\Functional\Controller;

use RKW\RkwNewsletter\Controller\SubscriptionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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
 * SubscriptionControllerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubscriptionControllerTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/media_utils',
        'typo3conf/ext/sms_responsive_images',
        'typo3conf/ext/dr_serp',
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/accelerator',
        'typo3conf/ext/persisted_sanitized_routing',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/fe_register',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_newsletter',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
        'css_styled_content',
        'filemetadata',
        'seo'
    ];

    /**
     * @var \RKW\RkwNewsletter\Controller\SubscriptionController|null
     */
    private ?SubscriptionController $subject = null;



    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    private ?PersistenceManager $persistenceManager = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->importDataSet(__DIR__ . '/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/TtContent.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/FeUsers.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:css_styled_content/static/constants.typoscript',
                'EXT:css_styled_content/static/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/constants.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/constants.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                'EXT:fe_register/Configuration/TypoScript/constants.typoscript',
                'EXT:fe_register/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Tests/Functional/Controller/Fixtures/Frontend/Basics.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManager->get(SubscriptionController::class);

    }


    /**
     * @test
     */
    public function newActionRedirectsToEditIfFrontendUserHasSubscription ()
    {

        // dummy so far!
        self::assertSame(1,1);

    }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
