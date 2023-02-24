<?php

namespace RKW\RkwNewsletter\Validation;

use Madj2k\CoreExtended\Utility\GeneralUtility;
use RKW\RkwNewsletter\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
 * Class FormValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FormValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    /**
     * validation
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($frontendUser)
    {
        $isValid = true;

        // get required fields of user
        $settings = GeneralUtility::getTypoScriptConfiguration('Rkwnewsletter');
        $requiredFields = array('email');
        if ($settings['requiredFieldsSubscription']) {
            $requiredFields = array_merge($requiredFields, \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $settings['requiredFieldsSubscription'], true));
        }

        // check valid email
        if (in_array('email', $requiredFields)) {

            if (! FrontendUserUtility::isEmailValid($frontendUser->getEmail())) {

                $this->result->forProperty('email')->addError(
                    new Error(
                        LocalizationUtility::translate(
                            'validator.email_invalid',
                            'rkw_newsletter'
                        ), 1537173349
                    )
                );
                $isValid = false;
            }
        }

        // check all properties on required
        foreach ($requiredFields as $requiredField) {

            $getter = 'get' . ucFirst($requiredField);
            if (method_exists($frontendUser, $getter)) {

                // has already been checked!
                if ($requiredField == 'email') {
                    continue;
                }

                if (
                    (
                        ($requiredField != 'txRkwregistrationGender')
                        && (empty($frontendUser->$getter()))
                    )
                    || (
                        ($requiredField == 'txRkwregistrationGender')
                        && ($frontendUser->$getter() == 99)
                    )
                ) {

                    $this->result->forProperty($requiredField)->addError(
                        new Error(
                            LocalizationUtility::translate(
                                'validator.field_required',
                                'rkw_newsletter',
                                array('field' =>
                                    LocalizationUtility::translate(
                                      'tx_rkwnewsletter_domain_model_frontenduser.' .
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($requiredField),
                                      'rkw_newsletter'
                                    ),
                                )
                            ), 1537173350
                        )
                    );
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }
}

