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

use RKW\RkwMailer\Domain\Model\QueueMail;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Issue
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Issue extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    
    /**
     * status
     *
     * @var int
     */
    protected $status = 0;

    
    /**
     * newsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $newsletter = null;

    
    /**
     * pages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages>
     */
    protected $pages = null;

    
    /**
     * approvals
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval>
     * @cascade remove
     */
    protected $approvals = null;


    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail = null;

    
    /**
     * recipients
     *
     * @var string
     */
    protected $recipients = '';


    /**
     * infoTstamp
     *
     * @var int
     */
    protected $infoTstamp = 0;


    /**
     * reminderTstamp
     *
     * @var int
     */
    protected $reminderTstamp = 0;


    /**
     * releaseTstamp
     *
     * @var int
     */
    protected $releaseTstamp = 0;

    
    /**
     * sentTstamp
     *
     * @var int
     */
    protected $sentTstamp = 0;


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
        $this->pages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approvals = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

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
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     */
    public function getNewsletter(): Newsletter
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
     * @return \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     */
    public function getQueueMail(): QueueMail
    {
        return $this->queueMail;
    }

    
    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }



    /**
     * Adds a recipient to the release
     *
     * @param int|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $recipientId
     * @return void
     * @throws \Exception
     * @api
     */
    public function addRecipients($recipient): void
    {
        if ($recipient instanceOf \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
            $recipientId = $recipient->getUid();
        } else if(is_int($recipient)) {
            $recipientId = $recipient;
        } else {
            throw new \Exception('Wrong type given', 1641968790);
        }

        $recipients = $this->getRecipients();
        $recipients[] = $recipientId;
        $this->setRecipients($recipients);
    }

    
    /**
     * Removes a recipient from the release
     *
     * @param int|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $recipient
     * @return void
     * @throws \Exception
     * @api
     */
    public function removeRecipients($recipient): void
    {
        if ($recipient instanceOf \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
            $recipientId = $recipient->getUid();
        } else if(is_int($recipient)) {
            $recipientId = $recipient;
        } else {
            throw new \Exception('Wrong type given', 1641968790);
        }

        $recipients = $this->getRecipients();
        if (false !== $key = array_search($recipientId, $recipients)) {
            array_splice($recipients, $key, 1);
            $this->setRecipients($recipients);
        }
    }

    /**
     * @return array
     * @api
     */
    public function getRecipients(): array
    {
        return GeneralUtility::trimExplode(',', $this->recipients, true);
    }


    /**
     * @param array $recipients
     * @return void
     * @api
     */
    public function setRecipients(array $recipients = []): void
    {
        $this->recipients = implode(',', $recipients);
    }
    
    
    /**
     * Returns the infoTstamp
     *
     * @return int $infoTstamp
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
     * @return int $reminderTstamp
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
     * @return int $sent
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
     * Returns the sentTstamp
     *
     * @return int $sentTstamp
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

}