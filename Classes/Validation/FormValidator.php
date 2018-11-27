<?php

namespace RKW\RkwNewsletter\Validation;

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
 * Class FormValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FormValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * booleanValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator
     * @inject
     */
    protected $booleanValidator;

    /**
     * emailAddressValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator
     * @inject
     */
    protected $emailAddressValidator;


    /**
     * validation
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public function isValid($frontendUser)
    {

        $isValid = true;

        // get required fields of user
        $settings = Common::getTyposcriptConfiguration('Rkwnewsletter');
        $requiredFields = array('email');
        if ($settings['requiredFieldsSubscription']) {
            $requiredFields = array_merge($requiredFields, \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $settings['requiredFieldsSubscription'], true));
        }


        // check valid email
        if (in_array('email', $requiredFields)) {

            if (!\RKW\RkwRegistration\Tools\Registration::validEmail($frontendUser->getEmail())) {

                $this->result->forProperty('email')->addError(
                    new \TYPO3\CMS\Extbase\Error\Error(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
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
                    //===
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
                        new \TYPO3\CMS\Extbase\Error\Error(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'validator.field_required',
                                'rkw_newsletter',
                                array('field' =>
                                          \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                              'tx_rkwnewsletter_domain_model_frontenduser.' . \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($requiredField),
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
        //====

    }


}

