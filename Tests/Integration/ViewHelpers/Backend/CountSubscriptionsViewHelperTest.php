<?php
namespace RKW\RkwNewsletter\Tests\Integration\ViewHelpers\Backend;

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

use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;


/**
 * CountSubscriptionsViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CountSubscriptionsViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/CountSubscriptionsViewHelperTest/Fixtures';


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
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository|null
     */
    private ?NewsletterRepository $newletterRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository|null
     */
    private ?TopicRepository $topicRepository = null;


    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView|null
     */
    private ?StandaloneView $standAloneViewHelper = null;


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

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/constants.typoscript',
                'EXT:postmaster/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository newsletterRepository */
        $this->newsletterRepository = $this->objectManager->get(NewsletterRepository::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository topicRepository */
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView standAloneViewHelper */
        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsAllSubscriptions()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given two topics, A and B, that belong to the newsletter-object
         * Given topic A has two subscriptions, user X and Y
         * Given topic B has one subscription, user Z
         * When the ViewHelper is rendered without topic-parameter
         * Then the value 3 is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'newsletter' => $newsletter,
                'topic' => null
            ]
        );

        self::assertEquals('3', trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsAllSubscriptionsDistinct()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given two topics, A and B, that belong to the newsletter-object
         * Given topic A has two subscriptions, user X and Y
         * Given topic B has two subscriptions, user X and Z
         * When the ViewHelper is rendered without topic-parameter
         * Then the value 3 is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(20);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'newsletter' => $newsletter,
                'topic' => null
            ]
        );


        self::assertEquals('3', trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsSubscriptionsOfTopic()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given two topics, A and B, that belong to the newsletter-object
         * Given topic A has two subscriptions, user X and Y
         * Given topic B has one subscription, user Z
         * When the ViewHelper is rendered with topic-parameter topic A
         * Then the value 2 is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(30);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'newsletter' => $newsletter,
                'topic' => $topic
            ]
        );

        self::assertEquals('2', trim($this->standAloneViewHelper->render()));
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
