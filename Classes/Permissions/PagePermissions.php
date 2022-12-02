<?php
namespace RKW\RkwNewsletter\Permissions;

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
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Status\PageStatus;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * PagePermissions
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagePermissions
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pagesRepository;


    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;


    /**
     * setPermissions for page defined by given issue and topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param array $settings
     * @return bool
     * @throws \RKW\RkwNewsletter\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function setPermissions (Pages $page, array $settings = []): bool
    {

        if (! $settings) {
            $settings = $this->getPermissionSettings();
        }

        $userGroupIdNames = [
            'userId',
            'groupId',
        ];

        $permissionNames = [
            'user',
            'group',
            'everybody',
        ];

        $update = false;
        $stage = PageStatus::getStage($page);

        $properties = array_merge( $userGroupIdNames, $permissionNames);
        foreach ($properties as $propertyName) {

            if (
                (isset($settings[$stage]))
                && (isset($settings[$stage][$propertyName]))
                && ($permission = $settings[$stage][$propertyName])
            ) {

                // check for valid permission-values - but not the user- and group-id!
                if (
                    (! in_array($propertyName, $userGroupIdNames))
                    && (! self::validatePermission($permission))
                ) {
                    continue;
                }

                $setter = 'setPerms' . ucfirst($propertyName);
                $page->$setter($permission);
                $update = true;
            }
        }

        if ($update) {
            $this->pagesRepository->update($page);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Updated page permissions for page id=%s to values for "%s".',
                    $page->getUid(),
                    $stage
                )
            );
        }

        return $update;
    }


    /**
     * Validate the given permission value
     *
     * @param int $permission
     * @return bool
     */
    public function validatePermission (int $permission): bool
    {
        if (
            ($permission < Permission::NOTHING)
            || ($permission > Permission::ALL)
        ) {
            return false;
        }

        return true;
    }


    /**
     * Returns permission-settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getPermissionSettings(): array
    {
        $settings = GeneralUtility::getTypoScriptConfiguration(
            'Rkwnewsletter',
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );

        return $settings['pages']['permissions'] ?: [];
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
