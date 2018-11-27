<?php

namespace RKW\RkwNewsletter\ViewHelpers;

use \RKW\RkwBasics\Helper\Common;

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
 * IsMandatoryFieldViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsMandatoryFieldViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $fieldName
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render($fieldName)
    {

        $settings = Common::getTyposcriptConfiguration('Rkwnewsletter');
        $requiredFields = array('email');
        if ($settings['requiredFieldsSubscription']) {
            $requiredFields = array_merge($requiredFields, \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $settings['requiredFieldsSubscription'], true));
        }

        return in_array($fieldName, $requiredFields);
        //===

    }


}