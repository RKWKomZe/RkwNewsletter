<?php

namespace RKW\RkwNewsletter\Domain\Model;
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

/**
 * Approval
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Approval extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * topic
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Topic
     */
    protected $topic;

    /**
     * issue
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    protected $issue;

    /**
     * page
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    protected $page;

    /**
     * allowedByUserStage1
     *
     * @var \RKW\RkwNewsletter\Domain\Model\BackendUser
     */
    protected $allowedByUserStage1;

    /**
     * allowedByUserStage2
     *
     * @var \RKW\RkwNewsletter\Domain\Model\BackendUser
     */
    protected $allowedByUserStage2;

    /**
     * allowedTstampStage1
     *
     * @var int
     */
    protected $allowedTstampStage1 = 0;

    /**
     * allowedTstampStage2
     *
     * @var int
     */
    protected $allowedTstampStage2 = 0;

    /**
     * sentInfoTstampStage1
     *
     * @var int
     */
    protected $sentInfoTstampStage1 = 0;

    /**
     * sentInfoTstampStage2
     *
     * @var int
     */
    protected $sentInfoTstampStage2 = 0;

    /**
     * sentReminderTstampStage1
     *
     * @var int
     */
    protected $sentReminderTstampStage1 = 0;

    /**
     * sentReminderTstampStage2
     *
     * @var int
     */
    protected $sentReminderTstampStage2 = 0;



    /**
     * Returns the topic
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Topic|null
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Sets the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic
     * @return void
     */
    public function setTopic(Topic $topic): void
    {
        $this->topic = $topic;
    }


    /**
     * Returns the issue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue|null $issue
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Sets the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue issue
     * @return void
     */
    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }


    /**
     * Returns the pages
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the pages
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages
     * @return void
     */
    public function setPage(Pages $pages): void
    {
        $this->page = $pages;
    }


    /**
     * Returns the user.
     *
     * @return \RKW\RkwNewsletter\Domain\Model\BackendUser|null
     */
    public function getAllowedByUserStage1()
    {
        return $this->allowedByUserStage1;
    }


    /**
     * Sets a user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     */
    public function setAllowedByUserStage1(BackendUser $backendUser): void
    {
        $this->allowedByUserStage1 = $backendUser;
    }



    /**
     * Returns the user.
     *
     * @return \RKW\RkwNewsletter\Domain\Model\BackendUser|null
     */
    public function getAllowedByUserStage2()
    {
        return $this->allowedByUserStage2;
    }

    /**
     * Sets a user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     */
    public function setAllowedByUserStage2(BackendUser $backendUser): void
    {
        $this->allowedByUserStage2 = $backendUser;
    }



    /**
     * Returns the allowedTstampStage1
     *
     * @return int
     */
    public function getAllowedTstampStage1(): int
    {
        return $this->allowedTstampStage1;
    }

    /**
     * Sets the allowedTstampStage1
     *
     * @param int $timestamp
     * @return void
     */
    public function setAllowedTstampStage1(int $timestamp): void
    {
        $this->allowedTstampStage1 = $timestamp;
    }

    /**
     * Returns the allowedTstampStage2
     *
     * @return int
     */
    public function getAllowedTstampStage2(): int
    {
        return $this->allowedTstampStage2;
    }

    /**
     * Sets the allowedTstampStage2
     *
     * @param int $timestamp
     * @return void
     */
    public function setAllowedTstampStage2(int $timestamp): void
    {
        $this->allowedTstampStage2 = $timestamp;
    }

    /**
     * Returns the sentInfoTstampStage1
     *
     * @return int
     */
    public function getSentInfoTstampStage1(): int
    {
        return $this->sentInfoTstampStage1;
    }

    /**
     * Sets the sentInfoTstampStage1
     *
     * @param int $timestamp
     * @return void
     */
    public function setSentInfoTstampStage1(int $timestamp): void
    {
        $this->sentInfoTstampStage1 = $timestamp;
    }

    /**
     * Returns the sentInfoTstampStage2
     *
     * @return int
     */
    public function getSentInfoTstampStage2(): int
    {
        return $this->sentInfoTstampStage2;
    }

    /**
     * Sets the sentInfoTstampStage2
     *
     * @param int $timestamp
     * @return void
     */
    public function setSentInfoTstampStage2(int $timestamp): void
    {
        $this->sentInfoTstampStage2 = $timestamp;
    }

    /**
     * Returns the sentReminderTstampStage1
     *
     * @return int
     */
    public function getSentReminderTstampStage1(): int
    {
        return $this->sentReminderTstampStage1;
    }

    /**
     * Sets the sentReminderTstampStage1
     *
     * @param int $timestamp
     * @return void
     */
    public function setSentReminderTstampStage1(int $timestamp): void
    {
        $this->sentReminderTstampStage1 = $timestamp;
    }

    /**
     * Returns the sendReminderomailStage2
     *
     * @return int
     */
    public function getSentReminderTstampStage2(): int
    {
        return $this->sentReminderTstampStage2;
    }

    /**
     * Sets the sentReminderTstampStage2
     *
     * @param int $timestamp
     * @return void
     */
    public function setSentReminderTstampStage2(int $timestamp): void
    {
        $this->sentReminderTstampStage2 = $timestamp;
    }


}
