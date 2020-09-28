<?php

namespace RKW\RkwNewsletter\ViewHelpers;
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
 * GetNewsletterTopicsWithoutSpecialsViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetNewsletterTopicsWithoutSpecialsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Gets all topics of the issue without special topics
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return array
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
    {

        $finalTopics = array();

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($issue->getNewsletter()->getTopic() as $topic) {

            if (!$topic->getIsSpecial()) {
                $finalTopics[] = $topic;
            }
        }

        return $finalTopics;
    }
}