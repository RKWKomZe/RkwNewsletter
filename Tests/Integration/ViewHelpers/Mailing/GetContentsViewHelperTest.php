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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;


/**
 * GetContentsViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetContentsViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GetContentsViewHelperTest/Fixtures';

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
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
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
    public function itReturnsContentsWithTopicAFirst()
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
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with topic-parameter in the order A-B
         * Then a list of five contents is rendered
         * Then the list contains only the given topics of the issue
         * Then the list starts with contents of topic A
         * Then the list is ordered in zipper-method respecting the defined order of contents
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);
        $objectStorage->attach($topic2);
        
        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage         
            ]
        );

        self::assertEquals(
            'Content 10.2,Content 11.2,Content 10.3,Content 11.3,Content 10.4', 
            trim($this->standAloneViewHelper->render())
        );
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsContentsWithTopicBFirst()
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
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with topic-parameter in the order B-A
         * Then a list of five contents is rendered
         * Then the list contains only the given topics of the issue
         * Then the list starts with contents of topic B
         * Then the list is ordered in zipper-method respecting the defined order of contents
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);
        
        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage
            ]
        );


        self::assertEquals(
            'Content 11.2,Content 10.2,Content 11.3,Content 10.3,Content 10.4', 
            trim($this->standAloneViewHelper->render())
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function itReturnsSortedContentsWithSpecialTopicFirst()
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
         * Given the topic-object C is marked as a special topic
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with topic-parameter topic A/topic B 
         * Then a list of seven contents is rendered
         * Then the list contains only the given topics of the issue
         * Then the list starts with contents of topic c
         * Then the list is ordered in zipper-method respecting the defined order of contents
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);
        $objectStorage->attach($topic2);
        
        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage
            ]
        );

        self::assertEquals(
            'Content 92.2,Content 90.2,Content 91.2,Content 92.3,Content 90.3,Content 91.3,Content 90.4',
            trim($this->standAloneViewHelper->render())
        );
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsContentsAndRespectsLimit()
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
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered with topic-parameter in the order B-A and with limit = 1
         * Then a list of two contents is rendered
         * Then the list contains only the given topics of the issue
         * Then the list starts with contents of topic B
         * Then the list is ordered in zipper-method respecting the defined order of contents
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);
        
        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topics' => $objectStorage,
                'limit' => 1
            ]
        );

        self::assertEquals(
            'Content 11.2,Content 10.2',
            trim($this->standAloneViewHelper->render())
        );
    }
    
    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsContentsOfAllAvailableTopics()
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
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * When the ViewHelper is rendered without topic parameter
         * Then a list of seven contents is rendered
         * Then the list contains all available topics of the issue
         * Then the list starts with contents of topic A
         * Then the list is ordered in zipper-method respecting the defined order of contents
         * Then the contents marked as editorial are ignored
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
            'Content 10.2,Content 11.2,Content 12.2,Content 10.3,Content 11.3,Content 12.3,Content 10.4',
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