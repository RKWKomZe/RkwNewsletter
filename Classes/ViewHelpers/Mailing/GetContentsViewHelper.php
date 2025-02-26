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

use RKW\RkwNewsletter\Mailing\ContentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * GetContentsViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetContentsViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('topics', ObjectStorage::class, 'ObjectStorage of topics to load contents for. (optional, default: all).', false, null);
        $this->registerArgument('limit', 'int', 'Limits the amount of returned contents for each topic. (optional, default: all).', false, 0);
    }


    /**
     * Gets the contents of the given issue and respects given topics
     *
     * @return array
     * @throws \RKW\RkwNewsletter\Exception
      */
    public function render(): array
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->arguments['issue'];
        $topics = $this->arguments['topics'];
        $limit = intval($this->arguments['limit']);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $contentLoader */
        $contentLoader = $objectManager->get(ContentLoader::class, $issue);

        // set topics and load content
        if ($topics) {
            $contentLoader->setTopics($topics);
        }

        return $contentLoader->getContents($limit);
    }
}

