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

use RKW\RkwAuthors\Domain\Model\Authors;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Newsletter
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Newsletter extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var int
     */
    protected int $sysLanguageUid = -1;


    /**
     * @var string
     */
    protected string $name = '';


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>|null
     */
    protected ?ObjectStorage $authors = null;


    /**
     * @var string
     */
    protected string $issueTitle = '';


    /**
     * @var string
     */
    protected string $senderName = '';


    /**
     * @var string
     */
    protected string $senderMail = '';


    /**
     * @var string
     */
    protected string $replyName = '';


    /**
     * @var string
     */
    protected string $replyMail = '';


    /**
     * @var string
     */
    protected string $returnPath = '';


    /**
     * @var int
     */
    protected int $priority = 0;


    /**
     * @var string
     */
    protected string $template = '';


    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Pages|null
     */
    protected ?Pages $settingsPage = null;


    /**
     * @var int
     */
    protected int $format = 0;


    /**
     * @var int
     */
    protected int $rythm = 0;


    /**
     * @var int
     */
    protected int $dayForSending = 0;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>|null
     */
    protected ?ObjectStorage $approval = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>|null
     */
    protected ?ObjectStorage $usergroup = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic>|null
     */
    protected ?ObjectStorage $topic = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Issue>|null
     */
    protected ?ObjectStorage $issue = null;


    /**
     * @var int
     */
    protected int $lastSentTstamp = 0;


    /**
     * @var int
     */
    protected int $lastIssueTstamp = 0;


    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }


    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects(): void
    {
        $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approval = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->topic = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->issue = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Returns the sysLanguageUid
     *
     * @return int $sysLanguageUid
     */
    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }


    /**
     * Sets the sysLanguageUid
     *
     * @param int $sysLanguageUid
     * @return void
     */
    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }


    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * Returns the issueTitle
     *
     * @return string
     */
    public function getIssueTitle(): string
    {
        return $this->issueTitle;
    }


    /**
     * Sets the issueTitle
     *
     * @param string $issueTitle
     * @return void
     */
    public function setIssueTitle(string $issueTitle): void
    {
        $this->issueTitle = $issueTitle;
    }


    /**
     * Returns the senderName
     *
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }


    /**
     * Sets the senderName
     *
     * @param string $senderName
     * @return void
     */
    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }


    /**
     * Returns the senderMail
     *
     * @return string
     */
    public function getSenderMail(): string
    {
        return $this->senderMail;
    }


    /**
     * Sets the senderMail
     *
     * @param string $senderMail
     * @return void
     */
    public function setSenderMail(string $senderMail): void
    {
        $this->senderMail = $senderMail;
    }


    /**
     * Returns the replyName
     *
     * @return string
     */
    public function getReplyName(): string
    {
        return $this->replyName;
    }


    /**
     * Sets the replyName
     *
     * @param string $replyName
     * @return void
    */
    public function setReplyName(string $replyName): void
    {
        $this->replyName = $replyName;
    }


    /**
     * Returns the replyMail
     *
     * @return string
     */
    public function getReplyMail(): string
    {
        return $this->replyMail;
    }


    /**
     * Sets the replyMail
     *
     * @param string $replyMail
     * @return void
     */
    public function setReplyMail(string $replyMail): void
    {
        $this->replyMail = $replyMail;
    }


    /**
     * Returns the returnPath
     *
     * @return string
     */
    public function getReturnPath(): string
    {
        return $this->returnPath;
    }


    /**
     * Sets the returnPath
     *
     * @param string $returnPath
     * @return void
     */
    public function setReturnPath(string $returnPath): void
    {
        $this->returnPath = $returnPath;
    }


    /**
     * Returns the priority
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }


    /**
     * Sets the priority
     *
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }


    /**
     * Returns the template
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }


    /**
     * Sets the template
     *
     * @param string $template
     * @return void
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }


    /**
     * Returns the settingsPage
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages|null
     */
    public function getSettingsPage():? Pages
    {
        return $this->settingsPage;
    }


    /**
     * Sets the settingsPage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $settingsPage
     * @return void
     */
    public function setSettingsPage(Pages $settingsPage): void
    {
        $this->settingsPage = $settingsPage;
    }


    /**
     * Returns the format
     *
     * @return int
     */
    public function getFormat(): int
    {
        return $this->format;
    }


    /**
     * Sets the format
     *
     * @param int $format
     * @return void
     */
    public function setFormat(int $format): void
    {
        $this->format = $format;
    }


    /**
     * Returns the rythm
     *
     * @return int $rythm
     */
    public function getRythm(): int
    {
        return $this->rythm;
    }


    /**
     * Sets the rythm
     *
     * @param int $rythm
     * @return void
     */
    public function setRythm(int $rythm): void
    {
        $this->rythm = $rythm;
    }


    /**
     * Returns the dayForSending
     *
     * @return int $dayForSending
     */
    public function getDayForSending(): int
    {
        return $this->dayForSending;
    }


    /**
     * Sets the dayForSending
     *
     * @param int $dayForSending
     * @return void
     */
    public function setDayForSending(int $dayForSending): void
    {
        $this->dayForSending = $dayForSending;
    }


    /**
     * Adds a backend user to the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function addApproval(BackendUser $backendUser): void
    {
        $this->approval->attach($backendUser);
    }


    /**
     * Removes a backend user from the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function removeApproval(BackendUser $backendUser): void
    {
        $this->approval->detach($backendUser);
    }


    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     * @api
     */
    public function getApproval(): ObjectStorage
    {
        return $this->approval;
    }


    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> $backendUser
     * @return void
     * @api
     */
    public function setApproval(ObjectStorage $backendUser)
    {
        $this->approval = $backendUser;
    }


    /**
     * Adds a usergroup to the newsletter
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
     * @return void
     * @api
     */
    public function addUsergroup(FrontendUserGroup $usergroup): void
    {
        $this->usergroup->attach($usergroup);
    }


    /**
     * Removes a usergroup from the newsletter
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
     * @return void
     * @api
     */
    public function removeUsergroup(FrontendUserGroup $usergroup): void
    {
        $this->usergroup->detach($usergroup);
    }


    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the usergroup
     * @api
     */
    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }


    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup
     * @return void
     * @api
     */
    public function setUsergroup(ObjectStorage $usergroup): void
    {
        $this->usergroup = $usergroup;
    }


    /**
     * Adds a topic to the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return void
     * @api
     */
    public function addTopic(Topic $topic): void
    {
        $this->topic->attach($topic);
    }


    /**
     * Removes a usergroup from the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return void
     * @api
     */
    public function removeTopic(Topic $topic): void
    {
        $this->topic->detach($topic);
    }


    /**
     * Returns the topic. Keep in mind that the property is called "topic"
     * although it can hold several topic.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the topic
     * @api
     */
    public function getTopic(): ObjectStorage
    {
        return $this->topic;
    }


    /**
     * Sets the topic. Keep in mind that the property is called "topic"
     * although it can hold several topic.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $topic
     * @return void
     * @api
     */
    public function setTopic(ObjectStorage $topic): void
    {
        $this->topic = $topic;
    }


    /**
     * Adds a issue to the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return void
     * @api
     */
    public function addIssue(Issue $issue): void
    {
        $this->issue->attach($issue);
    }


    /**
     * Removes a usergroup from the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return void
     * @api
     */
    public function removeIssue(Issue $issue): void
    {
        $this->issue->detach($issue);
    }


    /**
     * Returns the issue. Keep in mind that the property is called "issue"
     * although it can hold several issue.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the issue
     * @api
     */
    public function getIssue(): ObjectStorage
    {
        return $this->issue;
    }


    /**
     * Sets the issue. Keep in mind that the property is called "issue"
     * although it can hold several issue.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $issue
     * @return void
     * @api
     */
    public function setIssue(ObjectStorage $issue): void
    {
        $this->issue = $issue;
    }


    /**
     * Returns the lastSentTstamp
     *
     * @return int
     */
    public function getLastSentTstamp(): int
    {
        return $this->lastSentTstamp;
    }


    /**
     * Sets the lastSentTstamp
     *
     * @param int $lastSentTstamp
     * @return void
     */
    public function setLastSentTstamp(int $lastSentTstamp): void
    {
        $this->lastSentTstamp = $lastSentTstamp;
    }


    /**
     * Returns the lastIssueTstamp
     *
     * @return int
     */
    public function getLastIssueTstamp(): int
    {
        return $this->lastIssueTstamp;
    }


    /**
     * Sets the lastIssueTstamp
     *
     * @param int $lastIssueTstamp
     * @return void
     */
    public function setLastIssueTstamp(int $lastIssueTstamp): void
    {
        $this->lastIssueTstamp = $lastIssueTstamp;
    }


}
