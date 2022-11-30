<?php

namespace RKW\RkwNewsletter\ViewHelpers;

use RKW\RkwBasics\Utility\GeneralUtility as Common;

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

use RKW\RkwBasics\Utility\GeneralUtility;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsMandatoryFieldViewHelper
 *
 * returns true, if given field is mandatory
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo rework and write tests
 */
class IsMandatoryFieldViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string', 'FieldName to check for', true);
    }

    /**
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render(): bool
    {
        $fieldName = $this->arguments['fieldName'];

        $settings = GeneralUtility::getTyposcriptConfiguration('Rkwnewsletter');
        $requiredFields = array('email');
        if ($settings['requiredFieldsSubscription']) {
            $requiredFields = array_merge(
                $requiredFields,
                GeneralUtility::trimExplode(
                    ',',
                    $settings['requiredFieldsSubscription'],
                    true
                )
            );
        }

        return in_array($fieldName, $requiredFields);
    }


}
