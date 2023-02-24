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

use RKW\RkwNewsletter\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * HasBackendUserPermissionViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class HasBackendUserPermissionMultipleViewHelper extends AbstractHasBackendUserPermissionViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('issues', QueryResultInterface::class, 'Check permissions for this list of issues.', true);
        $this->registerArgument('topic', Topic::class, 'Check permissions for this topic (optional).', false, null);
        $this->registerArgument('approvalStage', 'int', 'Approval level to check. (optional, default:1)', false, null);
        $this->registerArgument('allApprovals', 'bool', 'Check for all approval, regardless on which topic or stage. (optional, default: false)', false, false);
    }


    /**
     * Checks if backendUser can approve/release at any given level of the issue-list
     *
     * @return int
     */
    public function render(): int
    {
        $issues = $this->arguments['issues'];
        $topic = is_object($this->arguments['topic']) ? $this->arguments['topic'] : null;
        $approvalStage = intval($this->arguments['approvalStage']) ?: 1;
        $allApprovals = boolval($this->arguments['allApprovals']);

        foreach ($issues as $issue) {
            if (self::checkPermissions($issue, $allApprovals, $topic, $approvalStage)) {
                return 1;
            }
        }

        return 0;
    }

}
