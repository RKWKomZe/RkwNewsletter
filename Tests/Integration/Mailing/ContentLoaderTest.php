<?php
namespace RKW\RkwNewsletter\Tests\Integration\Mailing;

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
use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Domain\Repository\ContentRepository;
use RKW\RkwNewsletter\Mailing\ContentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;


/**
 * ContentLoaderTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ContentLoaderTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ContentLoaderTest/Fixtures';

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
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     */
    private $contentRepository;    

    
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

        /** @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository contentRepository */
        $this->contentRepository = $this->objectManager->get(ContentRepository::class);        


    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getOrderingReturnsAllTopicIdsOfIssue()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When method is called without calling setTopics, addTopic or removeTopic before
         * Then it returns an array
         * Then the array contains three key-value-pairs
         * Then the key of topic A contains the value 0
         * Then the key of topic B contains the value 1
         * Then the key of topic C contains the value 2
         * Then topic D is not part of the array
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[10]);
        self::assertEquals(1, $result[11]);
        self::assertEquals(2, $result[12]);

    }

    
    /**
     * @test
     * @throws \Exception
     */
    public function getTopicsReturnsAllTopics()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given setTopics, addTopic or removeTopic are not called before
         * When method is called 
         * Then it returns an array
         * Then the array contains three topic-objects
         * Then topic D is not part of the array
         */
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getTopics();

        self::assertInternalType('array', $result);
        self::assertCount(3, $result);
        
        self::assertInstanceOf(Topic::class, $result[0]);
        self::assertInstanceOf(Topic::class, $result[1]);
        self::assertInstanceOf(Topic::class, $result[2]);

    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsThrowsException()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the method is called with sorting-parameter with one topic-object and one issue-object
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1649840507
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1649840507);
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        $subject->setTopics([$topic1, GeneralUtility::makeInstance(Issue::class)]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsUpdatesOrderingToTopicAFirst()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When the method is called with two topic-objects in the order topic A/topic B
         * Then getSorting returns an array 
         * Then the array contains two key-value-pairs
         * Then the key of topic A contains the value 0
         * Then the key of topic B contains the value 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);
              
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);
        
        $subject->setTopics([$topic1, $topic2]);
        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(0, $result[10]);
        self::assertEquals(1, $result[11]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsUpdatesOrderingToTopicBFirst()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * When method is called with two topic-objects in the order topic B/topic A
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic A contains the value 1
         * Then the key of topic B contains the value 0
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(1, $result[10]);
        self::assertEquals(0, $result[11]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsUpdatesOrderingToSpecialTopicFirst()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given topic-object C is marked as special topic
         * When method is called with two topic-objects in the order topic B/topic A
         * Then getSorting returns an array
         * Then the array contains three key-value-pairs
         * Then the key of topic C contains the value 0
         * Then the key of topic B contains the value 1
         * Then the key of topic A contains the value 2
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[82]);
        self::assertEquals(1, $result[81]);
        self::assertEquals(2, $result[80]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsUpdatesOrderingToTopicBFirstWithSpecialTopic()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given topic-object C is marked as special topic
         * When method is called with two topic-objects in the order topic B/topic A/topic C
         * Then getSorting returns an array
         * Then the array contains three key-value-pairs
         * Then the key of topic B contains the value 0
         * Then the key of topic A contains the value 1
         * Then the key of topic C contains the value 2
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic3 = $this->topicRepository->findByUid(82);

        $subject->setTopics([$topic2, $topic1, $topic3]);
        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[81]);
        self::assertEquals(1, $result[80]);
        self::assertEquals(2, $result[82]);


    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addTopicUpdatesOrdering()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given setTopics is called with topic-object B only
         * Then this method is called with topic-object A
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic A contains the value 1
         * Then the key of topic B contains the value 0
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        $subject->setTopics([$topic2]);
        $subject->addTopic($topic1);
        
        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(1, $result[10]);
        self::assertEquals(0, $result[11]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addTopicUpdatesOrderingWithSpecialTopicFirst()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given topic-object C is marked as special topic
         * Given setTopics is called with topic-object B only
         * Then this method is called with topic-object A
         * Then getSorting returns an array
         * Then the array contains three key-value-pairs
         * Then the key of topic C contains the value 0
         * Then the key of topic B contains the value 1
         * Then the key of topic A contains the value 2
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        $subject->setTopics([$topic2]);
        $subject->addTopic($topic1);

        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[82]);
        self::assertEquals(1, $result[81]);
        self::assertEquals(2, $result[80]);
        
    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function removeTopicUpdatesOrdering()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B, C and D
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * Then this method is called with topic-object B
         * Then getSorting returns an array
         * Then the array contains one key-value-pair
         * Then the key of topic A contains the value 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);
        
        $subject->setTopics([$topic2, $topic1]);
        $subject->removeTopic($topic2);

        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(1, $result);
        self::assertEquals(0, $result[10]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeTopicUpdatesOrderingWithSpecialTopicFirst()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given topic-object C is marked as special topic
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * Then this method is called with topic-object B
         * Then getSorting returns an array
         * Then the array contains two key-value-pair
         * Then the key of topic C contains the value 0
         * Then the key of topic A contains the value 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        $subject->setTopics([$topic2, $topic1]);
        $subject->removeTopic($topic2);

        $result = $subject->getOrdering();

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(0, $result[82]);
        self::assertEquals(1, $result[80]);
    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContentsWithTopicBFirst()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then an array of the size of five is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic B
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);
        
        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getContents();
        
        self::assertInternalType('array', $result);
        self::assertCount(5, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.2', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.3', $content->getHeader());

        $content = $result[3];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.3', $content->getHeader());

        $content = $result[4];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.4', $content->getHeader());
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContentsWithTopicAFirst()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic A/topic B
         * When the method is called
         * Then an array of the size of five is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic A
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);
        
        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        $subject->setTopics([$topic1, $topic2]);
        $result = $subject->getContents();

        self::assertInternalType('array', $result);
        self::assertCount(5, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.2', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.3', $content->getHeader());

        $content = $result[3];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.3', $content->getHeader());

        $content = $result[4];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.4', $content->getHeader());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContentsWithSpecialTopicFirst()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic A/topic B
         * When the method is called
         * Then an array of the size of seven is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic C
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(91);

        $subject->setTopics([$topic1, $topic2]);
        $result = $subject->getContents();

        self::assertInternalType('array', $result);
        self::assertCount(7, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 92.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.2', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 91.2', $content->getHeader());

        $content = $result[3];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 92.3', $content->getHeader());

        $content = $result[4];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.3', $content->getHeader());

        $content = $result[5];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 91.3', $content->getHeader());

        $content = $result[6];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.4', $content->getHeader());
    }
   

    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContentsAndRespectsLimit()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called with limit = 1
         * Then an array of the size of two is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic B
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then only one content of each topic A and B is included
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getContents(1);

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.2', $content->getHeader());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsAllTopics()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics, addTopic or removeTopic are not called before
         * When the method is called
         * Then an array of the size of five is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic A
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getContents();

        self::assertInternalType('array', $result);
        self::assertCount(7, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.2', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 22.2', $content->getHeader());

        $content = $result[3];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.3', $content->getHeader());

        $content = $result[4];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 21.3', $content->getHeader());

        $content = $result[5];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 22.3', $content->getHeader());

        $content = $result[6];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 20.4', $content->getHeader());

    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsFirstHeadlineTopicB()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEquals('Content 21.2', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsSecondHeadlineTopicBIfFirstEmpty()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(31);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEquals('Content 31.3', $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsEmptyIfNoHeadlinesTopicB()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(41);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEmpty($result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsFirstHeadlineSpecialTopic()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given three persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object
         * Given topic-object C is marked as special
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(91);

        $subject->setTopics([$topic2, $topic1]);
        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEquals('Content 92.2', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineIgnoresEditorials()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called with topic-object B only
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored even if there is only one topic given
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        $subject->setTopics([$topic2]);
        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEquals('Content 21.2', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsFirstHeadlineOfFirstTopicInConfiguration()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics, addTopic or removeTopic are not called before
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of the first topic in the newsletter configuration
         * Then the contents marked as editorial are ignored even if there is only one topic given
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getFirstHeadline();

        self::assertInternalType('string', $result);
        self::assertEquals('Content 20.2', $result);

    }

    
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getTopicOfContentReturnsTopicOfContent()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted page-object B
         * Given that page-object B belongs to the newsletter-object X
         * Given that page-object B belongs to the issue-object Y
         * Given that page-object B belongs to the topic-object A
         * Given a persisted content-object C
         * Given that content-object C belongs to the page-object B
         * When the method is called with content-object C as parameter
         * Then the topic-object A is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check50.xml');
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(50);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getTopicOfContent($content);

        self::assertInstanceOf(Topic::class, $result);
        self::assertEquals(50, $result->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTopicOfContentReturnsNull()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted page-object B
         * Given that page-object B belongs to the newsletter-object X
         * Given that page-object B belongs to the issue-object Y
         * Given that page-object does not belong to the topic-object A
         * Given a persisted content-object C
         * Given that content-object C belongs to the page-object B
         * When the method is called with content-object C as parameter
         * Then null is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(60);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getTopicOfContent($content);

        self::assertNull($result);
    }


    //=============================================

    
    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsNullForAllTopics()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics, addTopic or removeTopic are not called before
         * When the method is called
         * Then null is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $result = $subject->getEditorial();

        self::assertNull($result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsContentIfSpecialTopicAndEditorial()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given topic-object B is marked as special
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
         * Given setTopics is called with an empty array before
         * When the method is called
         * Then an object of instance \RKW\RkwNewsletter\Domain\Model\Content is returned
         * Then this object is the editorial of the given topic B
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(100);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $subject->setTopics([]);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $result */
        $result = $subject->getEditorial();

        self::assertInstanceOf(Content::class, $result);
        self::assertEquals(1, $result->getTxRkwnewsletterIsEditorial());
        self::assertEquals('Content 71.1', $result->getHeader());
    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsNullIfOneTopicUsedButNoEditorial()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called before with topic A only
         * When the method is called
         * Then null is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(70);
        
        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $subject->setTopics([$topic1]);
        $result = $subject->getEditorial();

        self::assertNull($result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsContentIfOneTopicUsedAndEditorial()
    {
        /**
         * Scenario:
         *
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
         * Given setTopics is called before with topic B only
         * When the method is called
         * Then an object of instance \RKW\RkwNewsletter\Domain\Model\Content is returned
         * Then this object is the editorial of the given topic B
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(71);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $subject = $this->objectManager->get(ContentLoader::class, $issue);

        $subject->setTopics([$topic1]);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Content $result */
        $result = $subject->getEditorial();
        
        self::assertInstanceOf(Content::class, $result);
        self::assertEquals(1, $result->getTxRkwnewsletterIsEditorial());
        self::assertEquals('Content 71.1', $result->getHeader());
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