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
     * Holds the subscriptions
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic>
     */
    protected $txRkwnewsletterSubscription;


    /**
     * @var string txRkwnewsletterHash
     */
    protected $txRkwnewsletterHash;

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

    protected function initStorageObjects()
    {
        $this->txRkwnewsletterSubscription = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Sets the Subscriptions. Keep in mind that the property is called "Subscription"
     * although it can hold several topics.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscription
     * @return void
     */

    public function setTxRkwnewsletterSubscription(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscription)
    {
        $this->txRkwnewsletterSubscription = $subscription;
    }

    /**
     * Adds a $subscription to the frontend user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $subscription
     * @return void
     */

    public function addTxRkwnewsletterSubscription(\RKW\RkwNewsletter\Domain\Model\Topic $subscription)
    {
        $this->txRkwnewsletterSubscription->attach($subscription);
    }

    /**
     * Removes a Subscription from the frontend user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $subscription
     * @return void
     */
    public function removeTxRkwnewsletterSubscription(\RKW\RkwNewsletter\Domain\Model\Topic $subscription)
    {
        $this->txRkwnewsletterSubscription->detach($subscription);
    }

    /**
     * Returns the topics. Keep in mind that the property is called "$newsletter"
     * although it can hold several $newsletter.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the $newsletter
     */
    public function getTxRkwnewsletterSubscription()
    {
        return $this->txRkwnewsletterSubscription;
    }


    /**
     * set the TxRkwnewsletterHash
     *
     * @param string $hash
     * @return void
     */

    public function setTxRkwnewsletterHash($hash)
    {
        $this->txRkwnewsletterHash = $hash;
    }

    /**
     * get the TxRkwnewsletterHash
     *
     * @return string
     */
    public function getTxRkwnewsletterHash()
    {
        return $this->txRkwnewsletterHash;
    }


}
