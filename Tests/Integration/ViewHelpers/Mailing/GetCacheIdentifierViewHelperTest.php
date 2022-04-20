<?php
namespace RKW\RkwNewsletter\Tests\Integration\ViewHelpers\Mailing;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;


/**
 * GetCacheIdentifierViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetCacheIdentifierViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GetCacheIdentifierViewHelperTest/Fixtures';

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
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository issueRepository */
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository topicRepository */
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
    public function itReturnsIdentifierWithTopicAFirst()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the ViewHelper is rendered with topic-parameter in the order A-B
         * Then a string is returned
         * Then this string starts with the issue-uid
         * Then this string contains the topic-ids in the order A-B as second part
         * Then this string contains the limit of zero as third part
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => [$topic1, $topic2]            
            ]
        );


        self::assertEquals(
            '10_10-11_0', 
            trim($this->standAloneViewHelper->render())
        );
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsIdentifierWithTopicBFirst()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the ViewHelper is rendered with topic-parameter in the order B-A
         * Then a string is returned
         * Then this string starts with the issue-uid
         * Then this string contains the topic-ids in the order B-A as second part
         * Then this string contains the limit of zero as third part
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => [$topic2, $topic1]
            ]
        );


        self::assertEquals(
            '10_11-10_0', 
            trim($this->standAloneViewHelper->render())
        );
    }

    
    
    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsIdentifierAndRespectsLimit()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the ViewHelper is rendered with topic-parameter in the order B-A and with limit = 1
         * Then a string is returned
         * Then this string starts with the issue-uid
         * Then this string contains the topic-ids in the order B-A as second part
         * Then this string contains the limit of one as third part
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => [$topic2, $topic1],
                'limit' => 1
            ]
        );

        self::assertEquals(
            '10_11-10_1',
            trim($this->standAloneViewHelper->render())
        );
    }
    
    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsIdentifierOfAllAvailableTopics()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the ViewHelper is rendered without topic parameter
         * Then a string is returned
         * Then this string starts with the issue-uid
         * Then this string contains the topic-ids of all available topics as second part
         * Then this string contains the limit of zero as third part
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue
            ]
        );

        self::assertEquals(
            '10_10-11-12_0',
            trim($this->standAloneViewHelper->render())
        );
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