<?php

namespace RKW\RkwNewsletter\Validation\TCA;
use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/*

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
class NewsletterTeaserLengthEvaluation
{


    /**
     * JavaScript code for client side validation/evaluation
     *
     * @return string JavaScript code for client side validation/evaluation
     */
    public function returnFieldJS() {
        return 'return value;';
    }

    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not. Must be passed by reference and changed if needed.
     * @return string Evaluated field value
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function evaluateFieldValue($value, $is_in, &$set) {

        $settings = $this->getSettings();

        if (
            ($settings['minTeaserLength'])
            || ($settings['maxTeaserLength'])
        ) {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $flashMessageService = $objectManager->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();

            $strLength = strlen(strip_tags($value));
            if (
                ($settings['minTeaserLength'])
                && ($strLength > 5)
                && ($strLength < intval($settings['minTeaserLength']))
            ) {

                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.message.tooShort',
                        'rkw_newsletter',
                        [$settings['minTeaserLength'], $strLength]
                    ),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.header.tooShort',
                        'rkw_newsletter',
                        [$settings['minTeaserLength'], $strLength]
                    ),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
                );
                $messageQueue->addMessage($message);

            }


            if (
                ($settings['maxTeaserLength'])
                && ($strLength  > intval($settings['maxTeaserLength']))
            ) {

                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.message.tooLong',
                        'rkw_newsletter',
                        [$settings['maxTeaserLength'], $strLength]
                    ),
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.header.tooLong',
                        'rkw_newsletter',
                        [$settings['maxTeaserLength'], $strLength]
                    ),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
                );
                $messageQueue->addMessage($message);

            }


        }
        return $value;
        //===
    }

    /**
     * Server-side validation/evaluation on opening the record
     *
     * @param array $parameters Array with key 'value' containing the field value from the database
     * @return string Evaluated field value
     */
    public function deevaluateFieldValue(array $parameters) {


        return $parameters['value'];
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwnewsletter', $which);
        //===
    }


}