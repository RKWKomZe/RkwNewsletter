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
    protected $name;


    /**
     * shortDescription
     *
     * @var string
     */
    protected $shortDescription;


    /**
     * containerPage
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    protected $containerPage;

    /**
     * newsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $newsletter;

    /**
     * approvalStage1
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     */
    protected $approvalStage1;

    /**
     * approvalStage2
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser>
     */
    protected $approvalStage2;

    /**
     * primaryColor
     *
     * @var string
     */
    protected $primaryColor;

    /**
     * primaryColorEditorial
     *
     * @var string
     */
    protected $primaryColorEditorial;

    /**
     * secondaryColor
     *
     * @var string
     */
    protected $secondaryColor;

    /**
     * secondaryColorEditorial
     *
     * @var string
     */
    protected $secondaryColorEditorial;

    /**
     * isSpecial
     *
     * @var bool
     */
    protected $isSpecial;


    /**
     * sorting
     *
     * @var int
     */
    protected $sorting;

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
        $this->approvalStage1 = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approvalStage2 = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * Returns the shortDescription
     *
     * @return string $shortDescription
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Sets the shortDescription
     *
     * @param string $shortDescription
     * @return void
     */
    public function setShortDescription($shortDescription)
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
    public function setContainerPage(\RKW\RkwNewsletter\Domain\Model\Pages $containerPage)
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
    public function setNewsletter(\RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
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
    public function addApprovalStage1(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
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
    public function removeApprovalStage1(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
    {
        $this->approvalStage1->detach($backendUser);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> An object storage
     *     containing the backend user
     * @api
     */
    public function getApprovalStage1()
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
    public function setApprovalStage1(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $backendUser)
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
    public function addApprovalStage2(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
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
    public function removeApprovalStage2(\RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser)
    {
        $this->approvalStage2->detach($backendUser);
    }

    /**bool
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\BackendUser> An object storage
     *     containing the backend user
     * @api
     */
    public function getApprovalStage2()
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
    public function setApprovalStage2(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $backendUser)
    {
        $this->approvalStage2 = $backendUser;
    }

    /**
     * Returns the primaryColor
     *
     * @return string $primaryColor
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * Sets the primaryColor
     *
     * @param string $primaryColor
     * @return void
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;
    }

    /**
     * Returns the primaryColorEditorial
     *
     * @return string $primaryColorEditorial
     */
    public function getPrimaryColorEditorial()
    {
        return $this->primaryColorEditorial;
    }

    /**
     * Sets the primaryColorEditorial
     *
     * @param string $primaryColorEditorial
     * @return void
     */
    public function setPrimaryColorEditorial($primaryColorEditorial)
    {
        $this->primaryColorEditorial = $primaryColorEditorial;
    }

    /**
     * Returns the secondaryColor
     *
     * @return string $secondaryColor
     */
    public function getSecondaryColor()
    {
        return $this->secondaryColor;
    }

    /**
     * Sets the secondaryColor
     *
     * @param string $secondaryColor
     * @return void
     */
    public function setSecondaryColor($secondaryColor)
    {
        $this->secondaryColor = $secondaryColor;
    }

    /**
     * Returns the secondaryColorEditorial
     *
     * @return string $secondaryColorEditorial
     */
    public function getSecondaryColorEditorial()
    {
        return $this->secondaryColorEditorial;
    }

    /**
     * Sets the secondaryColorEditorial
     *
     * @param string $secondaryColorEditorial
     * @return void
     */
    public function setSecondaryColorEditorial($secondaryColorEditorial)
    {
        $this->secondaryColorEditorial = $secondaryColorEditorial;
    }


    /**
     * Returns the isSpecial
     *
     * @return bool $isSpecial
     */
    public function getIsSpecial()
    {
        return $this->isSpecial;
    }

    /**
     * Sets the isSpecial
     *
     * @param bool $isSpecial
     * @return void
     */
    public function setIsSpecial($isSpecial)
    {
        $this->isSpecial = $isSpecial;
    }

    /**
     * Returns the sorting
     *
     * @return bool $sorting
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param bool $sorting
     * @return void
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

}