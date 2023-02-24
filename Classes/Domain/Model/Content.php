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
use Madj2k\CoreExtended\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Content
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Content extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{


    /**
     * @var int
     */
    protected int $crdate = 0;


    /**
     * @var int
     */
    protected int $sysLanguageUid = -1;


    /**
     * @var string
     */
    protected string $header = '';


    /**
     * @var string
     */
    protected string $headerLink = '';


    /**
     * @var string
     */
    protected string $bodytext = '';


    /**
     * @var string
     */
    protected string $contentType = 'textpic';


    /**
     * @var int
     */
    protected int $imageCols = 0;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\CoreExtended\Domain\Model\FileReference>|null
     */
    protected ?ObjectStorage $image = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>|null
     */
    protected ?ObjectStorage $txRkwnewsletterAuthors = null;


    /**
     * @var bool
     */
    protected bool $txRkwnewsletterIsEditorial = false;


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
        $this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txRkwnewsletterAuthors = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Returns the uid
     *
     * @return int $uid
     */
    public function getUid(): int
    {
        return $this->uid;
    }


    /**
     * Sets the uid
     *
     * @param int $uid
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }


    /**
     * Returns the crdate
     *
     * @return int $crdate
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }


    /**
     * Sets the crdate
     *
     * @param int $crdate
     * @return void
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
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
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader(): string
    {
        return $this->header;
    }


    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader(string $header): void
    {
        $this->header = $header;
    }


    /**
     * Returns the headerLink
     *
     * @return string $headerLink
     */
    public function getHeaderLink(): string
    {
        return $this->headerLink;
    }


    /**
     * Sets the headerLink
     *
     * @param string $headerLink
     * @return void
     */
    public function setHeaderLink(string $headerLink): void
    {
        $this->headerLink = $headerLink;
    }


    /**
     * Returns the bodytext
     *
     * @return string $bodytext
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }


    /**
     * Sets the bodytext
     *
     * @param string $bodytext
     * @return void
     */
    public function setBodytext(string $bodytext): void
    {
        $this->bodytext = $bodytext;
    }


    /**
     * Returns the contentType
     *
     * @return string $contentType
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }


    /**
     * Sets the contentType
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }


    /**
     * Returns the imageCols
     *
     * @return int $imageCols
     */
    public function getImageCols(): int
    {
        return $this->imageCols;
    }


    /**
     * Sets the imageCols
     *
     * @param int $imageCols
     * @return void
     */
    public function setImageCols(int $imageCols): void
    {
        $this->imageCols = $imageCols;
    }


    /**
     * Adds an image
     *
     * @param \Madj2k\CoreExtended\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function addImage(FileReference $image): void
    {
        $this->image->attach($image);
    }


    /**
     * Removes an image
     *
     * @param \Madj2k\CoreExtended\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function removeImage(FileReference $image): void
    {
        $this->image->detach($image);
    }


    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\CoreExtended\Domain\Model\FileReference>
     * @api
     */
    public function getImage(): ObjectStorage
    {
        return $this->image;
    }


    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\CoreExtended\Domain\Model\FileReference> $image
     * @return void
     * @api
     */
    public function setImage(ObjectStorage $image): void
    {
        $this->image = $image;
    }


    /**
     * Adds a txRkwnewsletterAuthors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $txRkwnewsletterAuthors
     * @return void
     * @api
     */
    public function addTxRkwnewsletterAuthors(Authors $txRkwnewsletterAuthors): void
    {
        $this->txRkwnewsletterAuthors->attach($txRkwnewsletterAuthors);
    }


    /**
     * Removes a txRkwnewsletterAuthors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $txRkwnewsletterAuthors The Authors to be removed
     * @return void
     * @api
     */
    public function removeTxRkwnewsletterAuthors(Authors $txRkwnewsletterAuthors): void
    {
        $this->txRkwnewsletterAuthors->detach($txRkwnewsletterAuthors);
    }


    /**
     * Returns the txRkwnewsletterAuthors
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $txRkwnewsletterAuthors
     * @api
     */
    public function getTxRkwnewsletterAuthors(): ObjectStorage
    {
        return $this->txRkwnewsletterAuthors;
    }


    /**
     * Sets the txRkwnewsletterAuthors
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $txRkwnewsletterAuthors
     * @return void
     * @api
     */
    public function setTxRkwnewsletterAuthors(ObjectStorage $txRkwnewsletterAuthors): void
    {
        $this->txRkwnewsletterAuthors = $txRkwnewsletterAuthors;
    }


    /**
     * Returns the txRkwnewsletterIsEditorial
     *
     * @return bool
     */
    public function getTxRkwnewsletterIsEditorial(): bool
    {
        return $this->txRkwnewsletterIsEditorial;
    }


    /**
     * Sets the txRkwnewsletterIsEditorial
     *
     * @param bool $txRkwnewsletterIsEditorial
     * @return void
     */
    public function setTxRkwnewsletterIsEditorial(bool $txRkwnewsletterIsEditorial): void
    {
        $this->txRkwnewsletterIsEditorial = $txRkwnewsletterIsEditorial;
    }


}
