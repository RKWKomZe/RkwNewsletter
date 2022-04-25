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

use RKW\RkwMailer\Domain\Model\QueueRecipient;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwNewsletter\Domain\Model\Issue;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;


/**
 * WebViewController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class WebViewController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * topicRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository;


    /**
     * QueueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @inject
     */
    protected $queueMailRepository;

    /**
     * QueueRecipientRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueRecipientRepository
     * @inject
     */
    protected $queueRecipientRepository;


    /**
     * action show
     *
     * @ignorevalidation $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param array $topicsRaw
     * @param string $hash
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function showAction(Issue $issue, array $topicsRaw = [], string $hash = '')
    {
        
        $queueMailId = 0;
        $queueRecipientId = 0;

        // check for queueMailId and queueRecipientId as params from redirection
        $rkwMailerParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwmailer');
        if (isset($rkwMailerParams['mid'])) {
            $queueMailId = intval($rkwMailerParams['mid']);
        }
        if (isset($rkwMailerParams['uid'])) {
            $queueRecipientId = intval($rkwMailerParams['uid']);
        }
        
        // set default recipient based on FE-language settings – just in case
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $queueRecipient->setLanguageCode($GLOBALS['TSFE']->config['config']['language']);
        $this->view->assign('queueRecipient', $queueRecipient);

        // check if there is a recipient given
        if (
            ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($queueRecipient = $this->queueRecipientRepository->findByUid($queueRecipientId))
        ) {

            // assign objects to view
            $this->view->assignMultiple(
                array(
                    'queueRecipient'   => $queueRecipient,
                    'queueMail'        => $queueMail,
                )
            );
        }
        
        // convert topic-ids to objects
        $topics = new ObjectStorage();
        foreach ($topicsRaw as $topicId) {
            if ($topic = $this->topicRepository->findByIdentifier($topicId)) {
                $topics->attach($topic);
            }
        }
        

        // add paths depending on template of newsletter - including the default one!
        $settings = GeneralUtility::getTyposcriptConfiguration(
            'Rkwnewsletter', 
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        
        $layoutPaths = $settings['view']['newsletter']['layoutRootPaths'];
        $layoutPathsNew = [];
        if (is_array($layoutPaths)) {
            foreach ($layoutPaths as $path) {
                $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $layoutPathsNew[] = $path . 'Default';
                if ($issue->getNewsletter()->getTemplate() != 'Default') {
                    $layoutPathsNew[] = $path . $issue->getNewsletter()->getTemplate();
                }
            }
        }

        $partialPaths = $settings['view']['newsletter']['partialRootPaths'];
        $partialPathsNew = [];
        if (is_array($partialPaths)) {
            foreach ($partialPaths as $path) {
                $path = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $partialPathsNew[] = $path . 'Default';
                if ($issue->getNewsletter()->getTemplate() != 'Default') {
                    $partialPathsNew[] = $path . $issue->getNewsletter()->getTemplate();
                }
            }
        }

        $this->view->setLayoutRootPaths($layoutPathsNew);
        $this->view->setPartialRootPaths($partialPathsNew);

        // override maxContentItems
        $settings['settings']['maxContentItems'] = 9999;
        $this->view->assignMultiple(
            array(
                'issue'      => $issue,
                'topics'     => $topics,
                'hash'       => $hash,
                'settings'   => $settings['settings'],
                'isWebView'  => true
            )
        );
    }
}