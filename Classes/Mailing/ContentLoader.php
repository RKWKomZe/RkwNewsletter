<?php
namespace RKW\RkwNewsletter\Mailing;

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

use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * ContentLoader
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ContentLoader
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    protected $issue;


    /**
     * @var array
     */
    protected $ordering = [];


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    protected $topics;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $contentRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pagesRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Constructor
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue $issue
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function __construct(Issue $issue)
    {
        $this->topics = new ObjectStorage();
        $this->setIssue($issue);
    }


    /**
     * Gets the issue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue|null
     */
    public function getIssue()
    {
        return $this->issue;
    }


    /**
     * Sets the issue and sets topics accordingly based on pages
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue $issue
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $topics = new ObjectStorage();
        foreach($this->issue->getPages() as $page) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
            if ($topic = $page->getTxRkwnewsletterTopic()) {
                $topics->attach($topic);
            }
        }

        $this->setTopics($topics);
    }



    /**
     * Returns the current ordering
     *
     * @return array
     */
    public function getOrdering(): array
    {
        return $this->ordering;
    }


    /**
     * Gets the topics
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     */
    public function getTopics (): ObjectStorage
    {
        return $this->topics;
    }


    /**
     * Sets the topics
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setTopics (ObjectStorage $topics): void
    {
        // reset topics
        $this->topics = GeneralUtility::makeInstance(ObjectStorage::class);

        // add the given ones
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach($topics as $topic) {

            // check if topic belongs to current newsletter-configuration before we add it
            if ($this->issue->getNewsletter()->getTopic()->contains($topic)) {

                $this->topics->attach($topic);

            } else {

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'Topic with id=%s does not belong to issue with id=%s and is ignored.',
                        $topic->getUid(),
                        $this->issue->getUid()
                    )
                );
            }
        }

        $this->updateOrdering();
    }


    /**
     * Adds a topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function addTopic (Topic $topic): void
    {
        // check if topic belongs to current newsletter-configuration before we add it
        if ($this->issue->getNewsletter()->getTopic()->contains($topic)) {

            $this->topics->attach($topic);
            $this->updateOrdering();

        } else {

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Topic with id=%s does not belong to issue with id=%s and is ignored.',
                    $topic->getUid(),
                    $this->issue->getUid()
                )
            );
        }

    }


    /**
     * Removes a topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function removeTopic (Topic $topic): void
    {
        $this->topics->detach($topic);
        $this->updateOrdering();
    }



    /**
     * Checks if contents are available for the given topics
     *
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function hasContents (): bool
    {

        if ($pages = $this->getPages()) {
            return (bool) $this->contentRepository->countByPagesAndLanguage(
                $pages,
                ($this->issue->getNewsletter()? $this->issue->getNewsletter()->getSysLanguageUid(): 0),
                true
            );
        }

        return false;
    }


    /**
     * Counts the topics with contents
     *
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countTopicsWithContents (): int
    {

        $cnt = 0;
        if ($pages = $this->getPages()) {
            foreach ($pages as $page) {

                // get contents for topic
                $contentCount = $this->contentRepository->countByPageAndLanguage(
                    $page,
                    ($this->issue->getNewsletter() ? $this->issue->getNewsletter()->getSysLanguageUid() : 0),
                    false
                );

                if ($contentCount) {
                    $cnt++;
                }
            }
        }

        return $cnt;
    }

    /**
     * Get all contents as a zip-merged array by topics
     *
     * @param int limit
     * @return array
     */
    public function getContents (int $limit = 0): array
    {

        // load all contents of relevant pages
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $contents = [];
        foreach ($this->getPages() as $page) {

            // get contents for topic
            $contentsOfTopic = $this->contentRepository->findByPageAndLanguage(
                $page,
                ($this->issue->getNewsletter()? $this->issue->getNewsletter()->getSysLanguageUid(): 0),
                $limit,
                false
            )->toArray();

            // set contents to key according to desired order - this is already given via getPages()
            if ($contentsOfTopic) {
                $contents[] = $contentsOfTopic;
            }

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Loaded %s contents for topic with id=%s of issue with id=%s.',
                    count($contentsOfTopic),
                    $page->getTxRkwnewsletterTopic()->getUid(),
                    $this->issue->getUid()
                )
            );
        }

        // now mix topics together - pass array-items as separate parameters to arrayZipMerge
        $result = call_user_func_array(
            '\RKW\RkwBasics\Utility\GeneralUtility::arrayZipMerge',
            $contents
        );

        if (is_array($result)) {
            return $result;
        }

        return [];
    }


    /**
     * Get first headline
     *
     * @return string
     */
    public function getFirstHeadline (): string
    {

        // get first page in order
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        if (
            ($pages = $this->getPages())
            && ($page = $pages[0])
        ){

            /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
            $content = $this->contentRepository->findByPageAndLanguage(
                $page,
                ($this->issue->getNewsletter()? $this->issue->getNewsletter()->getSysLanguageUid() : 0),
                1,
                false
            )->getFirst();


            if ($content) {

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'Loaded first headline for topic with id=%s of issue with id=%s.',
                        $page->getTxRkwnewsletterTopic()->getUid(),
                        $this->issue->getUid()
                    )
                );

                return $content->getHeader();
            }

            return '';
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'No first headline found for issue with id=%s.',
                $this->issue->getUid()
            )
        );

        return '';
    }


    /**
     * Get topic of content
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Content $content
     * @return \RKW\RkwNewsletter\Domain\Model\Topic|null
     */
    public function getTopicOfContent (Content $content)
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByUid($content->getPid());
        if ($topic = $page->getTxRkwnewsletterTopic()) {
            return $topic;
        }

        return null;
    }


    /**
     * Get editorial if content contains an editorial
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Content|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getEditorial()
    {

        // always empty if we have more than one topic with contents
        if ($this->countTopicsWithContents() > 1) {
            return null;
        }

        // get first page in order
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        if (
            ($pages = $this->getPages())
            && ($page = $pages[0])
        ){

            return $this->contentRepository->findOneEditorialByPageAndLanguage(
                $page,
                ($this->issue->getNewsletter()? $this->issue->getNewsletter()->getSysLanguageUid(): 0)
            );
        }

        return null;
    }


    /**
     * Get the relevant pages based on set topics
     *
     * @return array
     */
    public function getPages(): array
    {

        $pages = [];
        foreach ($this->issue->getPages() as $page) {

            // get topic of page
            if ($topic = $page->getTxRkwnewsletterTopic()) {

                // check if topic is allowed
                if (!isset($this->ordering[$topic->getUid()])) {
                    continue;
                }

                // add page in order of given topics
                $pages[$this->ordering[$topic->getUid()]] = $page;
            }
        }

        ksort($pages);
        return $pages;
    }


    /**
     * Updates the ordering
     *
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    protected function updateOrdering (): void
    {

        // reset
        $this->ordering = [];
        $newTopics = new ObjectStorage();

        // Always include special topics and set them at the beginning of the array
        if ($this->issue->getNewsletter()) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
            foreach ($this->issue->getNewsletter()->getTopic() as $topic) {
                if (
                    ($topic->getIsSpecial())
                    && (!$this->topics->contains($topic))
                ) {
                    $newTopics->attach($topic);

                    $this->getLogger()->log(
                        LogLevel::DEBUG,
                        sprintf(
                            'Added special topic with id=%s to ordering of issue with id=%s.',
                            $topic->getUid(),
                            $this->issue->getUid()
                        )
                    );
                }
            }
        }

        // combine old with new, because we can't do an array_unshift to add topic at position 0
        if (count($newTopics->toArray())) {
            foreach ($this->topics as $topic) {

                if (! $topic instanceof Topic) {
                    throw new Exception(
                        'Only instances of \RKW\RkwNewsletter\Domain\Model\Topic are allowed here.',
                        1649840507
                    );
                }

                $newTopics->attach($topic);
            }

            $this->topics = $newTopics;
        }

        // get ordering in separate array based on topic-order
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        $cnt = 0;
        foreach ($this->topics as $topic) {

            if (! $topic instanceof Topic) {
                throw new Exception(
                    'Only instances of \RKW\RkwNewsletter\Domain\Model\Topic are allowed here.',
                    1649840507
                );
            }

            $this->ordering[$topic->getUid()] = $cnt;
            $cnt++;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Updated ordering of issue with id=%s to: %s.',
                $this->issue->getUid(),
                str_replace("\n", '', print_r($this->ordering, true))
            )
        );
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
