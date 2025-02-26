<?php
namespace RKW\RkwNewsletter\Mailing;

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

use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
use Madj2k\Postmaster\Validation\QueueMailValidator;
use Madj2k\Postmaster\Mail\MailMessage;
use RKW\RkwNewsletter\Domain\Model\FrontendUser;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Exception;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * MailProcessor
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailProcessor
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Issue|null
     */
    protected ?Issue $issue = null;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?NewsletterRepository $newsletterRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?IssueRepository $issueRepository;


    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?FrontendUserRepository $frontendUserRepository;


    /**
     * @var \RKW\RkwNewsletter\Mailing\ContentLoader
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ContentLoader $contentLoader;


    /**
     * @var \Madj2k\Postmaster\Mail\MailMessage
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?MailMessage $mailMessage;


    /**
     * @var \Madj2k\Postmaster\Validation\QueueMailValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?QueueMailValidator $queueMailValidator;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?PersistenceManager $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * @var array
     */
    protected array $settings = [];


    /**
     * @param \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository $newsletterRepository
     */
    public function injectNewsletterRepository(NewsletterRepository $newsletterRepository)
    {
        $this->newsletterRepository = $newsletterRepository;
    }


    /**
     * @param \RKW\RkwNewsletter\Domain\Repository\IssueRepository $issueRepository
     */
    public function injectIssueRepository(IssueRepository $issueRepository)
    {
        $this->issueRepository = $issueRepository;
    }

    /**
     * @param \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }


    /**
     * @param \RKW\RkwNewsletter\Mailing\ContentLoader $contentLoader
     */
    public function injectContentLoader(ContentLoader $contentLoader)
    {
        $this->contentLoader = $contentLoader;
    }


    /**
     * @param \Madj2k\Postmaster\Mail\MailMessage $mailMessage
     */
    public function injectMailMessage(MailMessage $mailMessage)
    {
        $this->mailMessage = $mailMessage;
    }


    /**
     * @param \Madj2k\Postmaster\Validation\QueueMailValidator $queueMailValidator
     */
    public function injectQueueMailValidator(QueueMailValidator $queueMailValidator)
    {
        $this->queueMailValidator = $queueMailValidator;
    }


    /**
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }


    /**
     * Gets the mailMessage
     *
     * @return \Madj2k\Postmaster\Mail\MailMessage
     */
    public function getMailMessage(): MailMessage
    {
        return $this->mailMessage;
    }


    /**
     * Gets the contentLoader
     *
     * @return \RKW\RkwNewsletter\Mailing\ContentLoader
     */
    public function getContentLoader(): ContentLoader
    {
        return $this->contentLoader;
    }


    /**
     * Gets the issue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue|null
     */
    public function getIssue():? Issue
    {
        return $this->issue;
    }


    /**
     * Sets the issue and inits mailMessage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue $issue
     * @return void
     * @throws Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setIssue(Issue $issue): void
    {
        self::debugTime(__LINE__, __METHOD__);
        if ($issue->_isNew()) {
            throw new Exception('Issue-object has to be persisted.', 1650541236);
        }

        if (! $issue->getNewsletter()) {
            throw new Exception('No newsletter-object for this issue set.', 1650541234);
        }

        $this->issue = $issue;
        $this->contentLoader->setIssue($issue);
        $this->init();
        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * Gets the recipients and adds them to issue
     *
     * @return bool
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setRecipients(): bool
    {

        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650541235);
        }

        // Check if not yet started!
        if (! $this->issue->getStartTstamp()) {

            // reset
            $this->issue->setRecipients([]);

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $recipients */
            $subscribers = $this->frontendUserRepository->findSubscriptionsByNewsletter($this->issue->getNewsletter());

            /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
            foreach ($subscribers as $frontendUser) {
                $this->issue->addRecipient($frontendUser);
            }

            $this->issueRepository->update($this->issue);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf('Added recipients to issue with id=%s.',
                    $this->issue->getUid()
                )
            );

            self::debugTime(__LINE__, __METHOD__);
            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf('No recipients added to issue with id=%s.',
                $this->issue->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * Gets the subscription has for a frontendUser
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function getSubscriptionHash(FrontendUser $frontendUser): string
    {

        self::debugTime(__LINE__, __METHOD__);
        // generate hash-value if none exists
        if (! $frontendUser->getTxRkwnewsletterHash()) {
            $hash = sha1($frontendUser->getUid() . $frontendUser->getEmail() . rand());
            $frontendUser->setTxRkwnewsletterHash($hash);
            $this->frontendUserRepository->update($frontendUser);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf('Generated subscription-hash for frontendUser with uid=%s.',
                    $frontendUser->getUid()
                )
            );
        }

        self::debugTime(__LINE__, __METHOD__);
        return $frontendUser->getTxRkwnewsletterHash();
    }


    /**
     * Sets the subscribed topics and shuffles them
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic>|null $topics
     * @return void
     * @throws Exception
     */
    public function setTopics(ObjectStorage $topics = null): void
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650549470);
        }

        // if no topic-parameter given, we take the existing and shuffle them!
        if (!$topics) {
            $topics = $this->contentLoader->getTopics();
        }

        // shuffle order and set it as reference for contentLoader
        $topicsArray = $topics->toArray();
        shuffle($topicsArray);

        $topicsShuffled = new ObjectStorage();
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topics */
        foreach ($topicsArray as $topic) {
            $topicsShuffled->attach($topic);
        }

        $this->contentLoader->setTopics($topicsShuffled);

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf('Set shuffled topics for issue with id=%s.',
                $this->issue->getUid()
            )
        );
        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * Gets the current subject for the mail
     *
     * @return string
     * @throws Exception
     */
    public function getSubject(): string
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650549470);
        }

        $firstHeadline = $this->contentLoader->getFirstHeadline();
        self::debugTime(__LINE__, __METHOD__);
        return ($firstHeadline ? ($this->issue->getTitle() . ' – ' . $firstHeadline) : $this->issue->getTitle());
    }


    /**
     * Sends an email to the given frontendUser with his subscriptions
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @return bool
     * @throws Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendMail(FrontendUser $frontendUser): bool
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650608449);
        }

        // load settings
        $settings = $this->getSettings();

        // set topics according to subscription
        $this->setTopics($frontendUser->getTxRkwnewsletterSubscription());

        // check for contents!
        if ($this->contentLoader->hasContents()) {

            // send email via mailMessage
            $result = $this->mailMessage->setTo(
                $frontendUser,
                array(
                    'marker'  => array(
                        'issue'             => $this->issue,
                        'topics'            => $this->contentLoader->getTopics(),
                        'hash'              => $this->getSubscriptionHash($frontendUser),
                        'limit'             => ($this->contentLoader->getTopics()->count() > 1)
                                                ? $settings['settings']['maxContentItemsPerTopic']
                                                : $settings['settings']['maxContentItems'],
                        'settings'          => $settings['settings'],
                    ),
                    'subject' => $this->getSubject(),
                ),
                true
            );

            if ($result) {

                self::debugTime(__LINE__, __METHOD__);
                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Added frontendUser with id=%s to recipients of issue with id=%s.',
                        $frontendUser->getUid(),
                        $this->issue->getUid()
                    )
                );

                return true;
            }
        }

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf('Did not add frontendUser with id=%s to recipients of issue with id=%s.',
                $frontendUser->getUid(),
                $this->issue->getUid()
            )
        );

        return false;
    }


    /**
     * Sends an email to the given email
     *
     * @param string $email
     * @return bool
     * @throws Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendTestMail(string $email): bool
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650629464);
        }

        // load settings
        $settings = $this->getSettings();

        // check for contents!
        if ($this->contentLoader->hasContents()) {

            // send email via mailMessage
            $result = $this->mailMessage->setTo(
                [
                    'fistName' => 'Maxima',
                    'lastName' => 'Musterfrau',
                    'txFeregisterGender' => 1,
                    'title' => 'Prof. Dr. Dr.',
                    'email' => $email,
                ],
                array(
                    'marker'  => array(
                        'issue'             => $this->issue,
                        'topics'            => $this->contentLoader->getTopics(),
                        'limit'             => ($this->contentLoader->getTopics()->count() > 1)
                                                ? $settings['settings']['maxContentItemsPerTopic']
                                                : $settings['settings']['maxContentItems'],
                        'settings'          => $settings['settings'],
                    ),
                    'subject' => 'TEST: ' . $this->getSubject(),
                ),
                false
            );

            if ($result) {

                self::debugTime(__LINE__, __METHOD__);
                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Added email "%s" to test-recipients of issue with id=%s.',
                        $email,
                        $this->issue->getUid()
                    )
                );

                return true;
            }
        }

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf('Did not add email "%s" to test-recipients of issue with id=%s.',
                $email,
                $this->issue->getUid()
            )
        );

        return false;
    }


    /**
     * Sends emails to all subscribers
     *
     * @param int $limit
     * @return bool
     * @throws Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \Exception
     */
    public function sendMails(int $limit = 0): bool
    {
        self::debugTime(__LINE__, __METHOD__);
        if (!$this->issue) {
            throw new Exception('No issue is set.', 1650636949);
        }

        // set startTstamp - no matter what!
        // persist queueMail on start to be able to start pipelining!
        if (! $this->issue->getStartTstamp()) {

            $this->issue->setStartTstamp(time());
            $this->issue->setQueueMail($this->mailMessage->getQueueMail());
            $this->issueRepository->update($this->issue);
            $this->persistenceManager->persistAll();

            $this->mailMessage->getQueueMail()->setType(1);
            $this->mailMessage->startPipelining();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf('Started sending of issue with id=%s.',
                    $this->issue->getUid()
                )
            );
        }

        // work through recipients
        if ($this->issue->getRecipients()) {

            $cnt = 0;
            /** @var int $frontendUserUid */
            foreach ($this->issue->getRecipients() as $frontendUserUid) {

                /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
                if ($frontendUser = $this->frontendUserRepository->findByUid($frontendUserUid)) {

                    try {
                        $this->sendMail($frontendUser);
                    } catch (\Exception $e) {
                        $this->getLogger()->log(
                            LogLevel::ERROR,
                            sprintf('Could not send issue with id=%s to recipient with id=%s. Reason: %s.',
                                $this->issue->getUid(),
                                $frontendUser->getUid(),
                                $e->getMessage()
                            )
                        );
                    }
                }

                // remove userId from list - if it exists or not!
                $this->issue->removeRecipientById($frontendUserUid);

                $cnt++;
                if ($cnt >= $limit) {
                    break;
                }
            }

            $this->issueRepository->update($this->issue);
            $this->persistenceManager->persistAll();

            // send mail
            $this->mailMessage->send();
            return true;
        }

        // no subscribers left? Then end current sending!
        // remove pipeline flag
        $this->mailMessage->stopPipelining();

        // set status and timestamp
        $this->issue->setSentTstamp(time());
        $this->issue->setStatus(IssueStatus::STAGE_DONE);

        // set timestamp to newsletter
        $this->issue->getNewsletter()->setLastSentTstamp($this->issue->getSentTstamp());
        $this->newsletterRepository->update($this->issue->getNewsletter());
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf('Finished sending of issue with id=%s.',
                $this->issue->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * Sends emails to all given emails
     *
     * @param string $emails
     * @return bool
     * @throws Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function sendTestMails(string $emails): bool
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650636949);
        }

        if (
            ($recipients = GeneralUtility::trimExplode(',', $emails, true))
            && (count($recipients))
        ){

            foreach($recipients as $recipient) {

                try {
                    $this->sendTestMail($recipient);
                } catch (\Exception $e) {
                    $this->getLogger()->log(
                        LogLevel::ERROR,
                        sprintf('Could not send test-mail for issue with id=%s to recipient with email "%s". Reason: %s.',
                            $this->issue->getUid(),
                            $recipient,
                            $e->getMessage()
                        )
                    );
                }
            }

            return $this->mailMessage->send();
        }

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf('Finished test-sending of issue with id=%s.',
                $this->issue->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * inits mailMessage
     *
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function init()
    {
        self::debugTime(__LINE__, __METHOD__);
        if (
            ($this->issue)
            && ($this->issue->getNewsletter())
        ) {

            if ($queueMail = $this->issue->getQueueMail()) {
                $this->mailMessage->setQueueMail($queueMail);

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Initialized mailMessage for issue with id=%s of newsletter-configuration with id=%s with existing queueMail-object with id=%s.',
                        $this->issue->getUid(),
                        $this->issue->getNewsletter()->getUid(),
                        $this->mailMessage->getQueueMail()->getUid()
                    )
                );

            } else {

                // set settingsPid and load settings from there
                $settings = $this->getSettings();
                if ($this->issue->getNewsletter()->getSettingsPage()) {
                    $this->mailMessage->getQueueMail()->setSettingsPid($this->issue->getNewsletter()->getSettingsPage()->getUid());
                }

                // set properties for queueMail
                /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
                $this->mailMessage->getQueueMail()->setSubject($this->issue->getTitle());
                $this->mailMessage->getQueueMail()->setCategory('rkwNewsletter');


                // set mail params
                if ($this->issue->getNewsletter()->getReturnPath()) {
                    $this->mailMessage->getQueueMail()->setReturnPath($this->issue->getNewsletter()->getReturnPath());
                }
                if ($this->issue->getNewsletter()->getReplyMail()) {
                    $this->mailMessage->getQueueMail()->setReplyToAddress($this->issue->getNewsletter()->getReplyMail());
                }
                if ($this->issue->getNewsletter()->getSenderMail()) {
                    $this->mailMessage->getQueueMail()->setFromAddress($this->issue->getNewsletter()->getSenderMail());
                }
                if ($this->issue->getNewsletter()->getSenderName()) {
                    $this->mailMessage->getQueueMail()->setReplyToName($this->issue->getNewsletter()->getSenderName());
                    $this->mailMessage->getQueueMail()->setFromName($this->issue->getNewsletter()->getSenderName());
                }

                $this->mailMessage->getQueueMail()->setPlaintextTemplate(
                    ($this->issue->getNewsletter()->getTemplate() ?: 'Default')
                );
                $this->mailMessage->getQueueMail()->setHtmlTemplate(
                    ($this->issue->getNewsletter()->getTemplate() ?: 'Default')
                );

                $this->mailMessage->getQueueMail()->addLayoutPaths($settings['view']['newsletter']['layoutRootPaths']);
                $this->mailMessage->getQueueMail()->addTemplatePaths($settings['view']['newsletter']['templateRootPaths']);
                $this->mailMessage->getQueueMail()->addPartialPaths($settings['view']['newsletter']['partialRootPaths']);

                // add paths depending on template - including the default one!
                $layoutPaths = $settings['view']['newsletter']['layoutRootPaths'];
                if (is_array($layoutPaths)) {
                    foreach ($layoutPaths as $path) {
                        $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        $this->mailMessage->getQueueMail()->addLayoutPath($path . 'Default');
                        if ($this->issue->getNewsletter()->getTemplate() != 'Default') {
                            $this->mailMessage->getQueueMail()->addLayoutPath(
                                $path . $this->issue->getNewsletter()->getTemplate()
                            );
                        }
                    }
                }

                $partialPaths = $settings['view']['newsletter']['partialRootPaths'];
                if (is_array($partialPaths)) {
                    foreach ($partialPaths as $path) {
                        $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        $this->mailMessage->getQueueMail()->addPartialPath($path . 'Default');
                        if ($this->issue->getNewsletter()->getTemplate() != 'Default') {
                            $this->mailMessage->getQueueMail()->addPartialPath(
                                $path . $this->issue->getNewsletter()->getTemplate()
                            );
                        }
                    }
                }

                /**
                 * @todo Add further template paths based on settingsPid
                 */

                // last but not least: check if queueMail has all configuration needed for sending
                if (! $this->queueMailValidator->validate($this->mailMessage->getQueueMail())) {
                    throw new Exception('Newsletter is missing essential configuration. Sending will not be possible.', 1651215173);
                }

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Initialized mailMessage for issue with id=%s of newsletter-configuration with id=%s with new queueMail-object with id=%s.',
                        $this->issue->getUid(),
                        $this->issue->getNewsletter()->getUid(),
                        $this->mailMessage->getQueueMail()->getUid()
                    )
                );
            }
        }
        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK): array
    {

        $pid = 1;
        if ($this->issue->getNewsletter()->getSettingsPage()) {
            $pid = $this->issue->getNewsletter()->getSettingsPage()->getUid();
        }

        if (isset($this->settings[$pid])) {
            return $this->settings[$pid];
        }

        FrontendSimulatorUtility::simulateFrontendEnvironment($pid);
        $this->settings[$pid] = GeneralUtility::getTypoScriptConfiguration('Rkwnewsletter', $which);
        FrontendSimulatorUtility::resetFrontendEnvironment();

        return $this->settings[$pid];
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


    /**
     * Does debugging of runtime
     *
     * @param int $line
     * @param string  $function
     * @return void
     */
    private static function debugTime(int $line, string $function): void
    {

        if (\TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment()) {

            $path = \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/tx_rkwnewsletter_runtime.txt';
            file_put_contents($path, microtime() . ' ' . $line . ' ' . $function . "\n", FILE_APPEND);
        }
    }
}
