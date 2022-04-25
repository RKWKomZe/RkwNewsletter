<?php
namespace RKW\RkwNewsletter\Tests\Integration\Manager;

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
use RKW\RkwBasics\Domain\Model\FileReference;
use RKW\RkwBasics\Domain\Repository\FileReferenceRepository;
use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Newsletter;
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use RKW\RkwNewsletter\Domain\Repository\ContentRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use RKW\RkwNewsletter\Manager\IssueManager;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * IssueManagerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueManagerTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/IssueManagerTest/Fixtures';

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
     * @var \RKW\RkwNewsletter\Manager\IssueManager
     */
    private $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     */
    private $newsletterRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     */
    private $topicRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     */
    private $pagesRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     */
    private $contentRepository;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\FileReferenceRepository
     */
    private $fileReferenceRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     */
    private $approvalRepository;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_newsletter/Configuration/TypoScript/constants.typoscript',
                static::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->newsletterRepository = $this->objectManager->get(NewsletterRepository::class);
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
        $this->contentRepository = $this->objectManager->get(ContentRepository::class);
        $this->fileReferenceRepository = $this->objectManager->get(FileReferenceRepository::class);
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->subject = $this->objectManager->get(IssueManager::class);

        // For Mail-Interface
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'service@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyName'] = 'RKW';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToAddress'] = 'reply@mein.rkw.de';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] = 'bounces@mein.rkw.de';
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function replaceTitlePlaceholdersReplacesYear()
    {

        /**
         * Scenario:
         *
         * Given a string with {Y} placeholder
         * When the method is called
         * Then the placeholder is replaced with the current year
         */
        
        $result = $this->subject->replaceTitlePlaceholders('this year is {Y}');
        self::assertEquals('this year is '. date('Y'), $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function replaceTitlePlaceholdersReplacesMonth()
    {

        /**
         * Scenario:
         *
         * Given a string with {M} placeholder
         * When the method is called
         * Then the placeholder is replaced with the current year
         */

        $result = $this->subject->replaceTitlePlaceholders('this month is {M}');
        self::assertEquals('this month is '. date('m'), $result);
    }
    
    //=============================================
    
    /**
     * @test
     * @throws \Exception
     */
    public function createIssueThrowsExceptionIfNewsletterNotPersisted()
    {

        /**
         * Scenario:
         *
         * Given a newsletter-object that is not persisted
         * When the method is called
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1639058270
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1639058270);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = GeneralUtility::makeInstance(Newsletter::class);
        $this->subject->createIssue($newsletter);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function createIssueSetsTitleAndStatusToIssue()
    {

        /**
         * Scenario:
         *
         * Given a newsletter-object that is persisted
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Issue is returned
         * Then the title of this instance is set to the title set in the newsletter-object
         * Then the stage of the instance is set to zero
         * Then the isSpecial-attribute is set to false
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->subject->createIssue($newsletter);
        
        self::assertInstanceOf(Issue::class, $issue);
        self::assertEquals('Newsletter ' . date('m') . '/' . date('Y'), $issue->getTitle());
        self::assertEquals(0, $issue->getStatus());
        self::assertEquals(false, $issue->getIsSpecial());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function createIssueSetsIsSpecial()
    {

        /**
         * Scenario:
         *
         * Given a newsletter-object that is persisted
         * When the method is called with isSpecial-Parameter
         * Then an instance of \RKW\RkwNewsletter\Model\Issue is returned
         * Then the title of this instance is set to the title set in the newsletter-object plus special marks
         * Then the stage of the instance is set to zero
         * Then the isSpecial-Attribute is set to true
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->subject->createIssue($newsletter, true);

        self::assertInstanceOf(Issue::class, $issue);
        self::assertEquals('SPECIAL: Newsletter ' . date('m') . '/' . date('Y'), $issue->getTitle());
        self::assertEquals(0, $issue->getStatus());
        self::assertEquals(true, $issue->getIsSpecial());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function createIssuePersistsIssueAndAddsItToNewsletter()
    {

        /**
         * Scenario:
         *
         * Given a newsletter-object that is persisted
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Issue is returned
         * Then the instance is persisted
         * Then the instance is added as issue to the newsletter-object
         * Then this newsletter-object is also persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->subject->createIssue($newsletter);
        
        self::assertInstanceOf(Issue::class, $issue);

        /** @var  \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $issuesDb */
        $issuesDb = $this->issueRepository->findAll();
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $issuesDb->getFirst();
        self::assertCount(1, $issuesDb);
        self::assertEquals($issueDb, $issue);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletterDb */
        $newsletterDb = $this->newsletterRepository->findByUid(10);

        self::assertCount(1, $newsletterDb->getIssue());
        $newsletterDb->getIssue()->rewind();
        self::assertEquals($issueDb, $newsletterDb->getIssue()->current());

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function createPageThrowsExceptionIfNoContainerPage()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object 
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the topic-object has no container-page defined
         * When the method is called
         * Then an exception is thrown
         * Then the exception is an instance of \RKW\RkwNewsletter\Exception
         * Then the exception has the code 1641967659
         */
        static::expectException(\RKW\RkwNewsletter\Exception::class);
        static::expectExceptionCode(1641967659);

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $this->subject->createPage($newsletter, $topic, $issue);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function createPagesSetsProperties()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the topic-object has a container-page defined
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Pages is returned
         * Then the txRkwnewsletterNewsletter-property of this instance is set to the newsletter-object
         * Then the txRkwnewsletterTopic-property of this instance is set to the topic-object
         * Then the title-property of this instance is set to the title of the issue
         * Then the dokType-property of this instance is set to the value 1
         * Then the pid-property of this instance is set to the container-page of the topic-object
         * Then the no-search-property of this instance is set to true
         * Then the txRkwnewsletterExclude-property of this instance is set to true
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->subject->createPage($newsletter, $topic, $issue);

        self::assertInstanceOf(Pages::class, $page);
        self::assertEquals($newsletter, $page->getTxRkwnewsletterNewsletter());
        self::assertEquals($topic, $page->getTxRkwnewsletterTopic());
        self::assertEquals($issue->getTitle(), $page->getTitle());
        self::assertEquals(1, $page->getDokType());
        self::assertEquals($topic->getContainerPage()->getUid(), $page->getPid());
        self::assertEquals(true, $page->getNoSearch());
        self::assertEquals(true, $page->getTxRkwnewsletterExclude());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createPagePersistsPageAndAddsItToIssue()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the topic-object has a container-page defined
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Pages is returned
         * Then the instance is persisted
         * Then the txRkwnewsletterNewsletter-property of this persisted instance is set to the newsletter-object
         * Then the txRkwnewsletterTopic-property of this persisted instance is set to the topic-object
         * Then the title-property of this persisted instance is set to the title of the issue
         * Then the dokType-property of this persisted instance is set to the value 1
         * Then the pid-property of this persisted instance is set to the container-page of the topic-object
         * Then the no-search-property of this persisted instance is set to true
         * Then the txRkwnewsletterExclude-property of this persisted instance is set to true
         * Then the instance is added as page to the issue-object
         * Then this issue-object is also updated
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->subject->createPage($newsletter, $topic, $issue);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        self::assertInstanceOf(Pages::class, $page);

        /** @var  \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $pagesDb */
        $pagesDb = $this->pagesRepository->findAll()->toArray();
        self::assertCount(1, $pagesDb);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $pageDb */
        $pageDb = $pagesDb[0];
        self::assertEquals($newsletter->getUid(), $pageDb->getTxRkwnewsletterNewsletter()->getUid());
        self::assertEquals($topic->getUid(), $pageDb->getTxRkwnewsletterTopic()->getUid());
        self::assertEquals($issue->getTitle(), $pageDb->getTitle());
        self::assertEquals(1, $pageDb->getDokType());
        self::assertEquals($topic->getContainerpage()->getUid(), $pageDb->getPid());
        self::assertEquals(true, $pageDb->getNoSearch());
        self::assertEquals(true, $pageDb->getTxRkwnewsletterExclude());

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(40);

        self::assertCount(1, $issueDb->getPages());
        $issueDb->getPages()->rewind();
        self::assertEquals($page->getUid(), $issueDb->getPages()->current()->getUid());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function createContentSetsPropertiesWithFallback()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted page-object that belongs to the issue-object
         * Given a persisted page-object with contents
         * Given the persisted page-object has no txRkwnewsletterTeaserHeading-property set
         * Given the persisted page-object has no txRkwnewsletterTeaserText-property set
         * Given the persisted page-object has no txRkwnewsletterTeaserLink-property set
         * Given the persisted page-object has no txRkwauthorsAuthorship-property set
         * Given the topic-object has a container-page defined
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Content is returned
         * Then the pid-property of this instance is set to the page-object that belongs to the issue-object
         * Then the sysLanguageUid-property of this instance is set to the sysLanguageUid-property of the newsletter-object
         * Then the contentType-property of this instance is set to the value 'textpic'
         * Then the header-property of this instance is set to the title of the page
         * Then the bodytext-property of this instance is set to the abstract of the page
         * Then the headerLink-property of this instance is set to the id of the page
         * Then no txRkwNewsletterAuthors-property is set
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(50);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(50);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(51);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->subject->createContent($newsletter, $targetPage, $page);

        self::assertInstanceOf(Content::class, $content);
        self::assertEquals($targetPage->getUid(), $content->getPid());
        self::assertEquals('textpic', $content->getContentType());
        self::assertEquals(1, $content->getImageCols());
        self::assertEquals($page->getTitle(), $content->getHeader());
        self::assertEquals($page->getAbstract(), $content->getBodytext());
        self::assertEquals('t3://page?uid=' . $page->getUid(), $content->getHeaderLink());
        self::assertCount(0, $content->getTxRkwnewsletterAuthors());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function createContentSetsProperties()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted page-object that belongs to the issue-object
         * Given the persisted page-object has a txRkwnewsletterTeaserHeading-property set
         * Given the persisted page-object has a txRkwnewsletterTeaserText-property set
         * Given the persisted page-object has a txRkwnewsletterTeaserLink-property set
         * Given the persisted page-object has a txRkwauthorsAuthorship-property set
         * Given the topic-object has a container-page defined
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Content is returned
         * Then the pid-property of this instance is set to the page-object
         * Then the sysLanguageUid-property of this instance is set to the sysLanguageUid-property of the newsletter-object
         * Then the contentType-property of this instance is set to the value 'textpic'
         * Then the header-property of this instance is set to the txRkwnewsletterTeaserHeading-property of the page
         * Then the bodytext-property of this instance is set to the txRkwnewsletterTeaserText-property of the page
         * Then the headerLink-property of this instance is set to the txRkwnewsletterTeaserLink-property of the page
         * Then the txRkwNewsletterAuthors-property of this instance is set to the txRkwNewsletterAuthors-property of the page
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(60);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(60);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(61);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->subject->createContent($newsletter, $targetPage, $page);

        self::assertInstanceOf(Content::class, $content);
        self::assertEquals($targetPage->getUid(), $content->getPid());
        self::assertEquals('textpic', $content->getContentType());
        self::assertEquals(1, $content->getImageCols());
        self::assertEquals('Header', $content->getHeader());
        self::assertEquals('Text', $content->getBodytext());
        self::assertEquals('http://www.google.de', $content->getHeaderLink());
        self::assertCount(1, $content->getTxRkwnewsletterAuthors());
        $content->getTxRkwnewsletterAuthors()->rewind();
        self::assertEquals(60, $content->getTxRkwnewsletterAuthors()->current()->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createContentPersistsContent()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted page-object that belongs to the issue-object
         * Given the topic-object has a container-page defined
         * When the method is called
         * Then an instance of \RKW\RkwNewsletter\Model\Content is returned
         * Then the instance is persisted
         * Then the pid-property of this persisted instance is set to the page-object
         * Then the sysLanguageUid-property of this persisted instance is set to the sysLanguageUid-property of the newsletter-object
         * Then the contentType-property of this persisted instance is set to the value 'textpic'
         * Then the header-property of this persisted instance is set to the txRkwnewsletterTeaserHeading-property of the page
         * Then the bodytext-property of this persisted instance is set to the txRkwnewsletterTeaserText-property of the page
         * Then the headerLink-property of this persisted instance is set to the txRkwnewsletterTeaserLink-property of the page
         * Then the txRkwNewsletterAuthors-property of this persisted instance is set to the txRkwNewsletterAuthors-property of the page
         */
        
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(70);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(70);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(71);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->subject->createContent($newsletter, $targetPage, $page);
        
        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();
        
        self::assertInstanceOf(Content::class, $content);
        
        /** @var  \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $contentsDb */
        $contentsDb = $this->contentRepository->findAll();
        self::assertCount(1, $contentsDb);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $contentDb*/
        $contentDb = $contentsDb->getFirst();
        self::assertEquals($content->getUid(), $contentDb->getUid());
        self::assertEquals($targetPage->getUid(), $contentDb->getPid());
        self::assertEquals('textpic', $contentDb->getContentType());
        self::assertEquals(1, $contentDb->getImageCols());
        self::assertEquals('Header', $contentDb->getHeader());
        self::assertEquals('Text', $contentDb->getBodytext());
        self::assertEquals('http://www.google.de', $contentDb->getHeaderLink());
        self::assertCount(1, $contentDb->getTxRkwnewsletterAuthors());
        $contentDb->getTxRkwnewsletterAuthors()->rewind();
        self::assertEquals(70, $contentDb->getTxRkwnewsletterAuthors()->current()->getUid());

    }
    

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function createImageSetsReference()
    {

        /**
         * Scenario:
         *
         * Given a persisted file-object 
         * Given a persisted content-object
         * Given a persisted fileReference-object between the page- and file-object
         * When the method is called
         * Then an instance of \RKW\RkwBasics\Model\FileReference is returned
         * Then the file-property is identical to the file-property of the fileReference-object
         * Then the tableLocal-property is identical to the tableLocale-property of the fileReference-object
         * Then the fieldname-property of this instance is set to 'image'
         * Then the tablenames-property of this instance is set to 'tt_content'
         * Then the uidForeign-property of this instance is set to the uid of the content-object
         * Then the pid-property of this instance is set to the pid of the content-object
         * Then the image-property of the content-object is updated to the amount of images referenced (1)
         * Then the image-property returns the file-reference 
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReferenceSource */
        $fileReferenceSource = $this->fileReferenceRepository->findByUid(80);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(80);

        /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReference */
        $fileReference = $this->subject->createFileReference($fileReferenceSource, $content);

        self::assertInstanceOf(FileReference::class, $fileReference);
        self::assertEquals($fileReferenceSource->getFile(), $fileReference->getFile());
        self::assertEquals($fileReferenceSource->getTableLocal(), $fileReference->getTableLocal());
        self::assertEquals('image', $fileReference->getFieldname());
        self::assertEquals('tt_content', $fileReference->getTablenames());
        self::assertEquals($content->getUid(), $fileReference->getUidForeign());
        self::assertEquals($content->getPid(), $fileReference->getPid());

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $contentDbCount =  $queryBuilder->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(80, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchColumn(0);

        self::assertEquals(1, $contentDbCount);
        
        // force TYPO3 to load object new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(80);

        self::assertCount(1, $content->getImage());

    }

    //=============================================
    
    /**
     * @test
     * @throws \Exception
     */
    public function buildContentsBuildsContentsSelectively()
    {
        
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted page-object as container-page
         * Given for the topic-object six persisted page-objects exist that belong to it
         * Given page-object one is hidden
         * Given page-object two is marked to be excluded
         * Given page-object three is marked as already used for a newsletter-issue
         * Given page-object four has the wrong doktype
         * Given page-object five and six have no properties that exclude them from being used
         * Given there is a seventh page-object that does not belong to the topic-object
         * When the method is called
         * Then true is returned
         * Then the contents of the page-objects one to four and seven are ignored
         * Then the contents of the page-objects four and five are created in the container-page
         * Then the txRkwnewsletterIncludeTstamp for the included page-objects five and six are set
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(90);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(90);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $result = $this->subject->buildContents($newsletter, $topic, $targetPage);
        
        self::assertTrue($result);
        
        $contents = $this->contentRepository->findByPid(90)->toArray();
        self::assertCount(2, $contents);
        self::assertEquals('t3://page?uid=95', $contents[0]->getHeaderLink());
        self::assertEquals('Use One', $contents[0]->getHeader());
        
        self::assertEquals('t3://page?uid=96', $contents[1]->getHeaderLink());
        self::assertEquals('Use Two', $contents[1]->getHeader());

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(95);
        self::assertGreaterThan(0, $page->getTxRkwnewsletterIncludeTstamp());

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(96);
        self::assertGreaterThan(0, $page->getTxRkwnewsletterIncludeTstamp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildContentsUsesRkwBasicsTeaserImage()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted page-object as container-page
         * Given for the topic-object there exists a persisted page-object that belongs to it
         * Given page-object has a file-reference to a file for the txRkwbasicsTeaserImage-property set
         * Given page-object has no file-reference to a file for the txRkwNewsletterTeaserImage-property set
         * When the method is called
         * Then true is returned
         * Then the content of the page-object is created in the container-page
         * Then the new content has one file-reference in the image-property
         * Then this file-references refers to the image of the txRkwbasicsTeaserImage-property of the page-object
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check100.xml');

        
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(100);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(100);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(100);

        $result = $this->subject->buildContents($newsletter, $topic, $targetPage);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();
        
        $contents = $this->contentRepository->findByPid(100)->toArray();
        self::assertCount(1, $contents);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $contents[0];
        
        /** @var \RKW\RkwBasics\Domain\Model\File $file */
        $file =  $content->getImage()->current()->getFile();

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(101);
        self::assertEquals($page->getTxRkwbasicsTeaserImage()->getFile(), $file);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildContentsUsesRkwNewsletterTeaserImage()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given a persisted topic-object that belongs to the newsletter-object
         * Given a persisted page-object as container-page
         * Given for the topic-object there exists a persisted page-object that belongs to it
         * Given page-object has a file-reference to a file for the txRkwbasicsTeaserImage-property set
         * Given page-object has a file-reference to a file for the txRkwNewsletterTeaserImage-property set
         * When the method is called
         * Then true is returned
         * Then the content of the page-object is created in the container-page
         * Then the new content has one file-reference in the image-property
         * Then this file-references refers to the image of the txRkwNewsletterTeaserImage-property of the page-object
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(110);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(110);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $targetPage */
        $targetPage = $this->pagesRepository->findByUid(110);

        $result = $this->subject->buildContents($newsletter, $topic, $targetPage);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $contents = $this->contentRepository->findByPid(110)->toArray();
        self::assertCount(1, $contents);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $contents[0];
        
        /** @var \RKW\RkwBasics\Domain\Model\File $file */
        $file =  $content->getImage()->current()->getFile();

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid(111);
        self::assertEquals($page->getTxRkwnewsletterTeaserImage()->getFile(), $file);
        
    }
    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function buildPagesReturnsFalseWhenNoTopicsDefined()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given no persisted topic-objects belong to the newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check130.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(130);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(130);

        $result = $this->subject->buildPages($newsletter, $issue);
        self::assertFalse($result);

    }
    
    
    /**
     * @test
     * @throws \Exception
     */
    public function buildPagesCreatesPageForEachTopic()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given two persisted topic-objects that belong to the newsletter-object
         * Given each topic-object has a container-page set
         * Given these container-pages are persisted
         * Given a persisted issue-object that belongs to the newsletter-object
         * When the method is called
         * Then true is returned
         * Then two content-pages are created 
         * Then each content-page is a subpages of the container-page of the corresponding topic
         * Then each content-page has the newsletter and the corresponding topic as reference
         * Then for each content-page an approval-object is created
         * Then each approval-object has the content-page and the corresponding topic as reference
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicOne */
        $topicOne = $this->topicRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicTwo */
        $topicTwo = $this->topicRepository->findByUid(121);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(120);
        
        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $countBefore = $queryBuilder->count('uid')
            ->from('pages')
            ->execute()
            ->fetchColumn(0);
        
        $result = $this->subject->buildPages($newsletter, $issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $countAfter = $queryBuilder->count('uid')
            ->from('pages')
            ->execute()
            ->fetchColumn(0);

        self::assertEquals(2, $countAfter - $countBefore);
            
        $pageOne = $this->pagesRepository->findByUid(122);
        self::assertEquals(120, $pageOne->getPid());
        self::assertEquals($newsletter->getUid(), $pageOne->getTxRkwnewsletterNewsletter()->getUid());
        self::assertEquals($topicOne->getUid(), $pageOne->getTxRkwnewsletterTopic()->getUid());
        
        $pageTwo = $this->pagesRepository->findByUid(123);
        self::assertEquals(121, $pageTwo->getPid());
        self::assertEquals($newsletter->getUid(), $pageTwo->getTxRkwnewsletterNewsletter()->getUid());
        self::assertEquals($topicTwo->getUid(), $pageTwo->getTxRkwnewsletterTopic()->getUid());

        $approvals = $this->approvalRepository->findAll()->toArray();
        self::assertCount(2, $approvals);

        self::assertEquals($pageOne, $approvals[0]->getPage());
        self::assertEquals($pageOne->getTxRkwnewsletterTopic(), $approvals[0]->getTopic());
        
        self::assertEquals($pageTwo, $approvals[1]->getPage());
        self::assertEquals($pageTwo->getTxRkwnewsletterTopic(), $approvals[1]->getTopic());  

    }


    /**
     * @test
     * @throws \Exception
     */
    public function buildPagesCreatesPageForGivenTopic()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given two persisted topic-objects A and B that belong to the newsletter-object
         * Given each topic-object has a container-page set
         * Given these container-pages are persisted
         * Given a persisted issue-object that belongs to the newsletter-object
         * When the method is called with topic A as parameter
         * Then true is returned
         * Then one content-page is created
         * Then the content-page is a subpages of the container-page of topic A
         * Then the content-page has the newsletter and  topic A as reference
         * Then for the content-page an approval-object is created
         * Then the approval-object has the content-page and the topic A as reference
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check120.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicOne */
        $topicOne = $this->topicRepository->findByUid(120);

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(120);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class)
            ->removeByType(HiddenRestriction::class)
            ->removeByType(DeletedRestriction::class);

        $countBefore = $queryBuilder->count('uid')
            ->from('pages')
            ->execute()
            ->fetchColumn(0);

        $result = $this->subject->buildPages($newsletter, $issue, [$topicOne]);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $countAfter = $queryBuilder->count('uid')
            ->from('pages')
            ->execute()
            ->fetchColumn(0);

        self::assertEquals(1, $countAfter - $countBefore);

        $pageOne = $this->pagesRepository->findByUid(122);
        self::assertEquals(120, $pageOne->getPid());
        self::assertEquals($newsletter->getUid(), $pageOne->getTxRkwnewsletterNewsletter()->getUid());
        self::assertEquals($topicOne->getUid(), $pageOne->getTxRkwnewsletterTopic()->getUid());

        $approvals = $this->approvalRepository->findAll()->toArray();
        self::assertCount(1, $approvals);

        self::assertEquals($pageOne, $approvals[0]->getPage());
        self::assertEquals($pageOne->getTxRkwnewsletterTopic(), $approvals[0]->getTopic());

    }


    //=============================================
    /**
    * @test
    * @throws \Exception
    */
    public function buildIssueReturnsFalseWhenNoTopicsDefined()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given no topic-objects belong to the newsletter-object
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check140.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(140);

        $result = $this->subject->buildIssue($newsletter);
        self::assertFalse($result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildIssueCreatesIssueAndSetsStatus ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given two persisted topic-objects belong to the newsletter-object
         * Given each persisted topic-object has a persisted container-page defined
         * When the method is called
         * Then true is returned
         * Then an issue is created and persisted
         * Then the stage of the issue is set to the value 1
         * Then the isSpecial-value of the issue is not set
         * Then the lastIssueTimestamp of the newsletter-object is set to the current time
         * Then this change of the newsletter-object is persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(150);

        $result = $this->subject->buildIssue($newsletter);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(1, $issues);
        self::assertEquals(1, $issues[0]->getStatus());
        self::assertEquals(false, $issues[0]->getIsSpecial());

        self::assertLessThanOrEqual(time(), $newsletter->getLastIssueTstamp());
        self::assertGreaterThan(time()-10, $newsletter->getLastIssueTstamp());

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletterDb */
        $newsletterDb = $this->newsletterRepository->findByUid(150);
        self::assertEquals($newsletter->getLastIssueTstamp(), $newsletterDb->getLastIssueTstamp());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildIssueCreatesManualIssueWithGivenTopicsAndSetsStatus ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given two persisted topic-objects A and B belong to the newsletter-object
         * Given each persisted topic-object has a persisted container-page defined
         * When the method is called with topic A as parameter
         * Then true is returned
         * Then an issue is created and persisted
         * Then the stage of the issue is set to the value 1
         * Then no lastIssueTimestamp for the newsletter-object is set 
         * Then the isSpecial-value of the issue is set
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check150.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid(150);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topicOne */
        $topic = $this->topicRepository->findByUid(150);
        
        $result = $this->subject->buildIssue($newsletter, [$topic]);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(1, $issues);
        self::assertEquals(1, $issues[0]->getStatus());
        self::assertEquals(true, $issues[0]->getIsSpecial());

        self::assertEquals(0, $newsletter->getLastIssueTstamp());

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsFalseWhenNoNewsletterDueMonthly ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with monthly rhythm
         * Given to each newsletter-object belong two persisted topic-objects 
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of February
         * Given today is the 25th of February
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 2  , 25, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 2  , 15, date("Y"));

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertFalse($result);

    }
    

    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsTrueWhenNewsletterDueMonthly ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with monthly rhythm
         * Given to each newsletter-object belong two persisted topic-objects
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of January
         * Given today is the 14th of February
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then true is returned
         * Then two issues are created
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 2  , 14, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 1  , 15, date("Y"));

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(2, $issues);

    }
        

    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsTrueWhenNewsletterDueMonthlyBetweenYears ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with monthly rhythm
         * Given to each newsletter-object belong two persisted topic-objects
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of December last year
         * Given today is the 14th of January
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then true is returned
         * Then two issues are created
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check160.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 1  , 14, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 12  , 15, date("Y") -1);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(2, $issues);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsFalseWhenNoNewsletterDueQuarterly ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with quarterly rhythm
         * Given to each newsletter-object belong two persisted topic-objects
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of January
         * Given today is the 25th of March
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check170.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 3  , 25, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 1  , 15, date("Y"));

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertFalse($result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsTrueWhenNewsletterDueQuarterly ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with quarterly rhythm
         * Given to each newsletter-object belong two persisted topic-objects
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of January
         * Given today is the 2nd of April
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check170.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 4  , 2, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 1  , 15, date("Y"));

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(2, $issues);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function buildAllIssuesReturnsTrueWhenNewsletterDueQuarterlyBetweenYears ()
    {
        /**
         * Scenario:
         *
         * Given two persisted newsletter-objects with quarterly rhythm
         * Given to each newsletter-object belong two persisted topic-objects
         * Given each persisted topic-object has a persisted container-page defined
         * Given both of the newsletter-objects were sent on the 15th of October
         * Given today is the 2nd of January the next year
         * Given the tolerance is set to fifteen days
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check170.xml');

        // prepare timestamps for test
        $timestampNow = mktime(0, 0, 0, 1  , 2, date("Y"));
        $timestampNewsletter = mktime(0, 0, 0, 10  , 15, date("Y") - 1);

        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_rkwnewsletter_domain_model_newsletter');

        $updateQueryBuilder = $connection->createQueryBuilder();
        $updateQueryBuilder->update('tx_rkwnewsletter_domain_model_newsletter')
            ->set('last_issue_tstamp', $timestampNewsletter);

        $updateQueryBuilder->execute();

        // tolerance is 15 days (1209600) for testing
        $result = $this->subject->buildAllIssues(1209600, 15, $timestampNow);
        self::assertTrue($result);

        $issues = $this->issueRepository->findAll()->toArray();
        self::assertCount(2, $issues);

    }


    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseForWrongStage()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "approval"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);
        $issue->setStatus(IssueStatus::STAGE_APPROVAL);

        $result = $this->subject->increaseLevel($issue);
        self::assertFalse($result);

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsTrueAndSetsFirstLevel()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then true is returned
         * Then the infoTstamp-property is set
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check260.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(260);

        $result = $this->subject->increaseLevel($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issueDb = $this->issueRepository->findByUid(260);
        self::assertGreaterThan(0, $issueDb->getInfoTstamp());
    }

    

    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsTrueAndSetSecondLevel()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property is set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then true is returned
         * Then the reminderTstamp-property is set
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check270.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(270);

        $result = $this->subject->increaseLevel($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issueDb = $this->issueRepository->findByUid(270);
        self::assertGreaterThan(0, $issueDb->getReminderTstamp());
        
    }


    /**
     * @test
     * @throws \Exception
     */
    public function increaseLevelReturnsFalseForLevel2()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property is set
         * Given the issue-object has a value for the reminderTstamp-property set
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check280.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(280);

        
        $result = $this->subject->increaseLevel($issue);
        self::assertFalse($result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStageDraft()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "draft"
         * When the method is called
         * Then true is returned
         * Then the stage is set to "approval"
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check290.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(290);
        $issue->setStatus(IssueStatus::STAGE_DRAFT);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(290);
        self::assertEquals(IssueStatus::STAGE_APPROVAL, $issueDb->getStatus());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStageApproval()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "approval"
         * When the method is called
         * Then true is returned
         * Then the stage is set to "release"
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check290.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(290);
        $issue->setStatus(IssueStatus::STAGE_APPROVAL);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(290);
        self::assertEquals(IssueStatus::STAGE_RELEASE, $issueDb->getStatus());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStageRelease()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "release"
         * When the method is called
         * Then true is returned
         * Then the stage is set to "sending"
         * Then the releaseTstamp-property is set
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check290.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(290);
        $issue->setStatus(IssueStatus::STAGE_RELEASE);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(290);
        self::assertEquals(IssueStatus::STAGE_SENDING, $issueDb->getStatus());
        self::assertGreaterThan(0, $issueDb->getReleaseTstamp());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsTrueForStageSending()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "sending"
         * When the method is called
         * Then true is returned
         * Then the stage is set to "done"
         * Then the sentTstamp-property is set
         * Then the changes to the issue-object are persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check290.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(290);
        $issue->setStatus(IssueStatus::STAGE_SENDING);

        $result = $this->subject->increaseStage($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issueDb */
        $issueDb = $this->issueRepository->findByUid(290);
        self::assertEquals(IssueStatus::STAGE_DONE, $issueDb->getStatus());
        self::assertGreaterThan(0, $issueDb->getSentTstamp());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function increaseStageReturnsFalseForStageDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted issue-object
         * Given the issue-object has the stage "done"
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check290.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(290);
        $issue->setStatus(IssueStatus::STAGE_DONE);

        $result = $this->subject->increaseStage($issue);
        self::assertFalse($result);
        
    }

    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientReturnsEmptyArray()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has no approval-admins set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given that issue-object has the stage "release"
         * When the method is called
         * Then an empty array is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check230.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(230);

        self::assertEmpty($this->subject->getMailRecipients($issue));
    }

    
    
    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipients()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given that issue-object has the stage "release"
         * When the method is called
         * Then an array is returned
         * Then this array contains the two be-users
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(240);

        $result = $this->subject->getMailRecipients($issue);
        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals(240, $result[0]->getUid());
        self::assertEquals(241, $result[1]->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsEmptyArrayOnWrongStage()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given that issue-object has the stage "approval"
         * When the method is called
         * Then an empty array is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check240.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(240);
        $issue->setStatus(IssueStatus::STAGE_APPROVAL);

        self::assertEmpty($this->subject->getMailRecipients($issue));

    }
    
    
    /**
     * @test
     * @throws \Exception
     */
    public function getMailRecipientsReturnsRecipientsAndChecksForEmail()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given one of the approval-admins has an invalid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given that issue-object has the stage "release"
         * When the method is called
         * Then an array is returned
         * Then this array contains the be-user with the valid email-address
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check250.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(250);

        $result = $this->subject->getMailRecipients($issue);
        self::assertInternalType('array', $result);
        self::assertCount(1, $result);
        self::assertEquals(251, $result[0]->getUid());
    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsZeroForStageApproval()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "approval"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check300.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(300);
        $issue->setStatus(IssueStatus::STAGE_APPROVAL);

        $result = $this->subject->sendMails($issue);
        self::assertEquals(0, $result);

    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStageReleaseLevel1()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check300.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(300);

        $result = $this->subject->sendMails($issue);
        self::assertEquals(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsOneForStageReleaseLevel2()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check310.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(310);

        $result = $this->subject->sendMails($issue);
        self::assertEquals(1, $result);
    }



    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsTwoForStageReleaseLevelDone()
    {

        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has a value for the reminderTstamp-property set
         * When the method is called
         * Then the value 2 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check320.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(320);

        $result = $this->subject->sendMails($issue);
        self::assertEquals(2, $result);
    }
    
    /**
     * @test
     * @throws \Exception
     */
    public function sendMailsReturnsZeroIfNoRecipients()
    {


        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has no approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * When the method is called
         * Then the value 1 is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check330.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(330);

        $result = $this->subject->sendMails($issue);
        self::assertEquals(0, $result);

    }
    
    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseIfOneApprovalNotReady ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "approval"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given one of the approval-objects have the allowedTstampStage2-property set
         * Given one of the approval-objects have the allowedTstampStage2-property not set
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check180.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(180);

        $result = $this->subject->processConfirmation($issue);
        self::assertFalse($result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueIfAllApprovalsReady ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "approval"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * When the method is called
         * Then true is returned
         * Then the stage of the issue is set to "release"
         * Then the new status of the issue is persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(190);
        $issue->setStatus(IssueStatus::STAGE_APPROVAL);

        $result = $this->subject->processConfirmation($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(190);
        self::assertEquals(IssueStatus::STAGE_RELEASE, $issue->getStatus());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStageReleaseLevel1 ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * When the method is called
         * Then true is returned
         * Then the infoTstamp-property is set
         * Then the reminderTstamp-property is not set
         * Then the changes to the issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check190.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(190);

        $result = $this->subject->processConfirmation($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(190);
        self::assertGreaterThan(0, $issue->getInfoTstamp());
        self::assertEquals(0, $issue->getReminderTstamp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsTrueAndIncreasesLevelForStageReleaseLevel2 ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * When the method is called
         * Then true is returned
         * Then the infoTstamp-property is set
         * Then the reminderTstamp-property is set
         * Then the changes to the issue-object are persisted
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check200.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(200);

        $result = $this->subject->processConfirmation($issue);
        self::assertTrue($result);

        // force TYPO3 to load objects new from database
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(200);
        self::assertGreaterThan(0, $issue->getInfoTstamp());
        self::assertGreaterThan(0, $issue->getReminderTstamp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseForStageReleaseLevelDone ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has a value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check210.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(210);

        $result = $this->subject->processConfirmation($issue);
        self::assertFalse($result);
        
    }

    /**
     * @test
     * @throws \Exception
     */
    public function processConfirmationReturnsFalseForWrongStage ()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "sending"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check220.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(220);

        $result = $this->subject->processConfirmation($issue);
        self::assertFalse($result);

    }


    //=============================================


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfNotInRightStage()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "sending"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set 
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * When the method is called
         * Then zero is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check340.xml');
        
        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfInStageApprovalAndDueForInfoMail()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "approval"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * When the method is called
         * Then one is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check350.xml');

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfInStageReleaseAndDueForInfoMail()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has no value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * When the method is called
         * Then one is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check360.xml');

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(1, $result);

    }

    
    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfInStageReleaseAndDueForReminderMail()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * When the method is called
         * Then one is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check370.xml');

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfInStageReleaseAndNotDueForReminderMail()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has no value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * Given the infoTstamp-property is set to a value not older than 600 seconds from now
         * When the method is called
         * Then zero is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check370.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue*/
        $issue = $this->issueRepository->findByUid(370);
        $issue->setInfoTstamp(time() - 5);
        $this->issueRepository->update($issue);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(0, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsOneIfInStageReleaseAndDueForAutomaticConfirmation()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has a value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * When the method is called
         * Then one is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check380.xml');

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsZeroIfInStageReleaseAndNotDueForAutomaticConfirmation()
    {
        /**
         * Scenario:
         *
         * Given a persisted newsletter-object
         * Given that newsletter-object has two approval-admins set
         * Given all of the approval-admins have a valid email-address set
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given the issue-object has the stage "release"
         * Given the issue-object has a value for the infoTstamp-property set
         * Given the issue-object has a value for the reminderTstamp-property set
         * Given two persisted approval-objects
         * Given the two approval-objects belong to the issue-object
         * Given both of the approval-objects have the allowedTstampStage2-property set
         * Given the tolerance-parameter for the level has been set to 600 seconds
         * Given the reminerTstamp-property is set to a value not older than 600 seconds from now
         * When the method is called
         * Then zero is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check380.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue*/
        $issue = $this->issueRepository->findByUid(380);
        $issue->setReminderTstamp(time() - 5);
        $this->issueRepository->update($issue);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        $result = $this->subject->processAllConfirmations(600);
        self::assertEquals(0, $result);

    }
    
    
    /**
     * 
     * @throws \Exception
     */
    public function processAllConfirmationsReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a two persisted issue-objects
         * Given one of the issue-objects has the stage "release"
         * Given one of the issue-objects has the stage "approval"
         * When the method is called
         * Then true is returned
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check220.xml');

        $result = $this->subject->processAllConfirmations();
        self::assertTrue($result);

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