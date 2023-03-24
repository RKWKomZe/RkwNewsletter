<?php
namespace RKW\RkwNewsletter\Tests\Integration\ViewHelpers\Mailing\Content;

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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;


/**
 * GetEditorialViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetEditorialViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GetEditorialViewHelperTest/Fixtures';

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
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     */
    private $topicRepository;


    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $standAloneViewHelper;


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

        /** @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository issueRepository */
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);

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




    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsEmptyForAllTopics()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given none of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with issue-parameter only
         * Then an empty value is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
            ]
        );

        $result = trim($this->standAloneViewHelper->render());
        self::assertEmpty($result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsEmptyIfOneTopicUsedButNoEditorial()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given none of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with issue-parameter and topic-parameter with topic A
         * Then an empty value is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(10);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage
            ]
        );

        $result = trim($this->standAloneViewHelper->render());
        self::assertEmpty($result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsContentIfOneTopicUsedAndEditorial()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given the page-object Q contains four content-objects
         * Given none of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with issue-parameter and topic-parameter with topic B
         * Then the editorial of topic B is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage
            ]
        );

        $result = trim($this->standAloneViewHelper->render());
        self::assertEquals('Content 11.1', $result);
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
