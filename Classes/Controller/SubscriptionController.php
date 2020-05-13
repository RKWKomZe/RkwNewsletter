<?php

namespace RKW\RkwNewsletter\Controller;
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
 * SubscriptionController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubscriptionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * newsletterRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;

    /**
     * topicRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository;

    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;


    /**
     * FrontendUser
     *
     * @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     */
    protected $frontendUser;

    /**
     * FrontendUser via hash, not logged in
     *
     * @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     */
    protected $frontendUserByHash;


    /**
     * initializeAction
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function initializeAction()
    {

        parent::initializeAction();

        // identify user by given hash value
        if ($this->request->hasArgument('hash')) {

            $this->frontendUserByHash = $this->frontendUserRepository->findOneByTxRkwnewsletterHash($this->request->getArgument('hash'));

            // check if user could be identified
            if (!$this->getFrontendUserByHash()) {
                $this->controllerContext = $this->buildControllerContext();
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.error.notIdentified',
                        'rkw_newsletter'
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );

                $this->redirect('new');
            }


            // check if user is already logged in with another id!
            if (
                ($this->getFrontendUserByHash())
                && ($this->getFrontendUserId())
                && ($this->getFrontendUserId() != $this->getFrontendUserByHash()->getUid())
            ) {

                $this->frontendUserByHash = null;
                $this->controllerContext = $this->buildControllerContext();
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.error.alreadyLoggedIn',
                        'rkw_newsletter'
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );

                $this->redirect('message');
            }
        }
    }


    /**
     * Id of logged User
     *
     * @return integer
     */
    protected function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
            //===
        }

        return 0;
        //===
    }


    /**
     * Returns current logged in user object
     *
     * @return \RKW\RkwNewsletter\Domain\Model\FrontendUser|null
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {
            $this->frontendUser = $this->frontendUserRepository->findByIdentifier($this->getFrontendUserId());
        }

        if ($this->frontendUser instanceof \RKW\RkwNewsletter\Domain\Model\FrontendUser) {
            return $this->frontendUser;
            //===
        }


        return null;
        //===
    }


    /**
     * Returns user object identified by hash
     *
     * @return \RKW\RkwNewsletter\Domain\Model\FrontendUser|null
     */
    protected function getFrontendUserByHash()
    {
        return $this->frontendUserByHash;
        //===
    }


    /**
     * action new
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @param array $topics
     * @param integer $terms
     * @param integer $privacy
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function newAction(\RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser = null, $topics = array(), $terms = null, $privacy = null)
    {

        // FE-User may be logged in
        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        if (!$frontendUser) {
            $frontendUser = ($this->getFrontendUser() ? $this->getFrontendUser() : $this->getFrontendUserByHash());
        }

        // check if frontendUser has an existing subscription and redirect to edit
        if ($frontendUser) {
            if (count($frontendUser->getTxRkwnewsletterSubscription())) {
                $this->forward('edit');
                //===
            }
        }

        $this->view->assignMultiple(
            array(
                'newsletterList' => $this->newsletterRepository->findAllByType(),
                'topicList'      => $this->buildCleanedTopicList($topics),
                'frontendUser'   => $frontendUser,
                'terms'          => (bool)$terms,
                'privacy'        => (bool)$privacy,
            )
        );
    }


    /**
     * action create
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @param array $topics
     * @param integer $terms
     * @param integer $privacy
     * @validate $frontendUser \RKW\RkwNewsletter\Validation\FormValidator
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @return void
     */
    public function createAction(\RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser, $topics = array(), $terms = null, $privacy = null)
    {

        // check if terms are checked
        if (
            ($frontendUser->_isNew())
            && (!$terms)
        ) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.acceptTerms',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }

        if (!$privacy) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_privacy',
                    'rkw_registration'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions */
        $subscriptions = $this->buildCleanedTopicList($topics);

        // If no topics are selected this can't be the indention :)
        if (count($subscriptions) < 1) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.noTopicSelected',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }


        // Case 1: FE-User is not logged in and is not identified via hash-tag
        // Case 2: FE-User is not logged in, but has been identified by hash-tag
        if (
            ($frontendUser->_isNew())
            || (
                (!$frontendUser->_isNew())
                && (!$this->getFrontendUser())
            )
        ) {

            // register new user or simply send opt-in to existing user
            /** @var \RKW\RkwRegistration\Tools\Registration $registration */
            $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
            $registration->register(
                $frontendUser,
                false,
                [
                    'subscriptions' => $subscriptions,
                    'frontendUser' => $frontendUser
                ],
                'rkwNewsletter',
                $this->request
            );

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.optInCreated',
                    'rkw_newsletter'
                )
            );

            $this->redirect('message');
            //===

        // Case 3: Fe-User is logged in
        } else {
            if (
                ($this->getFrontendUser())
                && ($frontendUser->getUid() == $this->getFrontendUser()->getUid())
            ) {

                // set FeUser and save
                $frontendUser->setTxRkwnewsletterSubscription($subscriptions);
                if (! $frontendUser->getTxRkwnewsletterHash()) {
                    $hash = sha1($frontendUser->getUid() . $frontendUser->getEmail() . rand());
                    $frontendUser->setTxRkwnewsletterHash($hash);
                }
                $this->frontendUserRepository->update($frontendUser);

                \RKW\RkwRegistration\Tools\Privacy::addPrivacyData($this->request, $this->getFrontendUser(), $subscriptions, 'new newsletter subscription');

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.message.subscriptionSaved',
                        'rkw_newsletter'
                    )
                );

                $this->redirect('edit');
                //===


                // Case 3: something is strange
            } else {

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.error.unexpectedError',
                        'rkw_newsletter'
                    )
                );
                $this->forward('new');
                //===
            }
        }

        $this->redirect('new');
        //===

    }

    /**
     * action edit
     *
     * @param array $topics
     * @param integer $privacy
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function editAction($topics = array(), $privacy = null)
    {

        // FE-User has to be logged in or identified by hash
        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = ($this->getFrontendUser() ? $this->getFrontendUser() : $this->getFrontendUserByHash());

        if (!$frontendUser) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.notIdentified',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new');
            //===
        }


        $this->view->assignMultiple(
            array(
                'newsletterList' => $this->newsletterRepository->findAllByType(),
                'topicList'      => $this->buildCleanedTopicList($topics),
                'privacy'        => (bool)$privacy,
                'frontendUser'   => $frontendUser,
                'hash'           => ($this->getFrontendUserByHash() ? $this->getFrontendUserByHash()->getTxRkwnewsletterHash() : ''),
            )
        );
    }


    /**
     * action update
     *
     * @param array $topics
     * @param integer $privacy
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @return void
     */
    public function updateAction($topics = array(), $privacy = null)
    {

        // FE-User has to be logged in or identified by hash
        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = ($this->getFrontendUser() ? $this->getFrontendUser() : $this->getFrontendUserByHash());
        if (!$frontendUser) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.notIdentified',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('new');
            //===
        }

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions */
        $subscriptions = $this->buildCleanedTopicList($topics);

        // opt out should be possible without opt-in for existing users ;-)
        // and without having to accept the privacy conditions
        if (count($subscriptions) < 1) {

            $frontendUser->setTxRkwnewsletterSubscription($subscriptions);
            $frontendUser->setTxRkwnewsletterHash('');
            $this->frontendUserRepository->update($frontendUser);

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.allSubscriptionsDeleted',
                    'rkw_newsletter'
                )
            );
            $this->redirect('new');
            //===
        }


        // check if there have been any changes!
        $existingSubscriptionIds = array();
        $newSubscriptionIds = array();
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($subscriptions as $topic) {
            $newSubscriptionIds[] = $topic->getUid();
        }
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($frontendUser->getTxRkwnewsletterSubscription() as $topic) {
            $existingSubscriptionIds[] = $topic->getUid();
        }

        if (
            (
                (count($newSubscriptionIds) > count($existingSubscriptionIds))
                && (count(array_diff($newSubscriptionIds, $existingSubscriptionIds)) < 1)
            )
            || (
                (count($existingSubscriptionIds) >= count($newSubscriptionIds))
                && (count(array_diff($existingSubscriptionIds, $newSubscriptionIds)) < 1)
            )
        ) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.nothingChanged',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('edit', null, null, $this->request->getArguments());
            //===
        }

        // check privacy field
        if (!$privacy) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_privacy',
                    'rkw_registration'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('edit', null, null, $this->request->getArguments());
            //===
        }


        // Case 1: FE-User is not logged in, but has been identified by hash-tag
        if (

            ($this->getFrontendUserByHash())
            && (!$this->getFrontendUser())
        ) {

            // register new user or simply send opt-in to existing user
            /** @var \RKW\RkwRegistration\Tools\Registration $registration */
            $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
            $registration->register(
                $frontendUser,
                false,
                [
                    'subscriptions' => $subscriptions,
                    'frontendUser' => $frontendUser
                ],
                'rkwNewsletter',
                $this->request
            );

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.optInCreated',
                    'rkw_newsletter'
                )
            );

            $this->redirect('message');
            //===

            // Case 2: Fe-User is logged in
        } else {
            if ($this->getFrontendUser()) {

                // set FeUser and save
                $frontendUser->setTxRkwnewsletterSubscription($subscriptions);
                $this->frontendUserRepository->update($frontendUser);

                \RKW\RkwRegistration\Tools\Privacy::addPrivacyData($this->request, $this->getFrontendUser(), $subscriptions, 'edited newsletter subscription');

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.message.subscriptionSaved',
                        'rkw_newsletter'
                    )
                );

                // Case 3: something is strange
            } else {

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.error.unexpectedError',
                        'rkw_newsletter'
                    )
                );
                $this->forward('new');
                //===
            }
        }

        $this->redirect('edit');
        //===

    }


    /**
     * action message
     *
     * @return void
     */
    public function messageAction()
    {
        // nothing to do here – just look good

        $this->view->assignMultiple(
            array(
                'frontendUser' => ($this->getFrontendUser() ? $this->getFrontendUser() : $this->getFrontendUserByHash()),
            )
        );
    }


    /**
     * action optIn
     * takes optIn parameter counter that were previously sent to the user via e-mail
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function optInAction()
    {

        $tokenYes = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : ''));
        $tokenNo = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : ''));
        $userSha1 = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        /** @var \RKW\RkwRegistration\Tools\Registration $register */
        $register = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $check = $register->checkTokens($tokenYes, $tokenNo, $userSha1, $this->request, $data);

        // set hash value for changing subscriptions without login
        $hash = '';
        if ($check == 1) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.subscriptionSaved',
                    'rkw_newsletter'
                )
            );

            if (
                ($data['frontendUser'])
                && ($frontendUser = $data['frontendUser'])
                && ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
                && ($frontendUser = $this->frontendUserRepository->findByIdentifier($frontendUser->getUid()))
            ) {
                /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
                if (!$frontendUser->getTxRkwnewsletterHash()) {
                    $hash = sha1($frontendUser->getUid() . $frontendUser->getEmail() . rand());
                    $frontendUser->setTxRkwnewsletterHash($hash);
                    $this->frontendUserRepository->update($frontendUser);

                } else {
                    $hash = $frontendUser->getTxRkwnewsletterHash();
                }
            }


        } elseif ($check == 2) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.subscriptionCanceled',
                    'rkw_newsletter'
                )
            );

            if (
                ($data['frontendUser'])
                && ($frontendUser = $data['frontendUser'])
                && ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
                && ($frontendUser = $this->frontendUserRepository->findByIdentifier($frontendUser->getUid()))
            ) {
                /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
                $hash = $frontendUser->getTxRkwnewsletterHash();
            }

        } else {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.subscriptionError',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }

        $this->redirect('message', null, null, array('hash' => $hash));
        //===
    }


    /**
     * createSubscription - used by SignalSlot
     * Called via SignalSlot after successfully completed optIn
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveSubscription(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase, \RKW\RkwRegistration\Domain\Model\Registration $registration)
    {
        if (
            ($registerData = $registration->getData())
            && ($subscriptions = $registerData['subscriptions'])
            && ($subscriptions instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage)
            && ($frontendUserUnsecure = $registerData['frontendUser'])
            && ($frontendUserUnsecure instanceof \RKW\RkwNewsletter\Domain\Model\FrontendUser)

        ) {
            // override with newsletter based model!
            /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUserDatabase */
            if ($frontendUserDatabase = $this->frontendUserRepository->findByIdentifier($frontendUserDatabase->getUid())) {

                // set subscription
                $frontendUserDatabase->setTxRkwnewsletterSubscription($subscriptions);

                // update fe-user data - no matter what
                $frontendUserDatabase->setTxRkwregistrationGender($frontendUserUnsecure->getTxRkwregistrationGender());
                $frontendUserDatabase->setTitle($frontendUserUnsecure->getTitle());
                $frontendUserDatabase->setTxRkwregistrationTitle($frontendUserUnsecure->getTxRkwregistrationTitle());
                $frontendUserDatabase->setFirstName($frontendUserUnsecure->getFirstName());
                $frontendUserDatabase->setLastName($frontendUserUnsecure->getLastName());
                $frontendUserDatabase->setCompany($frontendUserUnsecure->getCompany());

                $this->frontendUserRepository->update($frontendUserDatabase);
            }
        }
    }


    /**
     * buildCleanedTopicList
     * Build cleaned up topic list
     *
     * @param array $topics
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    protected function buildCleanedTopicList($topics = array())
    {

        // generate valid subscription list
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $topicList */
        $topicList = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

        // check if given topics exists and set them to subscription
        foreach ($topics as $topicId) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
            if ($topic = $this->topicRepository->findByUid($topicId)) {
                $topicList->attach($topic);
            }
        }

        return $topicList;
        //===
    }

    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage()
    {
        return false;
        //===
    }


}