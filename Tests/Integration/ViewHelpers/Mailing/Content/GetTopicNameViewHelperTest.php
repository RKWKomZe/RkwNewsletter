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
use RKW\RkwNewsletter\Domain\Repository\ContentRepository;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;


/**
 * GetTopicNameViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetTopicNameViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GetTopicNameViewHelperTest/Fixtures';

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
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     */
    private $contentRepository;


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

        /** @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository contentRepository */
        $this->contentRepository = $this->objectManager->get(ContentRepository::class);

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
    public function itReturnsNameOfTopic()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted page-object B
         * Given that page-object B belongs to the newsletter-object X
         * Given that page-object B belongs to the issue-object Y
         * Given that page-object B belongs to the topic-object A
         * Given a persisted content-object C
         * Given that content-object C belongs to the page-object B
         * When the ViewHelper is rendered with content-object C as parameter
         * Then a string is returned
         * Then the name of topic-object A is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(10);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'content' => $content
            ]
        );

        $result = trim($this->standAloneViewHelper->render());
        self::assertIsString( $result);
        self::assertEquals('Topic 10', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsEmpty()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a persisted newsletter-object X
         * Given a persisted issue-object Y that belongs to the newsletter-object X
         * Given a persisted topic-object A that belongs to the newsletter-object X
         * Given a persisted page-object B
         * Given that page-object B belongs to the newsletter-object X
         * Given that page-object B belongs to the issue-object Y
         * Given that page-object B does not belong to the topic-object A
         * Given a persisted content-object C
         * Given that content-object C belongs to the page-object B
         * When the ViewHelper is rendered with content-object C as parameter
         * Then the topic-object A is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByUid(20);

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->contentRepository->findByUid(20);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'issue' => $issue,
                'content' => $content
            ]
        );

        self::assertEmpty(trim($this->standAloneViewHelper->render()));
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
