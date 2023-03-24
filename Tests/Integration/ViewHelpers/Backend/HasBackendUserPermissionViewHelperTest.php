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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;


/**
 * HasBackendUserPermissionViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class HasBackendUserPermissionViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/HasBackendUserPermissionViewHelperTest/Fixtures';


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
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    private ?IssueRepository $issueRepository = null;


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

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsZeroIfHasReleasePermissionButIsNotLoggedIn()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has the permission to release the issue
         * Given this backend-user is logged in
         * When the ViewHelper is rendered
         * Then the value 0 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            ['issue' => $issue]
        );


        self::assertEquals(0, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsOneIfUserHasReleasePermission()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has the permission to release the issue
         * Given this backend-user is logged in
         * When the ViewHelper is rendered
         * Then the value 1 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        $GLOBALS['BE_USER']->user['uid'] = 10;

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            ['issue' => $issue]
        );


        self::assertEquals(1, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsZeroIfUserHasReleasePermissionForOtherNewsletter()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object A
         * Given a persisted issue-object A that belongs to the newsletter-object A
         * Given a persisted newsletter-object B
         * Given a persisted issue-object B that belongs to the newsletter-object B
         * Given a persisted backend-user
         * Given that backend-user has the permission to release newsletter B
         * Given this backend-user is logged in
         * When the ViewHelper is rendered with issue A as parameter
         * Then the value 0 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        $GLOBALS['BE_USER']->user['uid'] = 20;

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assignMultiple(
            ['issue' => $issue]
        );


        self::assertEquals(0, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsZeroIfUserHasApprovalPermissionForAnyTopicOnReleaseOnly()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted topic-object A that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has not the permission to release the issue
         * Given that backend-user has the permission to approve the topic A on approvalsStage 1
         * Given this backend-user is logged in
         * When the ViewHelper is rendered without any topic- or stage-parameter
         * Then the value 0 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(70);

        $GLOBALS['BE_USER']->user['uid'] = 70;

        $this->standAloneViewHelper->setTemplate('Check70.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
            ]
        );

        self::assertEquals(0, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsOneIfUserHasApprovalPermissionForSpecificTopic()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted topic-object A that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has not the permission to release the issue
         * Given that backend-user has the permission to approve the topic A on approvalsStage 1
         * Given this backend-user is logged in
         * When the ViewHelper is rendered with approvalStage-parameter = 1 and topic-parameter = topic A
         * Then the value 1 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(30);

        $GLOBALS['BE_USER']->user['uid'] = 30;

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topic' => $topic,
                'approvalStage' => 1
            ]
        );

        self::assertEquals(1, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsOneIfUserHasApprovalPermissionForAnyTopic()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted topic-object A that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has not the permission to release the issue
         * Given that backend-user has the permission to approve the topic A on approvalsStage 1
         * Given this backend-user is logged in
         * When the ViewHelper is rendered without any topic- or stage-parameter, but with allApprovals-parameter = true
         * Then the value 1 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(60);

        $GLOBALS['BE_USER']->user['uid'] = 60;

        $this->standAloneViewHelper->setTemplate('Check60.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
            ]
        );

        self::assertEquals(1, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsZeroIfUserHasApprovalPermissionOnAnotherStage()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted topic-object A that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has not the permission to release the issue
         * Given that backend-user has the permission to approve the topic A on approvalsStage 1
         * Given this backend-user is logged in
         * When the ViewHelper is rendered with approvalStage-parameter = 2 and topic-parameter = topic A
         * Then the value 0 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(30);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(30);

        $GLOBALS['BE_USER']->user['uid'] = 30;

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topic' => $topic,
                'approvalStage' => 2
            ]
        );


        self::assertEquals(0, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsZeroIfUserHasApprovalPermissionForOtherNewsletter()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object A
         * Given a persisted issue-object A that belongs to the newsletter-object A
         * Given a persisted topic-object A that belongs to the newsletter-object A
         * Given a persisted newsletter-object A
         * Given a persisted issue-object B that belongs to the newsletter-object B
         * Given a persisted topic-object B that belongs to the newsletter-object B
         * Given a persisted backend-user
         * Given that backend-user has not the permission to release the issue of newsletter A
         * Given that backend-user has not the permission to release the issue of newsletter B
         * Given that backend-user has the permission to approve the topic B on approvalsStage 1 for newsletter B
         * Given this backend-user is logged in
         * When the ViewHelper is rendered with issue A as parameter and approvalStage = 1 as parameter and topic-parameter = topic B
         * Then the value 0 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(40);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $topic = $this->topicRepository->findByUid(40);

        $GLOBALS['BE_USER']->user['uid'] = 40;

        $this->standAloneViewHelper->setTemplate('Check40.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'topic' => $topic,
                'approvalStage' => 1
            ]
        );


        self::assertEquals(0, trim($this->standAloneViewHelper->render()));
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsTrueIfUserIsAdmin()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object
         * Given a persisted issue-object that belongs to the newsletter-object
         * Given a persisted backend-user
         * Given that backend-user has no permissions for the issue or the approvals
         * Given this backend-user is admin
         * Given this backend-user is logged in
         * When the ViewHelper is rendered
         * Then the value 1 is rendered
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(50);

        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $GLOBALS['BE_USER']->user['uid'] = 50;
        $GLOBALS['BE_USER']->user['admin'] = 1;

        $this->standAloneViewHelper->setTemplate('Check50.html');
        $this->standAloneViewHelper->assignMultiple(
            ['issue' => $issue]
        );


        self::assertEquals(1, trim($this->standAloneViewHelper->render()));
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
