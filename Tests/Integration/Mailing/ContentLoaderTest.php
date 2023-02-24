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
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Domain\Repository\ContentRepository;
use RKW\RkwNewsletter\Mailing\ContentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;


/**
 * ContentLoaderTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
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
        'typo3conf/ext/core_extended',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwNewsletter\Mailing\ContentLoader|null
     */
    private ?ContentLoader $subject = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    private ?IssueRepository $issueRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository|null
     */
    private ?TopicRepository $topicRepository = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository|null
     */
    private ?ContentRepository $contentRepository = null;


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
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/constants.typoscript',
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

        /** @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository contentRepository */
        $this->contentRepository = $this->objectManager->get(ContentRepository::class);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $subject */
        $this->subject = $this->objectManager->get(ContentLoader::class);


    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setIssueSetsIssueAndTopics()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects X and Y
         * Given a persisted issue-object M that belongs to the newsletter-object X
         * Given a persisted issue-object N that belongs to the newsletter-object Y
         * Given three persisted topic-objects A, B, C that belong to the newsletter-object X
         * Given a persisted topic-object D that belongs to the newsletter-object Y
         * Given for topic-object A there is a page-object W that belongs to the issue-object M
         * Given for topic-object B there is a page-object X that belongs to the issue-object M
         * Given for topic-object C there is a page-object Y that belongs to the issue-object M
         * Given for topic-object D there is a page-object Z that belongs to the issue-object N
         * Given the issue-object N is set via setIssue before
         * When method is called
         * Then getIssue returns the issue M that has been set
         * Then getTopics returns three topics
         * Then these topics are the topics A, B and C of the given issue M
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueOne */
        $issueOne = $this->issueRepository->findByUid(110);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueTwo */
        $issueTwo = $this->issueRepository->findByUid(111);

        $expectedTopics = [];
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach($issueOne->getPages() as $page) {
            $expectedTopics[] = $page->getTxRkwnewsletterTopic();
        }

        $this->subject->setIssue($issueTwo);
        $this->subject->setIssue($issueOne);

        self::assertEquals($issueOne, $this->subject->getIssue());
        self::assertCount(3, $this->subject->getTopics());
        self::assertEquals($expectedTopics, $this->subject->getTopics()->toArray());
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
         * Given the issue-object is set via setIssue before
         * When method is called
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

        $this->subject->setIssue($issue);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[10]);
        self::assertEquals(1, $result[11]);
        self::assertEquals(2, $result[12]);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getTopicsReturnsAllTopicsOfIssue()
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
         * Given the issue-object is set via setIssue before
         * When method is called
         * Then it returns an ObjectStorage
         * Then the ObjectStorage contains three topic-objects
         * Then topic D is not part of the ObjectStorage
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $this->subject->setIssue($issue);
        $result = $this->subject->getTopics();

        self::assertInstanceOf(ObjectStorage::class, $result);
        self::assertCount(3, $result);

        $result = $result->toArray();
        self::assertInstanceOf(Topic::class, $result[0]);
        self::assertInstanceOf(Topic::class, $result[1]);
        self::assertInstanceOf(Topic::class, $result[2]);
    }

    //=============================================

    /**
     * @comment: not needed anymore!
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
         * Given the issue-object is set via setIssue before
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        $this->subject->setIssue($issue);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);
        $objectStorage->attach(GeneralUtility::makeInstance(Issue::class));

        $this->subject->setTopics($objectStorage);

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
         * Given the issue-object is set via setIssue before
         * When the method is called with two topic-objects in the order topic A/topic B
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic A contains the value 0
         * Then the key of topic B contains the value 1
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

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
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
         * Given the issue-object is set via setIssue before
         * When method is called with two topic-objects in the order topic B/topic A
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic A contains the value 1
         * Then the key of topic B contains the value 0
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

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
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
         * Given the issue-object is set via setIssue before
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
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
         * Given the issue-object is set via setIssue before
         * When the method is called with two topic-objects in the order topic B/topic A/topic C
         * Then getSorting returns an array
         * Then the array contains three key-value-pairs
         * Then the key of topic B contains the value 0
         * Then the key of topic A contains the value 1
         * Then the key of topic C contains the value 2
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic3 = $this->topicRepository->findByUid(82);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);
        $objectStorage->attach($topic3);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[81]);
        self::assertEquals(1, $result[80]);
        self::assertEquals(2, $result[82]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTopicsIgnoresTopicsOfOtherNewsletters()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted newsletter-object Y
         * Given a persisted issue-object K that belongs to the newsletter-object X
         * Given a persisted issue-object L that belongs to the newsletter-object Y
         * Given two persisted topic-objects A and B that belong to the the newsletter-object X
         * Given one persisted topic-object C that belongs to the the newsletter-object Y
         * Given for topic-object A there is a page-object X that belongs to the current issue-object K
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object K
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object L
         * Given the issue-object is set via setIssue before
         * When the method is called with three topic-objects in the order topic B/topic A/topic C
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic B contains the value 0
         * Then the key of topic A contains the value 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(161);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic3 */
        $topic3 = $this->topicRepository->findByUid(162);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);
        $objectStorage->attach($topic3);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertEquals(0, $result[161]);
        self::assertEquals(1, $result[160]);

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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $this->subject->addTopic($topic1);

        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $this->subject->addTopic($topic1);

        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(3, $result);
        self::assertEquals(0, $result[82]);
        self::assertEquals(1, $result[81]);
        self::assertEquals(2, $result[80]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function addTopicIgnoresTopicOfOtherNewsletters()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted newsletter-object Y
         * Given a persisted issue-object K that belongs to the newsletter-object X
         * Given a persisted issue-object L that belongs to the newsletter-object Y
         * Given two persisted topic-objects A and B that belong to the the newsletter-object X
         * Given one persisted topic-object C that belongs to the the newsletter-object Y
         * Given for topic-object A there is a page-object X that belongs to the current issue-object K
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object K
         * Given for topic-object C there is a page-object Z that belongs to the current issue-object L
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic-objects B and A
         * When the method is called with topic-object C
         * Then getSorting returns an array
         * Then the array contains two key-value-pairs
         * Then the key of topic B contains the value 0
         * Then the key of topic A contains the value 1
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(160);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(161);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic3 */
        $topic3 = $this->topicRepository->findByUid(162);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        $this->subject->addTopic($topic3);
        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertEquals(0, $result[161]);
        self::assertEquals(1, $result[160]);
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(11);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $this->subject->removeTopic($topic2);

        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(81);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $this->subject->removeTopic($topic2);

        $result = $this->subject->getOrdering();

        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertEquals(0, $result[82]);
        self::assertEquals(1, $result[80]);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContents()
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
         * Given the issue-object is set via setIssue before
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getContents();

        self::assertIsArray( $result);
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
         * Given the issue-object is set via setIssue before
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

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getContents(1);

        self::assertIsArray( $result);
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
    public function getContentsReturnsSortedContentsAndIgnoresEmptyTopics()
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
         * Given the page-object X contains no content-objects
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with two topic-objects in the order topic A/topic B
         * When the method is called
         * Then an array of the size of three is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array contains only contents of topic B respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         * Then no contents of topic C are included
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(41);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getContents();

        self::assertIsArray( $result);
        self::assertCount(3, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 40.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 40.3', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 40.4', $content->getHeader());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getContentsReturnsSortedContentsOfAllTopicsOfIssue()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given four persisted topic-objects A, B and C
         * Given this topic-objects belong to the newsletter-object
         * Given for topic-object A there is a page-object X that belongs to the current issue-object
         * Given for topic-object B there is a page-object Y that belongs to the current issue-object
         * Given for topic-object C there is a page-object Z that does NOT belong to the current issue-object
         * Given the page-object X contains four content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Y contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the page-object Z contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given addTopic is called with topic C before
         * When the method is called
         * Then an array of the size of five is returned
         * Then the items are instances of \RKW\RkwNewsletter\Domain\Model\Content
         * Then the array starts with contents of topic A
         * Then the array is ordered in zipper-method respecting the defined order of contents (database)
         * Then the contents marked as editorial are ignored
         * Then the contents of topic C are ignored
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(92);

        $this->subject->setIssue($issue);
        $this->subject->addTopic($topic);
        $result = $this->subject->getContents();

        self::assertIsArray( $result);
        self::assertCount(5, $result);

        $content = $result[0];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.2', $content->getHeader());

        $content = $result[1];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 91.2', $content->getHeader());

        $content = $result[2];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.3', $content->getHeader());

        $content = $result[3];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 91.3', $content->getHeader());

        $content = $result[4];
        self::assertInstanceOf(Content::class, $content);
        self::assertEquals('Content 90.4', $content->getHeader());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsFirstHeadline()
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
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
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
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getFirstHeadline();

        self::assertIsString( $result);
        self::assertEquals('Content 21.2', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getFirstHeadlineReturnsEmptyIfFirstIsEmpty()
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
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with two topic-objects in the order topic B/topic A
         * When the method is called
         * Then a string is returned
         * Then the string is empty
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(31);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getFirstHeadline();

        self::assertIsString( $result);
        self::assertEmpty($result);

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
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic-object B only
         * When the method is called
         * Then a string is returned
         * Then the string is the headline of the first content of topic B
         * Then the contents marked as editorial are ignored even if there is only one topic given
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(21);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getFirstHeadline();

        self::assertIsString( $result);
        self::assertEquals('Content 21.2', $result);

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
         * Given the issue-object is set via setIssue before
         * When the method is called with content-object C as parameter
         * Then the topic-object A is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(50);

        $this->subject->setIssue($issue);
        $result = $this->subject->getTopicOfContent($content);

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
         * Given the issue-object is set via setIssue before
         * When the method is called with content-object C as parameter
         * Then null is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(60);

        $this->subject->setIssue($issue);
        $result = $this->subject->getTopicOfContent($content);

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
         * Given the issue-object is set via setIssue before
         * When the method is called
         * Then null is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        $this->subject->setIssue($issue);
        $result = $this->subject->getEditorial();

        self::assertNull($result);
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
         * Given the issue-object is set via setIssue before
         * Given setTopics is called before with topic A only
         * When the method is called
         * Then null is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(70);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);
        $result = $this->subject->getEditorial();

        self::assertNull($result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsContentIfOneTopicUsedWithEditorial()
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
         * Given the issue-object is set via setIssue before
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

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $result */
        $result = $this->subject->getEditorial();

        self::assertInstanceOf(Content::class, $result);
        self::assertEquals(1, $result->getTxRkwnewsletterIsEditorial());
        self::assertEquals('Content 71.1', $result->getHeader());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsContentIfSpecialTopicOnlyWithEditorial()
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
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with an empty ObjectStorage before (= no topics set)
         * When the method is called
         * Then an object of instance \RKW\RkwNewsletter\Domain\Model\Content is returned
         * Then this object is the editorial of the given topic B
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(100);

        $this->subject->setIssue($issue);
        $this->subject->setTopics(new ObjectStorage());

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $result */
        $result = $this->subject->getEditorial();

        self::assertInstanceOf(Content::class, $result);
        self::assertEquals(1, $result->getTxRkwnewsletterIsEditorial());
        self::assertEquals('Content 71.1', $result->getHeader());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getEditorialReturnsContentIfEmptyTopicAndSpecialTopicWithEditorial()
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
         * Given the page-object Q contains no content-objects
         * Given none of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic A only
         * When the method is called
         * Then an object of instance \RKW\RkwNewsletter\Domain\Model\Content is returned
         * Then this object is the editorial of the given topic B
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(150);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(150);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $result */
        $result = $this->subject->getEditorial();

        self::assertInstanceOf(Content::class, $result);
        self::assertEquals(1, $result->getTxRkwnewsletterIsEditorial());
        self::assertEquals('Content 151.1', $result->getHeader());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPagesReturnsRelevantPagesInTopicOrder()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted topic-object C that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given that page-object S belongs to the newsletter-object X
         * Given that page-object S belongs to the issue-object Y
         * Given that page-object S belongs to the topic-object C
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B/topic A
         * When the method is called
         * Then an array is returned
         * Then this array contains two items of \RKW\RkwNewsletter\Domain\Model\Pages
         * Then the first item is page R
         * Then the second item is page Q
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(121);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        /** @var array $result */
        $result = $this->subject->getPages();

        self::assertIsArray( $result);
        self::assertCount(2, $result);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $result[0];
        self::assertInstanceOf(Pages::class, $page);
        self::assertEquals(121 , $page->getUid());

        $page = $result[1];
        self::assertInstanceOf(Pages::class, $page);
        self::assertEquals(120, $page->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPagesReturnsEmptyArray()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted topic-object C that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given that page-object S belongs to the newsletter-object X
         * Given that page-object S belongs to the issue-object Y
         * Given that page-object S belongs to the topic-object C
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with an empty ObjectStorage (=no topics set)
         * When the method is called
         * Then an array is returned
         * Then this array is empty
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(120);

        $this->subject->setIssue($issue);
        $this->subject->setTopics(new ObjectStorage());

        /** @var array $result */
        $result = $this->subject->getPages();

        self::assertIsArray( $result);
        self::assertCount(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPagesReturnsIgnoresPagesWithoutTopic()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted topic-object B that belongs to the newsletter-object X
         * Given a persisted topic-object C that belongs to the newsletter-object X
         * Given a persisted page-object Q
         * Given that page-object Q belongs to the newsletter-object X
         * Given that page-object Q belongs to the issue-object Y
         * Given that page-object Q belongs to the topic-object A
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to no topic-object
         * Given that page-object S belongs to the newsletter-object X
         * Given that page-object S belongs to the issue-object Y
         * Given that page-object S belongs to the topic-object C
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B/topic A
         * When the method is called
         * Then an array is returned
         * Then this array contains one item of \RKW\RkwNewsletter\Domain\Model\Pages
         * Then this item is page Q
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check130.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(130);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(130);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(131);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        /** @var array $result */
        $result = $this->subject->getPages();

        self::assertIsArray( $result);
        self::assertCount(1, $result);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $result[0];
        self::assertInstanceOf(Pages::class, $page);
        self::assertEquals(131 , $page->getUid());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function hasContentReturnsTrue()
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
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains one content-object
         * Given this content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B/topic A
         * When the method is called
         * Then true returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(141);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->hasContents());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function hasContentReturnsTrueOnEditorialOnly()
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
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains one content-object
         * Given this content-object is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B only
         * When the method is called
         * Then true returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(141);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertTrue($this->subject->hasContents());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function countTopicsWithContentsReturnsOneForNormalContent ()
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
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains one content-object
         * Given this content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B/topic A
         * When the method is called
         * Then one returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(141);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertEquals(1, $this->subject->countTopicsWithContents());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function countTopicsWithContentsReturnsZeroForTopicWithEditorialOnly()
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
         * Given one of the content-objects is an editorial
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains one content-object
         * Given this content-object is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic B only
         * When the method is called
         * Then true returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(140);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic2 */
        $topic2 = $this->topicRepository->findByUid(141);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic2);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertEquals(0, $this->subject->countTopicsWithContents());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function countTopicsWithContentsReturnsOneForNonSetSpecialTopic()
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
         * Given the page-object Q contains no content-objects
         * Given that page-object R belongs to the newsletter-object X
         * Given that page-object R belongs to the issue-object Y
         * Given that page-object R belongs to the topic-object B
         * Given the page-object R contains three content-objects
         * Given one of the content-objects is an editorial
         * Given the issue-object is set via setIssue before
         * Given setTopics is called with topic A only
         * When the method is called
         * Then one is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(150);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic1 */
        $topic1 = $this->topicRepository->findByUid(150);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($topic1);

        $this->subject->setIssue($issue);
        $this->subject->setTopics($objectStorage);

        self::assertEquals(1, $this->subject->countTopicsWithContents());
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
