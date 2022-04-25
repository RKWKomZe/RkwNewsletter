<?php
namespace RKW\RkwNewsletter\ViewHelpers\Mailing\Content;
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

use RKW\RkwNewsletter\Domain\Model\Content;
use RKW\RkwNewsletter\Mailing\ContentLoader;
use RKW\RkwNewsletter\ViewHelpers\Mailing\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * GetTopicNameViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetTopicNameViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('content', Content::class, 'Content to get topic for.', true);
    }


    /**
     * Gets the contents of the given issue and respects given topics
     *
     * @return string
     * @throws \RKW\RkwNewsletter\Exception
      */
    public function render():string
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->arguments['issue'];

        /** @var \RKW\RkwNewsletter\Domain\Model\Content $content */
        $content = $this->arguments['content'];
                            
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        
        /** @var \RKW\RkwNewsletter\Mailing\ContentLoader $contentLoader */
        $contentLoader = $objectManager->get(ContentLoader::class, $issue);
        
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        if ($topic = $contentLoader->getTopicOfContent($content)) {
            return $topic->getName(); 
        }
        
        return '';
    }
}

