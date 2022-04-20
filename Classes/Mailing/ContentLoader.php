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
     * @var array
     */
    protected $topics = [];


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
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
        $this->issue = $issue;
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach($this->issue->getPages() as $page) {
            
            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
            if ($topic = $page->getTxRkwnewsletterTopic()) {
                $this->addTopic($topic);
            }
        }
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
     * @return array<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     */
    public function getTopics (): array
    {
        return $this->topics;
    }
    
    
    /**
     * Sets the topics 
     *
     * @param array<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setTopics (array $topics): void 
    {
        $this->topics = $topics;
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
        $this->topics[] = $topic;
        $this->updateOrdering();
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
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $tempTopic */
        foreach ($this->topics as $key => $tempTopic) {
            if ($tempTopic->getUid() == $topic->getUid()) {
                unset($this->topics[$key]);
            }
        }
        $this->updateOrdering();
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
        foreach ($this->issue->getPages() as $page) {

            // get topic of page
            $topic = $page->getTxRkwnewsletterTopic();

            // check if topic is allowed
            if (! isset($this->ordering[$topic->getUid()])) {
                continue;
            }

            // get contents for topic 
            $contentsOfTopic = $this->contentRepository->findByPageAndLanguage(
                $page,
                $this->issue->getNewsletter()->getSysLanguageUid(),
                $limit,
                false
            )->toArray();
            
            // set contents to key according to desired order
            $contents[$this->ordering[$topic->getUid()]] = $contentsOfTopic;

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Loaded %s contents for topic with id=%s of issue with id=%s.',
                    count($contentsOfTopic),
                    $topic->getUid(),
                    $this->issue->getUid()
                )
            );
        }

        // sort by key for correct order
        ksort($contents);

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
        
        // get first topic in order
        $firstTopic = key($this->ordering);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach ($this->issue->getPages() as $page) {

            // get topic of page
            $topic = $page->getTxRkwnewsletterTopic();

            // check if topic is the one wanted
            if ($topic->getUid() !== $firstTopic) {
                continue;
            }

            /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
            $content = $this->contentRepository->findByPageAndLanguage(
                $page,
                $this->issue->getNewsletter()->getSysLanguageUid(),
                1,
                false
            )->getFirst();
            
            
            if ($content) {
                
                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'Loaded headline for topic with id=%s of issue with id=%s.',
                        $topic->getUid(),
                        $this->issue->getUid()
                    )
                );
                
                return $content->getHeader();
            }
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'No headline found for issue with id=%s.',
                $topic->getUid()
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
     */
    public function getEditorial()
    {
        
        // always empty if more than one topic is set
        if (count($this->ordering) > 1) {
            return null;
        }

        // get first topic in order
        $firstTopic = key($this->ordering);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach ($this->issue->getPages() as $page) {

            // get topic of page
            $topic = $page->getTxRkwnewsletterTopic();

            // check if topic is the one wanted
            if ($topic->getUid() !== $firstTopic) {
                continue;
            }

            return $this->contentRepository->findOneEditorialByPageAndLanguage(
                $page,
                $this->issue->getNewsletter()->getSysLanguageUid()
            );
        }

        return null;
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
        
        // Always include special topics and set them at the beginning of the array 
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($this->issue->getNewsletter()->getTopic() as $topic) {
            if (
                ($topic->getIsSpecial())
                && (!in_array($topic, $this->topics))
            ) {
                 array_unshift($this->topics, $topic);

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
        
        // get ordering in separate array based on topic id
        $cnt = 0;
        ksort($this->topics);

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
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