<?php

namespace RKW\RkwNewsletter\TCA;

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

use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class OptionLabels
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptionLabels
{
    /**
     * Fetches labels for newsletter topics
     *
     * @params array &$params
     * @params object $pObj
     * @param array $params
     * @param $pObj
     * @return void
     */
    public static function getNewsletterTopicTitlesWithRootByUid(array &$params, $pObj): void
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository $topic */
        $topicRepository = $objectManager->get(TopicRepository::class);
        $result = $topicRepository->findAll();

        // build extended names
        $extendedNames = [];

        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        foreach ($result as $topic) {
            $extendedNames[$topic->getUid()] = self::getExtendedTopicName($topic);
        }

        // override given values
        foreach ($params['items'] as &$item) {
            if (isset($extendedNames[$item[1]])) {
                $item[0] = $extendedNames[$item[1]];
            }
        }
    }


    /**
     * Return extended name for a newsletter topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return string
     */
    public static function getExtendedTopicName(Topic $topic): string
    {

        if ($topic->getNewsletter()) {
            return $topic->getName() . ' (' . $topic->getNewsletter()->getName() . ')';
        }

        return $topic->getName();

    }

}
