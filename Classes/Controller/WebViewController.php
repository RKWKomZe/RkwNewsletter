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

use RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;


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
     * pagesRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository;

    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


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
     * @param string $pagesOrder
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function showAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $pagesOrder = '')
    {

        // check for queueMailId and queueRecipientId as params from redirection
        $rkwMailerParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwmailer');
        $queueMailId = intval($rkwMailerParams['mid']);
        $queueRecipientId = intval($rkwMailerParams['uid']);
        $pagesOrder = preg_replace('/[^0-9,]+/', '', $pagesOrder);

        // set default recipient based on FE-language settings – just in case
        /** @var \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Domain\\Model\\QueueRecipient');
        $queueRecipient->setLanguageCode($GLOBALS['TSFE']->config['config']['language']);
        $this->view->assign('queueRecipient', $queueRecipient);

        // check if there is a recipient given
        $frontendUser = null;
        if (
            ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($queueRecipient = $this->queueRecipientRepository->findByUid($queueRecipientId))
        ) {

            // get subscription from FE-User - or load everything of issue
            $frontendUser =  $this->frontendUserRepository->findByIdentifier($queueRecipient->getFrontendUser());

            // assign objects to view
            $this->view->assignMultiple(
                array(
                    'queueRecipient'   => $queueRecipient,
                    'queueMail'        => $queueMail,
                )
            );
        }

        // if frontendUser is given, we use it's subscriptions
        if (
            ($frontendUser)
            && ($issue->getNewsletter()->getType() != 1)
        ){
            $pages = $this->pagesRepository->findAllByIssueAndSubscription($issue, $frontendUser->getTxRkwnewsletterSubscription(), $pagesOrder);
        } else {
            $pages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, false, $pagesOrder);
        }

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $finalSpecialPages */
        $specialPages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, true);


        // add paths depending on template of newsletter - including the default one!
        $settings = Common::getTyposcriptConfiguration('Rkwnewsletter', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK );
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

        $this->view->assignMultiple(
            array(
                'issue'            => $issue,
                'pages'            => $pages,
                'specialPages'     => $specialPages,
                'maxItemsPerTopic' => 9999,
                'webView'          => true
            )
        );
    }
}