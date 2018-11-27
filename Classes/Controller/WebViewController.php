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
 * WebViewController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
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
     * action show
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @param array $pages
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function showAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue, \RKW\RkwMailer\Domain\Model\QueueRecipient $queueRecipient, \RKW\RkwMailer\Domain\Model\QueueMail $queueMail, $pages = array())
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages> $finalPages */
        $finalPages = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $finalSpecialPages */
        $finalSpecialPages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, true);

        // get all pages of issue
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $issuePages */
        $issuePages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue);

        // check if given pages belong to the given issue
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        foreach ($issuePages as $page) {
            if (in_array($page->getUid(), $pages)) {
                $finalPages->attach($page);
            }
        }

        $this->view->assignMultiple(
            array(
                'issue'            => $issue,
                'pages'            => $finalPages,
                'specialPages'     => $finalSpecialPages,
                'maxItemsPerTopic' => 9999,
                'queueRecipient'   => $queueRecipient,
                'queueMail'        => $queueMail,
            )
        );
    }
}