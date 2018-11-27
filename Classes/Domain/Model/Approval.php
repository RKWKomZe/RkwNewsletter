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
 * @copyright Rkw Kompetenzzentrum
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
     * @var integer
     */
    protected $allowedTstampStage1 = 0;

    /**
     * allowedTstampStage2
     *
     * @var integer
     */
    protected $allowedTstampStage2;

    /**
     * sentInfoTstampStage1
     *
     * @var integer
     */
    protected $sentInfoTstampStage1;

    /**
     * sentInfoTstampStage2
     *
     * @var integer
     */
    protected $sentInfoTstampStage2;

    /**
     * sentReminderTstampStage1
     *
     * @var integer
     */
    protected $sentReminderTstampStage1;

    /**
     * sentReminderTstampStage2
     *
     * @var integer
     */
    protected $sentReminderTstampStage2;


    /**
     * Returns the topic
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Topic
     * @api
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
     * @api
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }


    /**
     * Returns the issue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue $issue
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
    public function setIssue(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
    {
        $this->issue = $issue;
    }


    /**
     * Returns the pages
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages
     * @api
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
     * @api
     */
    public function setPage($pages)
    {
        $this->page = $pages;
    }

    /**
     * Sets a user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function setAllowedByUserStage1(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
    {
        $this->allowedByUserStage1 = $backendUser;
    }


    /**
     * Returns the user.
     *
     * @return \RKW\RkwNewsletter\Domain\Model\BackendUser
     * @api
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
     * @api
     */
    public function setAllowedByUserStage2(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
    {
        $this->allowedByUserStage2 = $backendUser;
    }


    /**
     * Returns the user.
     *
     * @return \RKW\RkwNewsletter\Domain\Model\BackendUser
     * @api
     */
    public function getAllowedByUserStage2()
    {
        return $this->allowedByUserStage2;
    }


    /**
     * Returns the allowedTstampStage1
     *
     * @return integer $sent
     */
    public function getAllowedTstampStage1()
    {
        return $this->allowedTstampStage1;
    }

    /**
     * Sets the allowedTstampStage1
     *
     * @param integer $sent
     * @return void
     */
    public function setAllowedTstampStage1($allowedAt)
    {
        $this->allowedTstampStage1 = $allowedAt;
    }

    /**
     * Returns the allowedTstampStage2
     *
     * @return integer $sent
     */
    public function getAllowedTstampStage2()
    {
        return $this->allowedTstampStage2;
    }

    /**
     * Sets the allowedTstampStage2
     *
     * @param integer $sent
     * @return void
     */
    public function setAllowedTstampStage2($allowedAt)
    {
        $this->allowedTstampStage2 = $allowedAt;
    }

    /**
     * Returns the sentInfoTstampStage1
     *
     * @return integer $sendInfomail
     */
    public function getSentInfoTstampStage1()
    {
        return $this->sentInfoTstampStage1;
    }

    /**
     * Sets the sentInfoTstampStage1
     *
     * @param integer $sendInfomail
     * @return void
     */
    public function setSentInfoTstampStage1($sendInfomail)
    {
        $this->sentInfoTstampStage1 = $sendInfomail;
    }

    /**
     * Returns the sentInfoTstampStage2
     *
     * @return integer $sendInfomai2
     */
    public function getSentInfoTstampStage2()
    {
        return $this->sentInfoTstampStage2;
    }

    /**
     * Sets the sentInfoTstampStage2
     *
     * @param integer $sendInfomail
     * @return void
     */
    public function setSentInfoTstampStage2($sendRemindermail)
    {
        $this->sentInfoTstampStage2 = $sendRemindermail;
    }

    /**
     * Returns the sentReminderTstampStage1
     *
     * @return integer $sendRemindermail
     */
    public function getSentReminderTstampStage1()
    {
        return $this->sentReminderTstampStage1;
    }

    /**
     * Sets the sentReminderTstampStage1
     *
     * @param integer $sendRemindermail
     * @return void
     */
    public function setSentReminderTstampStage1($sendRemindermail)
    {
        $this->sentReminderTstampStage1 = $sendRemindermail;
    }

    /**
     * Returns the sendReminderomailStage2
     *
     * @return integer $sendRemindermai2
     */
    public function getSentReminderTstampStage2()
    {
        return $this->sentReminderTstampStage2;
    }

    /**
     * Sets the sentReminderTstampStage2
     *
     * @param integer $sendRemindermail
     * @return void
     */
    public function setSentReminderTstampStage2($sendRemindermail)
    {
        $this->sentReminderTstampStage2 = $sendRemindermail;
    }


}