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

use RKW\RkwBasics\Domain\Model\FileReference;

/**
 * Pages
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Pages extends \RKW\RkwAuthors\Domain\Model\Pages
{
    /**
     * permsUserId
     *
     * @var integer
     */
    protected $permsUserId = 0;

    
    /**
     * permsGroupId
     *
     * @var integer
     */
    protected $permsGroupId = 0;

    
    /**
     * permsUser
     *
     * @var integer
     */
    protected $permsUser = 0;

    
    /**
     * permsGroup
     *
     * @var integer
     */
    protected $permsGroup = 0;

    
    /**
     * permsEverybody
     *
     * @var integer
     */
    protected $permsEverybody = 0;

   
    /**
     * txRkwnewsletterNewsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $txRkwnewsletterNewsletter;

    /**
     * txRkwnewsletterTopic
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Topic
     */
    protected $txRkwnewsletterTopic;


    /**
     * txRkwnewsletterIssue
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    protected $txRkwnewsletterIssue;


    /**
     * txRkwnewsletterExclude
     *
     * @var bool
     */
    protected $txRkwnewsletterExclude = false;

    
    /**
     * txRkwnewsletterTeaserHeading
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserHeading = '';

    
    /**
     * txRkwnewsletterTeaserText
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserText = '';

    
    /**
     * txRkwnewsletterTeaserImage
     *
     * @var \RKW\RkwBasics\Domain\Model\FileReference
     */
    protected $txRkwnewsletterTeaserImage;

    
    /**
     * txRkwnewsletterTeaserLink
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserLink = '';

    
    /**
     * txRkwnewsletterIncludeTstamp
     *
     * @var int
     */
    protected $txRkwnewsletterIncludeTstamp = 0;

    
    /**
     * Returns the permsUserId
     *
     * @return int $permsUserId
     */
    public function getPermsUserId(): int
    {
        return $this->permsUserId;
    }

    
    /**
     * Sets the permsUserId
     *
     * @param int $permsUserId
     * @return void
     */
    public function setPermsUserId(int $permsUserId): void
    {
        $this->permsUserId = $permsUserId;
    }

    
    /**
     * Returns the permsGroupId
     *
     * @return int $permsGroupId
     */
    public function getPermsGroupId(): int
    {
        return $this->permsGroupId;
    }

    
    /**
     * Sets the permsGroupId
     *
     * @param int $permsGroupId
     * @return void
     */
    public function setPermsGroupId(int $permsGroupId) :void
    {
        $this->permsGroupId = $permsGroupId;
    }

    
    /**
     * Returns the permsUser
     *
     * @return int $permsUser
     */
    public function getPermsUser(): int
    {
        return $this->permsUser;
    }

    
    /**
     * Sets the permsUser
     *
     * @param int $permsUser
     * @return void
     */
    public function setPermsUser(int $permsUser): void
    {
        $this->permsUser = $permsUser;
    }

    
    /**
     * Returns the permsGroup
     *
     * @return int $permsGroup
     */
    public function getPermsGroup(): int
    {
        return $this->permsGroup;
    }

    
    /**
     * Sets the permsGroup
     *
     * @param integer $permsGroup
     * @return void
     */
    public function setPermsGroup(int $permsGroup): void
    {
        $this->permsGroup = $permsGroup;
    }

    
    /**
     * Returns the permsEverybody
     *
     * @return int $permsEverybody
     */
    public function getPermsEverybody(): int
    {
        return $this->permsEverybody;
    }

    
    /**
     * Sets the permsEverybody
     *
     * @param int $permsEverybody
     * @return void
     */
    public function setPermsEverybody(int $permsEverybody): void
    {
        $this->permsEverybody = $permsEverybody;
    }
    
    
    /**
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter|null
     */
    public function getTxRkwnewsletterNewsletter()
    {
        return $this->txRkwnewsletterNewsletter;
    }

    
    /**
     * Sets the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $txRkwnewsletterNewsletter
     * @return void
     */
    public function setTxRkwnewsletterNewsletter(Newsletter $txRkwnewsletterNewsletter): void
    {
        $this->txRkwnewsletterNewsletter = $txRkwnewsletterNewsletter;
    }

    
    /**
     * Returns the topic
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Topic|null
     */
    public function getTxRkwnewsletterTopic()
    {
        return $this->txRkwnewsletterTopic;
    }

    
    /**
     * Sets the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $txRkwnewsletterTopic
     * @return void
     */
    public function setTxRkwnewsletterTopic(Topic $txRkwnewsletterTopic): void
    {
        $this->txRkwnewsletterTopic = $txRkwnewsletterTopic;
    }


    /**
     * Returns the txRkwnewsletterIssue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue|null $txRkwnewsletterIssue
     */
    public function getTxRkwnewsletterIssue()
    {
        return $this->txRkwnewsletterIssue;
    }
  

    /**
     * Returns the txRkwnewsletterExclude
     *
     * @return bool $txRkwnewsletterExclude
     */
    public function getTxRkwnewsletterExclude(): bool
    {
        return $this->txRkwnewsletterExclude;
    }

    
    /**
     * Sets the txRkwnewsletterExclude
     *
     * @param bool $txRkwnewsletterExclude
     * @return void
     */
    public function setTxRkwnewsletterExclude(bool $txRkwnewsletterExclude): void
    {
        $this->txRkwnewsletterExclude = $txRkwnewsletterExclude;
    }

    
    /**
     * Returns the txRkwnewsletterTeaserHeading
     *
     * @return string $txRkwnewsletterTeaserHeading
     */
    public function getTxRkwnewsletterTeaserHeading(): string
    {
        return $this->txRkwnewsletterTeaserHeading;
    }

    
    /**
     * Sets the txRkwnewsletterTeaserHeading
     *
     * @param string $txRkwnewsletterTeaserHeading
     * @return void
     */
    public function setTxRkwnewsletterTeaserHeading(string $txRkwnewsletterTeaserHeading): void
    {
        $this->txRkwnewsletterTeaserHeading = $txRkwnewsletterTeaserHeading;
    }

    
    /**
     * Returns the txRkwnewsletterTeaserText
     *
     * @return string $txRkwnewsletterTeaserText
     */
    public function getTxRkwnewsletterTeaserText(): string
    {
        return $this->txRkwnewsletterTeaserText;
    }

    /**
     * Sets the txRkwnewsletterTeaserText
     *
     * @param string $txRkwnewsletterTeaserText
     * @return void
     */
    public function setTxRkwnewsletterTeaserText(string $txRkwnewsletterTeaserText): void
    {
        $this->txRkwnewsletterTeaserText = $txRkwnewsletterTeaserText;
    }

    
    /**
     * Returns the image
     *
     * @return \RKW\RkwBasics\Domain\Model\FileReference|null $txRkwnewsletterTeaserImage
     */
    public function getTxRkwnewsletterTeaserImage()
    {
        return $this->txRkwnewsletterTeaserImage;
    }

    
    /**
     * Sets the image
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $txRkwnewsletterTeaserImage
     * @return void
     */
    public function setTxRkwnewsletterTeaserImage(FileReference $image): void
    {
        $this->txRkwnewsletterTeaserImage = $image;
    }

    
    /**
     * Returns the txRkwnewsletterTeaserLink
     *
     * @return string $txRkwnewsletterTeaserLink
     */
    public function getTxRkwnewsletterTeaserLink(): string
    {
        return $this->txRkwnewsletterTeaserLink;
    }

    
    /**
     * Sets the txRkwnewsletterTeaserLink
     *
     * @param string $txRkwnewsletterTeaserLink
     * @return void
     */
    public function setTxRkwnewsletterTeaserLink(string $txRkwnewsletterTeaserLink): void
    {
        $this->txRkwnewsletterTeaserLink = $txRkwnewsletterTeaserLink;
    }

    
    /**
     * Returns the txRkwnewsletterIncludeTstamp
     *
     * @return int $txRkwnewsletterIncludeTstamp
     */
    public function getTxRkwnewsletterIncludeTstamp(): int
    {
        return $this->txRkwnewsletterIncludeTstamp;
    }

    
    /**
     * Sets the txRkwnewsletterIncludeTstamp
     *
     * @param int $txRkwnewsletterIncludeTstamp
     * @return void
     */
    public function setTxRkwnewsletterIncludeTstamp(int $txRkwnewsletterIncludeTstamp): void
    {
        $this->txRkwnewsletterIncludeTstamp = $txRkwnewsletterIncludeTstamp;
    }
}