<?php
namespace RKW\RkwNewsletter\Validation;

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

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * EmailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EmailValidator implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ObjectManager $objectManager;


    /**
     * mail
     *
     * @param string $emailAddress
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    public function email(string $emailAddress): Result
    {
        /** @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validatorResolver */
        $validatorResolver = $this->objectManager->get(ValidatorResolver::class);
        $conjunctionValidator = $validatorResolver->createValidator('Conjunction');
        $conjunctionValidator->addValidator($validatorResolver->createValidator('EmailAddress'));
        return $conjunctionValidator->validate($emailAddress);
    }


}
