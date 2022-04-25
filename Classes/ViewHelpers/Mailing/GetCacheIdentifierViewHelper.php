<?php
namespace RKW\RkwNewsletter\ViewHelpers\Mailing;
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
use RKW\RkwNewsletter\Mailing\ContentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;


/**
 * GetCacheIdentifierViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetCacheIdentifierViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('topics', ObjectStorage::class, 'ObjectStorage of topics to load contents for. (optional, default: all).', false, null);
        $this->registerArgument('limit', 'int', 'Limits the amount of returned contents for each topic. (optional, default: all).', false, 0);
    }


    /**
     * Gets cache identifier based on the given params
     *
     * @return string
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function render(): string
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->arguments['issue'];
        $topics = $this->arguments['topics'];
        $limit = intval($this->arguments['limit']);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $contentLoader */
        $contentLoader = $objectManager->get(ContentLoader::class, $issue);

        // set topics
        if ($topics) {
            $contentLoader->setTopics($topics);
        }
        
        // get topic-ordering
        $ordering = $contentLoader->getOrdering();
        return $issue->getUid() . '_' . implode('-', array_keys($ordering)) . '_' . $limit;
    }
}

