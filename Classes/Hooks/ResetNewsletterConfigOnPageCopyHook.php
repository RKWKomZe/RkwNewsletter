<?php
namespace RKW\RkwNewsletter\Hooks;

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
 * ResetNewsletterConfigOnPageCopyHook
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class ResetNewsletterConfigOnPageCopyHook
{

    /**
     * Hook to modify newsletter config on page duplication
     *
     * @param string $action The action to perform, e.g. 'update'.
     * @param string $table The table affected by action, e.g. 'fe_users'.
     * @param int $uid The uid of the record affected by action.
     * @param array $modifiedFields The modified fields of the record.
     * @return void
     */
    public function processDatamap_postProcessFieldArray(
        string $action,
        string $table,
        int $uid,
        array &$modifiedFields
    ): void {

        if (
            $table === 'pages'
            && isset($modifiedFields['t3_origuid'])
        ) {
            $modifiedFields['tx_rkwnewsletter_include_tstamp'] = 0;
        }

    }

}
