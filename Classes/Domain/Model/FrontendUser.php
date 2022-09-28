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
 * FrontendUser
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUser extends \RKW\RkwRegistration\Domain\Model\FrontendUser
{
    /**
     * @var string
     * @validate \SJBR\SrFreecap\Validation\Validator\CaptchaValidator
     */
    protected $captchaResponse;

    /**
     * Holds the subscriptions
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic>
     */
    protected $txRkwnewsletterSubscription;


    /**
     * @var string txRkwnewsletterHash
     */
    protected $txRkwnewsletterHash = '';


    /**
     * @var bool txRkwnewsletterPriority
     */
    protected $txRkwnewsletterPriority = false;


    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

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
        $this->txRkwnewsletterSubscription = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Sets the captchaResponse
     *
     * @param string $captchaResponse
     * @return void
     */
    public function setCaptchaResponse($captchaResponse) {
        $this->captchaResponse = $captchaResponse;
    }

    /**
     * Getter for captchaResponse
     *
     * @return string
     */
    public function getCaptchaResponse() {
        return $this->captchaResponse;
    }

    /**
     * Sets the Subscriptions. Keep in mind that the property is called "Subscription"
     * although it can hold several topics.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscription
     * @return void
     * @api
     */

    public function setTxRkwnewsletterSubscription(ObjectStorage $subscription): void
    {
        $this->txRkwnewsletterSubscription = $subscription;
    }

    /**
     * Adds a $subscription to the frontend user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $subscription
     * @return void
     * @api
     */

    public function addTxRkwnewsletterSubscription(Topic $subscription): void
    {
        $this->txRkwnewsletterSubscription->attach($subscription);
    }

    /**
     * Removes a Subscription from the frontend user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $subscription
     * @return void
     * @api
     */
    public function removeTxRkwnewsletterSubscription(Topic $subscription): void
    {
        $this->txRkwnewsletterSubscription->detach($subscription);
    }

    /**
     * Returns the topics. Keep in mind that the property is called "$newsletter"
     * although it can hold several $newsletter.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the $newsletter
     * @api
     */
    public function getTxRkwnewsletterSubscription(): ObjectStorage
    {
        return $this->txRkwnewsletterSubscription;
    }


    /**
     * set the TxRkwnewsletterHash
     *
     * @param string $hash
     * @return void
     */

    public function setTxRkwnewsletterHash(string $hash): void
    {
        $this->txRkwnewsletterHash = $hash;
    }

    /**
     * get the TxRkwnewsletterHash
     *
     * @return string
     */
    public function getTxRkwnewsletterHash(): string
    {
        return $this->txRkwnewsletterHash;
    }


    /**
     * set the TxRkwnewsletterPriority
     *
     * @param bool $priority
     * @return void
     */

    public function setTxRkwnewsletterPriority(bool $priority): void
    {
        $this->txRkwnewsletterPriority = $priority;
    }

    /**
     * get the TxRkwnewsletterPriority
     *
     * @return bool
     */
    public function getTxRkwnewsletterPriority(): bool
    {
        return $this->txRkwnewsletterPriority;
    }

}
