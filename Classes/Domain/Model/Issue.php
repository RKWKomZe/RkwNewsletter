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
use Madj2k\Postmaster\Domain\Model\QueueMail;
use RKW\RkwNewsletter\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Issue
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Issue extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected string $title = '';


    /**
     * @var int
     */
    protected int $status = 0;


    /**
     *
     * @var string
     */
    protected string $introduction = '';


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>|null
     */
    protected ?ObjectStorage $authors = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter|null
     */
    protected ?Newsletter $newsletter = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages>|null
     */
    protected ?ObjectStorage $pages = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval>|null
     */
    protected ?ObjectStorage $approvals = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var int
     */
    protected int $infoTstamp = 0;


    /**
     * @var int
     */
    protected int $reminderTstamp = 0;


    /**
     * @var int
     */
    protected int $releaseTstamp = 0;


    /**
     * @var int
     */
    protected int $startTstamp = 0;


    /**
     * @var int
     */
    protected int $sentTstamp = 0;


    /**
     * @var string
     */
    protected string $recipients = '';


    /**
     * @var bool
     */
    protected bool $isSpecial = false;


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
        $this->pages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approvals = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->authors = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns the status
     *
     * @return int $status
     */
    public function getStatus(): int
    {
        return $this->status;
    }


    /**
     * Sets the status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    /**
     * Returns the introduction
     *
     * @return string $introduction
     */
    public function getIntroduction(): string
    {
        return $this->introduction;
    }


    /**
     * Sets the introduction
     *
     * @param string $introduction
     * @return void
     */
    public function setIntroduction(string $introduction): void
    {
        $this->introduction = $introduction;
    }


    /**
     * Adds an Authors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $authors
     * @return void
     */
    public function addAuthors(Authors $authors): void
    {
        $this->authors->attach($authors);
    }


    /**
     * Removes an Authors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $authorsToRemove The Authors to be removed
     * @return void
     */
    public function removeAuthors(Authors $authorsToRemove): void
    {
        $this->authors->detach($authorsToRemove);
    }


    /**
     * Returns the authors
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $authors
     * @api
     */
    public function getAuthors(): ObjectStorage
    {
        return $this->authors;
    }


    /**
     * Sets the authors
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $authors
     * @return void
     * @api
     */
    public function setAuthors(ObjectStorage $authors): void
    {
        $this->authors = $authors;
    }


    /**
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter|null $newsletter
     */
    public function getNewsletter():? Newsletter
    {
        return $this->newsletter;
    }


    /**
     * Sets the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return void
     */
    public function setNewsletter(Newsletter $newsletter): void
    {
        $this->newsletter = $newsletter;
    }


    /**
     * Adds a page to the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $pages
     * @return void
     * @api
     */
    public function addPages(Pages $pages): void
    {
        $this->pages->attach($pages);
    }


    /**
     * Removes a page from the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $pages
     * @return void
     * @api
     */
    public function removePages(Pages $pages): void
    {
        $this->pages->detach($pages);
    }


    /**
     * Returns the pages. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages>
     * @api
     */
    public function getPages(): ObjectStorage
    {
        return $this->pages;
    }


    /**
     * Sets the pages. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages> $pages
     * @return void
     * @api
     */
    public function setPages(ObjectStorage $pages):void
    {
        $this->pages = $pages;
    }


    /**
     * Adds a approval to the release
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @api
     */
    public function addApprovals(Approval $approval)
    {
        $this->approvals->attach($approval);
    }


    /**
     * Removes a approval from the release
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @api
     */
    public function removeApprovals(Approval $approval): void
    {
        $this->approvals->detach($approval);
    }


    /**
     * Returns the approval. Keep in mind that the property is called "approvals"
     * although it can hold several approvals.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval>
     * @api
     */
    public function getApprovals(): ObjectStorage
    {
        return $this->approvals;
    }


    /**
     * Sets the approval. Keep in mind that the property is called "approvals"
     * although it can hold several approvals.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval> $approval
     * @return void
     * @api
     */
    public function setApprovals(ObjectStorage $approvals)
    {
        $this->approvals = $approvals;
    }


    /**
     * Returns the queueMail
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail|null $queueMail
     */
    public function getQueueMail():? QueueMail
    {
        return $this->queueMail;
    }


    /**
     * Sets the queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }


    /**
     * Returns the infoTstamp
     *
     * @return int
     */
    public function getInfoTstamp(): int
    {
        return $this->infoTstamp;
    }


    /**
     * Sets the infoTstamp
     *
     * @param int $infoTstamp
     * @return void
     */
    public function setInfoTstamp(int $infoTstamp): void
    {
        $this->infoTstamp = $infoTstamp;
    }


    /**
     * Returns the reminderTstamp
     *
     * @return int
     */
    public function getReminderTstamp(): int
    {
        return $this->reminderTstamp;
    }


    /**
     * Sets the reminderTstamp
     *
     * @param int $reminderTstamp
     * @return void
     */
    public function setReminderTstamp(int $reminderTstamp): void
    {
        $this->reminderTstamp = $reminderTstamp;
    }


    /**
     * Returns the releaseTstamp
     *
     * @return int
     */
    public function getReleaseTstamp(): int
    {
        return $this->releaseTstamp;
    }


    /**
     * Sets the releaseTstamp
     *
     * @param int releaseTstamp
     * @return void
     */
    public function setReleaseTstamp(int $releaseTstamp): void
    {
        $this->releaseTstamp = $releaseTstamp;
    }


    /**
     * Returns the startTstamp
     *
     * @return int
     */
    public function getStartTstamp(): int
    {
        return $this->startTstamp;
    }


    /**
     * Sets the start
     *
     * @param int $startTstamp
     * @return void
     */
    public function setStartTstamp(int $startTstamp): void
    {
        $this->startTstamp = $startTstamp;
    }


    /**
     * Returns the sentTstamp
     *
     * @return int
     */
    public function getSentTstamp(): int
    {
        return $this->sentTstamp;
    }


    /**
     * Sets the sent
     *
     * @param int $sentTstamp
     * @return void
     */
    public function setSentTstamp(int $sentTstamp): void
    {
        $this->sentTstamp = $sentTstamp;
    }


    /**
     * Adds a recipient to the release
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @api
     */
    public function addRecipient(FrontendUser $frontendUser): void
    {
        $recipients = $this->getRecipients();
        $recipients[] = $frontendUser->getUid();
        $this->setRecipients($recipients);
    }


    /**
     * Removes a recipient from the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @api
     */
    public function removeRecipient(FrontendUser $frontendUser): void
    {
        $recipients = $this->getRecipients();
        if (false !== $key = array_search($frontendUser->getUid(), $recipients)) {
            array_splice($recipients, $key, 1);
            $this->setRecipients($recipients);
        }
    }


    /**
     * Removes a recipient by id from the issue
     *
     * @param int $frontendUserId
     * @return void
     * @api
     */
    public function removeRecipientById(int $frontendUserId): void
    {
        $recipients = $this->getRecipients();
        if (false !== $key = array_search($frontendUserId, $recipients)) {
            array_splice($recipients, $key, 1);
            $this->setRecipients($recipients);
        }
    }


    /**
     * Returns the recipient. Keep in mind that the property is called "recipients"
     * although it can hold several pages.
     *
     * @return array
     * @api
     */
    public function getRecipients(): array
    {
        return GeneralUtility::trimExplode(',', $this->recipients, true);
    }


    /**
     * Sets the recipient. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @param array $recipients
     * @return void
     * @api
     */
    public function setRecipients(array $recipients = []): void
    {
        $this->recipients = implode(',', $recipients);
    }


    /**
     * Returns the isSpecial
     *
     * @return bool
     */
    public function getIsSpecial(): bool
    {
        return $this->isSpecial;
    }


    /**
     * Sets the isSpecial
     *
     * @param bool $isSpecial
     * @return void
     */
    public function setIsSpecial(bool $isSpecial): void
    {
        $this->isSpecial = $isSpecial;
    }
}
