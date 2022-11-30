<?php

namespace RKW\RkwNewsletter\ViewHelpers\Backend;
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

use RKW\RkwNewsletter\Domain\Model\Issue;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetNonSpecialTopicsViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetNonSpecialTopicsViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('issue', Issue::class, 'Get non-special topics from this issue.', true);
    }


    /**
     * Gets all topics of the issue without special topics
     *
     * @return array
     */
    public function render(): array
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->arguments['issue'];
        $finalTopics = array();

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        if ($issue->getPages()) {
            foreach ($issue->getPages() as $page) {

                /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
                if ($topic = $page->getTxRkwnewsletterTopic()) {
                    if (!$topic->getIsSpecial()) {
                        $finalTopics[] = $topic;
                    }
                }
            }
        }

        return $finalTopics;
    }
}
