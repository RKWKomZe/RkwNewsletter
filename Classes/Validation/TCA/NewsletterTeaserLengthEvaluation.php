<?php
namespace RKW\RkwNewsletter\Validation\TCA;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class FormValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
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
    public function returnFieldJS(): string
    {
        return 'return value;';
    }


    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $isIn The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not. Must be passed by reference and changed if needed.
     * @return string Evaluated field value
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function evaluateFieldValue(string $value, string $isIn = null, bool &$set = false): string
    {

        $settings = $this->getSettings();
        if (
            ($settings['minTeaserLength'])
            || ($settings['maxTeaserLength'])
        ) {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $flashMessageService = $objectManager->get(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();

            $strLength = strlen(strip_tags($value));
            if (
                ($settings['minTeaserLength'])
                && ($strLength > 5)
                && ($strLength < intval($settings['minTeaserLength']))
            ) {

                $message = GeneralUtility::makeInstance(FlashMessage::class,
                    LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.message.tooShort',
                        'rkw_newsletter',
                        [$settings['minTeaserLength'], $strLength]
                    ),
                    LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.header.tooShort',
                        'rkw_newsletter',
                        [$settings['minTeaserLength'], $strLength]
                    ),
                    FlashMessage::INFO
                );
                $messageQueue->addMessage($message);
            }

            if (
                ($settings['maxTeaserLength'])
                && ($strLength  > intval($settings['maxTeaserLength']))
            ) {

                $message = GeneralUtility::makeInstance(FlashMessage::class,
                    LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.message.tooLong',
                        'rkw_newsletter',
                        [$settings['maxTeaserLength'], $strLength]
                    ),
                    LocalizationUtility::translate(
                        'newsletterTeaserLengthEvaluation.header.tooLong',
                        'rkw_newsletter',
                        [$settings['maxTeaserLength'], $strLength]
                    ),
                    FlashMessage::INFO
                );
                $messageQueue->addMessage($message);

            }
        }

        return $value;
    }


    /**
     * Server-side validation/evaluation on opening the record
     *
     * @param array $parameters Array with key 'value' containing the field value from the database
     * @return string Evaluated field value
     */
    public function deevaluateFieldValue(array $parameters): string
    {
        return $parameters['value'];
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('Rkwnewsletter', $which);
    }


}
