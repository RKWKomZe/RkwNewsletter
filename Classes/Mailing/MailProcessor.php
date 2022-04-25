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

use RKW\RkwNewsletter\Domain\Model\FrontendUser;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Exception;
use RKW\RkwNewsletter\Status\IssueStatus;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use RKW\RkwBasics\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    protected $issue;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    
    /**
     * newsletterRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;
    
    
    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;


    /**
     * persistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    
    /**
     * @var \RKW\RkwNewsletter\Mailing\ContentLoader
     * @inject
     */
    protected $contentLoader;


    /**
     * @var \RKW\RkwMailer\Service\MailService
     * @inject
     */
    protected $mailService;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    
    /**
     * Gets the mailService
     *
     * @return \RKW\RkwMailer\Service\MailService
     */
    public function getMailService()
    {
        return $this->mailService;
    }


    /**
     * Gets the contentLoader
     *
     * @return \RKW\RkwNewsletter\Mailing\ContentLoader
     */
    public function getContentLoader()
    {
        return $this->contentLoader;
    }

    
    /**
     * Gets the issue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue|null
     */
    public function getIssue()
    {
        return $this->issue;
    }


    /**
     * Sets the issue and inits mailService
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue $issue
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
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
     * Gets the recipients and updated offsetSent for issue
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function getSubscribers(int $limit = 0): QueryResultInterface
    {

        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650541235);
        }
        
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $recipients */
        $recipients = $this->frontendUserRepository->findSubscriptionsByNewsletter(
            $this->issue->getNewsletter(), 
            $this->issue->getSentOffset(),
            $limit
        );
        
        // update offset-value
        $this->issue->setSentOffset($this->issue->getSentOffset() + count($recipients->toArray()));
        $this->issueRepository->update($this->issue);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf('Fetched %s recipients for issue with id=%s.',
                count($recipients->toArray()),
                $this->issue->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
        return $recipients;
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
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Topic> $topics
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
     * @throws \RKW\RkwMailer\Exception
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
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        
        // set topics according to subscription
        $this->setTopics($frontendUser->getTxRkwnewsletterSubscription());
        
        // check for contents!
        if ($this->contentLoader->hasContents()) {
            
            // send email via mailService
            $result = $this->mailService->setTo(
                $frontendUser,
                array(
                    'marker'  => array(
                        'issue'             => $this->issue,
                        'topics'            => $this->contentLoader->getTopics(),
                        'hash'              => $this->getSubscriptionHash($frontendUser),
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
     * @throws \RKW\RkwMailer\Exception
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
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        // check for contents!
        if ($this->contentLoader->hasContents()) {

            // send email via mailService
            $result = $this->mailService->setTo(
                [
                    'fistName' => 'Maxima',
                    'lastName' => 'Musterfrau',
                    'tx_rkwregistration_gender' => 1,
                    'title' => 'Prof. Dr. Dr.',
                    'email' => $email
                ],
                array(
                    'marker'  => array(
                        'issue'             => $this->issue,
                        'topics'            => $this->contentLoader->getTopics(),
                        'settings'          => $settings['settings'],
                    ),
                    'subject' => $this->getSubject(),
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
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendMails(int $limit = 0): bool
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $this->issue) {
            throw new Exception('No issue is set.', 1650636949);
        }
        
        if (
            ($subscribers = $this->getSubscribers($limit))
            && (count($subscribers))
        ){

            /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
            foreach($subscribers as $frontendUser) {

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

            $this->mailService->send();
            return true;
        }

        // no subscribers left? Then end current sending!
        // remove pipeline flag
        $this->mailService->stopPipelining();
        
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
     * @throws \RKW\RkwMailer\Exception
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

            $this->mailService->send();
            return true;
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
     * inits mailService
     *
     * @return void
     * @throws \RKW\RkwMailer\Exception
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
                $this->mailService->setQueueMail($queueMail);

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Initialized mailService for issue with id=%s of newsletter-configuration with id=%s with existing queueMail-object with id=%s.',
                        $this->issue->getUid(),
                        $this->issue->getNewsletter()->getUid(),
                        $this->mailService->getQueueMail()->getUid()
                    )
                );
                
            } else {
                
                // load settings
                $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

                // set properties for queueMail
                /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
                $queueMail = $this->mailService->getQueueMail();
                $this->mailService->getQueueMail()->setType(1);
                $this->mailService->getQueueMail()->setSubject($this->issue->getTitle());
                $this->mailService->getQueueMail()->setCategory('rkwNewsletter');
                if ($this->issue->getNewsletter()->getSettingsPage()) {
                    $this->mailService->getQueueMail()->setSettingsPid($this->issue->getNewsletter()->getSettingsPage()->getUid());
                }

                // set mail params
                if ($this->issue->getNewsletter()->getReturnPath()) {
                    $this->mailService->getQueueMail()->setReturnPath($this->issue->getNewsletter()->getReturnPath());
                }
                if ($this->issue->getNewsletter()->getReplyMail()) {
                    $this->mailService->getQueueMail()->setReplyToAddress($this->issue->getNewsletter()->getReplyMail());
                }
                if ($this->issue->getNewsletter()->getSenderMail()) {
                    $this->mailService->getQueueMail()->setFromAddress($this->issue->getNewsletter()->getSenderMail());
                }
                if ($this->issue->getNewsletter()->getSenderName()) {
                    $this->mailService->getQueueMail()->setReplyToName($this->issue->getNewsletter()->getSenderName());
                    $this->mailService->getQueueMail()->setFromName($this->issue->getNewsletter()->getSenderName());
                }

                $this->mailService->getQueueMail()->addLayoutPaths($settings['view']['newsletter']['layoutRootPaths']);
                $this->mailService->getQueueMail()->addTemplatePaths($settings['view']['newsletter']['templateRootPaths']);
                $this->mailService->getQueueMail()->addPartialPaths($settings['view']['newsletter']['partialRootPaths']);

                // add paths depending on template - including the default one!
                $layoutPaths = $settings['view']['newsletter']['layoutRootPaths'];
                if (is_array($layoutPaths)) {
                    foreach ($layoutPaths as $path) {
                        $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        $this->mailService->getQueueMail()->addLayoutPath($path . 'Default');
                        if ($this->issue->getNewsletter()->getTemplate() != 'Default') {
                            $this->mailService->getQueueMail()->addLayoutPath(
                                $path . $this->issue->getNewsletter()->getTemplate()
                            );
                        }
                    }
                }

                $partialPaths = $settings['view']['newsletter']['partialRootPaths'];
                if (is_array($partialPaths)) {
                    foreach ($partialPaths as $path) {
                        $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        $this->mailService->getQueueMail()->addPartialPath($path . 'Default');
                        if ($this->issue->getNewsletter()->getTemplate() != 'Default') {
                            $this->mailService->getQueueMail()->addPartialPath(
                                $path . $this->issue->getNewsletter()->getTemplate()
                            );
                        }
                    }
                }

                $this->mailService->getQueueMail()->setPlaintextTemplate(
                    ($this->issue->getNewsletter()->getTemplate() ?: 'Default')
                );
                $this->mailService->getQueueMail()->setHtmlTemplate(                    
                    ($this->issue->getNewsletter()->getTemplate() ?: 'Default')
                );

                // only add issue and start pipelining if mail is released!!!
                // do not do this in test-mode!
                if (IssueStatus::getStage($this->issue) == IssueStatus::STAGE_SENDING) {
                    $this->mailService->startPipelining();
                    $this->issue->setQueueMail($queueMail);
                    $this->issueRepository->update($this->issue);
                    $this->persistenceManager->persistAll();
                }

                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf('Initialized mailService for issue with id=%s of newsletter-configuration with id=%s with new queueMail-object with id=%s.',
                        $this->issue->getUid(),
                        $this->issue->getNewsletter()->getUid(),
                        $this->mailService->getQueueMail()->getUid()
                    )
                );
            }
        }
        self::debugTime(__LINE__, __METHOD__);
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

        
    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwnewsletter', $which);
    }

    
    /**
     * Does debugging of runtime
     *
     * @param integer $line
     * @param string  $function
     */
    private static function debugTime($line, $function)
    {

        if (GeneralUtility::getApplicationContext()->isDevelopment()) {

            $path = PATH_site . '/typo3temp/var/logs/tx_rkwnewsletter_runtime.txt';
            file_put_contents($path, microtime() . ' ' . $line . ' ' . $function . "\n", FILE_APPEND);
        }
    }
}