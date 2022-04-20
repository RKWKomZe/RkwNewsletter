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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Topic
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Topic extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * name
     *
     * @var string
     */
    protected $name = '';


    /**
     * shortDescription
     *
     * @var string
     */
    protected $shortDescription = '';


    /**
     * containerPage
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    protected $containerPage = null;

    /**
     * newsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $newsletter = null;

    /**
     * approvalStage1
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     */
    protected $approvalStage1 = null;

    /**
     * approvalStage2
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     */
    protected $approvalStage2 = null;

       /**
     * isSpecial
     *
     * @var bool
     */
    protected $isSpecial = false;

    /**
     * sorting
     *
     * @var int
     */
    protected $sorting = 0;

    
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
        $this->approvalStage1 = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approvalStage2 = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * Returns the shortDescription
     *
     * @return string 
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * Sets the shortDescription
     *
     * @param string $shortDescription
     * @return void
     */
    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * Returns the containerPage
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages $containerPage
     */
    public function getContainerPage()
    {
        return $this->containerPage;
    }

    /**
     * Sets the containerPage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $containerPage
     * @return void
     */
    public function setContainerPage(Pages $containerPage): void
    {
        $this->containerPage = $containerPage;
    }

    /**
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     */
    public function getNewsletter()
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
     * Adds a backend user to the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function addApprovalStage1(BackendUser $backendUser): void
    {
        $this->approvalStage1->attach($backendUser);
    }

    /**
     * Removes a backend user from the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function removeApprovalStage1(BackendUser $backendUser): void
    {
        $this->approvalStage1->detach($backendUser);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> An object storage containing the backend user
     * @api
     */
    public function getApprovalStage1(): ObjectStorage
    {
        return $this->approvalStage1;
    }

    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> $backendUser
     * @return void
     * @api
     */
    public function setApprovalStage1(ObjectStorage $backendUser): void
    {
        $this->approvalStage1 = $backendUser;
    }

    /**
     * Adds a backend user to the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function addApprovalStage2(BackendUser $backendUser): void
    {
        $this->approvalStage2->attach($backendUser);
    }

    /**
     * Removes a backend user from the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function removeApprovalStage2(BackendUser $backendUser): void
    {
        $this->approvalStage2->detach($backendUser);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> An object storage containing the backend user
     * @api
     */
    public function getApprovalStage2(): ObjectStorage
    {
        return $this->approvalStage2;
    }

    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> $backendUser
     * @return void
     * @api
     */
    public function setApprovalStage2(ObjectStorage $backendUser): void
    {
        $this->approvalStage2 = $backendUser;
    }

    /**
     * Returns the isSpecial
     *
     * @return bool $isSpecial
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

    /**
     * Returns the sorting
     *
     * @return int $sorting
     */
    public function getSorting(): int
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param int $sorting
     * @return void
     */
    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

}