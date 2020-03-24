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
 * Newsletter
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Newsletter extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * SysLanguageUid
     *
     * @var integer
     */
    protected $sysLanguageUid = -1;

    /**
     * name
     *
     * @var string
     */
    protected $name;


    /**
     * introduction
     *
     * @var string
     */
    protected $introduction;

    /**
     * issueTitle
     *
     * @var string
     */
    protected $issueTitle;

    /**
     * senderName
     *
     * @var string
     */
    protected $senderName;

    /**
     * senderMail
     *
     * @var string
     */
    protected $senderMail;

    /**
     * replyName
     *
     * @var string
     */
    protected $replyName;

    /**
     * replyMail
     *
     * @var string
     */
    protected $replyMail;

    /**
     * returnPath
     *
     * @var string
     */
    protected $returnPath;

    /**
     * priority
     *
     * @var integer
     */
    protected $priority;

    
    /**
     * type
     *
     * @var integer
     */
    protected $type;

    /**
     * template
     *
     * @var string
     */
    protected $template;

    /**
     * settingsPage
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    protected $settingsPage;

    /**
     * format
     *
     * @var integer
     */
    protected $format;

    /**
     * rythm
     *
     * @var integer
     */
    protected $rythm;

    /**
     * approval
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     */
    protected $approval;

    /**
     * usergroup
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

    /**
     * topic
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic>
     */
    protected $topic;

    /**
     * issue
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Issue>
     */
    protected $issue;

    /**
     * lastSentTstamp
     *
     * @var integer
     */
    protected $lastSentTstamp;

    /**
     * lastIssueTstamp
     *
     * @var integer
     */
    protected $lastIssueTstamp;


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
    protected function initStorageObjects()
    {
        $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approval = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->topic = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->issue = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }

    /**
     * Returns the sysLanguageUid
     *
     * @return string $sysLanguageUid
     */
    public function getSysLanguageUid()
    {
        return $this->sysLanguageUid;
    }

    /**
     * Sets the sysLanguageUid
     *
     * @param string $sysLanguageUid
     * @return void
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Returns the introduction
     *
     * @return string $introduction
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }


    /**
     * Sets the introduction
     *
     * @param string $introduction
     * @return void
     */
    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
    }

    /**
     * Returns the issueTitle
     *
     * @return string $issueTitle
     */
    public function getIssueTitle()
    {
        return $this->issueTitle;
    }

    /**
     * Sets the issueTitle
     *
     * @param string $issueTitle
     * @return void
     */
    public function setIssueTitle($issueTitle)
    {
        $this->issueTitle = $issueTitle;
    }

    /**
     * Returns the senderName
     *
     * @return string $senderName
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Sets the senderName
     *
     * @param string $senderName
     * @return void
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * Returns the senderMail
     *
     * @return string $senderMail
     */
    public function getSenderMail()
    {
        return $this->senderMail;
    }

    /**
     * Sets the senderMail
     *
     * @param string $senderMail
     * @return void
     */
    public function setSenderMail($senderMail)
    {
        $this->senderMail = $senderMail;
    }

    /**
     * Returns the replyName
     *
     * @return string $replyName
     */
    public function getReplyName()
    {
        return $this->replyName;
    }

    /**
     * Sets the replyName
     *
     * @param string $replyName
     * @return void
    */
    public function setReplyName($replyName)
    {
        $this->replyName = $replyName;
    }

    /**
     * Returns the replyMail
     *
     * @return string $replyMail
     */
    public function getReplyMail()
    {
        return $this->replyMail;
    }

    /**
     * Sets the replyMail
     *
     * @param string $replyMail
     * @return void
     */
    public function setReplyMail($replyMail)
    {
        $this->replyMail = $replyMail;
    }

    /**
     * Returns the returnPath
     *
     * @return string $returnPath
     */
    public function getReturnPath()
    {
        return $this->returnPath;
    }

    /**
     * Sets the returnPath
     *
     * @param string $returnPath
     * @return void
     */
    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
    }

    /**
     * Returns the priority
     *
     * @return integer $priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Sets the priority
     *
     * @param integer $priority
     * @return void
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }



    /**
     * Returns the type
     *
     * @return integer $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param integer $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the template
     *
     * @return string $template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the template
     *
     * @param string $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the settingsPage
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages $settingsPage
     */
    public function getSettingsPage()
    {
        return $this->settingsPage;
    }

    /**
     * Sets the settingsPage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $settingsPage
     * @return void
     */
    public function setSettingsPage(\RKW\RkwNewsletter\Domain\Model\Pages $settingsPage)
    {
        $this->settingsPage = $settingsPage;
    }

    /**
     * Returns the format
     *
     * @return integer $format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the format
     *
     * @param integer $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }


    /**
     * Returns the rythm
     *
     * @return integer $rythm
     */
    public function getRythm()
    {
        return $this->rythm;
    }

    /**
     * Sets the rythm
     *
     * @param integer $rythm
     * @return void
     */
    public function setRythm($rythm)
    {
        $this->rythm = $rythm;
    }


    /**
     * Adds a backend user to the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function addApproval(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
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
    public function removeApproval(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
    {
        $this->approval->detach($backendUser);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     * @api
     */
    public function getApproval()
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
    public function setApproval(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $backendUser)
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
    public function addUsergroup(\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup)
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
    public function removeUsergroup(\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup)
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
    public function getUsergroup()
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
    public function setUsergroup(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup)
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
    public function addTopic(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
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
    public function removeTopic(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
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
    public function getTopic()
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
    public function setTopic(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $topic)
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
    public function addIssue(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
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
    public function removeIssue(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
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
    public function getIssue()
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
    public function setIssue(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $issue)
    {
        $this->issue = $issue;
    }


    /**
     * Returns the lastSentTstamp
     *
     * @return integer $lastSentTstamp
     */
    public function getLastSentTstamp()
    {
        return $this->lastSentTstamp;
    }

    /**
     * Sets the lastSentTstamp
     *
     * @param integer $lastSentTstamp
     * @return void
     */
    public function setLastSentTstamp($lastSentTstamp)
    {
        $this->lastSentTstamp = $lastSentTstamp;
    }

    /**
     * Returns the lastIssueTstamp
     *
     * @return integer $lastIssueTstamp
     */
    public function getLastIssueTstamp()
    {
        return $this->lastIssueTstamp;
    }

    /**
     * Sets the lastIssueTstamp
     *
     * @param integer $lastIssueTstamp
     * @return void
     */
    public function setLastIssueTstamp($lastIssueTstamp)
    {
        $this->lastIssueTstamp = $lastIssueTstamp;
    }


}