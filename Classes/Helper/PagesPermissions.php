<?php

namespace RKW\RkwNewsletter\Helper;
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
 * PagesPermissions Helper
 *
 * This class is initially using a "pages" element and a permTarget ("user", "group", "everybody"). After construct this class
 * rights can be set or deprived (through boolean setter methods)
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesPermissions
{
    const PAGES_PERM_SHOW = 1;
    const PAGES_PERM_EDIT = 2;
    const PAGES_PERM_DELETE = 4;
    const PAGES_PERM_CREATE = 8;
    const PAGES_PERM_CONTENT_CREATE = 16;

    /**
     * pages
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    protected $pages = null;

    /**
     * permTarget
     *
     * @var string
     */
    protected $permTarget = '';

    /**
     * permsAddedUp
     *
     * @var integer
     */
    protected $permsAddedUp = 0;

    /**
     * permShow
     *
     * @var boolean
     */
    protected $permShow = false;

    /**
     * permEdit
     *
     * @var boolean
     */
    protected $permEdit = false;

    /**
     * permDelete
     *
     * @var boolean
     */
    protected $permDelete = false;

    /**
     * permCreate
     *
     * @var boolean
     */
    protected $permCreate = false;

    /**
     * permContentCreate
     *
     * @var boolean
     */
    protected $permContentCreate = false;

    /**
     * constructor
     * give pages element and the target group, which rights are should edit ("user", "group", "everybody")
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $pages
     * @param string $userGroupOrEverybody ("user", "group" or "everybody")
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function __construct($pages, $userGroupOrEverybody)
    {
        $this->pages = $pages;
        $this->permTarget = $userGroupOrEverybody;

        // get rights from $pages and set it to this class
        $permGetter = 'getPerms' . ucfirst($this->permTarget);
        if (!method_exists($this->pages, $permGetter)) {
            throw new \RKW\RkwNewsletter\Exception("Getter method does not exist!", 1540554503);
            //===
        }
        
        // this intval is not important: Just convert "null" to "0" (code below - function parsePermissions - also works with "null")
        $this->permsAddedUp = intval($this->pages->$permGetter);

        // fill properties
        $this->parsePermissions();
    }

    /**
     * Get the permsAddedUp
     *
     * @return integer
     */
    public function getPermsAddedUp()
    {
        return $this->permsAddedUp;
    }

    /**
     * Sets the permsAddedUp
     *
     * @param boolean $permsAddedUp
     * @return void
     */
    public function setPermsAddedUp($permsAddedUp)
    {
        $this->permsAddedUp = $permsAddedUp;
        $this->parsePermissions();
    }

    /**
     * Sets the permShow
     *
     * @param boolean $permShow
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setPermShow($permShow)
    {
        $this->permShow = $permShow;
        $this->mergeAndSetPermissions();
    }

    /**
     * Sets the permEdit
     *
     * @param boolean $permEdit
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setPermEdit($permEdit)
    {
        $this->permEdit = $permEdit;
        $this->mergeAndSetPermissions();
    }

    /**
     * Sets the permDelete
     *
     * @param boolean $permDelete
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setPermDelete($permDelete)
    {
        $this->permDelete = $permDelete;
        $this->mergeAndSetPermissions();
    }

    /**
     * Sets the permCreate
     *
     * @param boolean $permCreate
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setPermCreate($permCreate)
    {
        $this->permCreate = $permCreate;
        $this->mergeAndSetPermissions();
    }

    /**
     * Sets the permContentCreate
     *
     * @param boolean $permContentCreate
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    public function setPermContentCreate($permContentCreate)
    {
        $this->permContentCreate = $permContentCreate;
        $this->mergeAndSetPermissions();
    }


    /**
     * parsePermissions
     * Calculates with the total count which actions for this page are allowed
     *
     * @return void
     */
    protected function parsePermissions()
    {
        $permsAddedUp = $this->permsAddedUp;

        // 16 - page content edit
        if ($permsAddedUp >= self::PAGES_PERM_CONTENT_CREATE) {
            $this->permContentCreate = true;
            $permsAddedUp -= self::PAGES_PERM_CONTENT_CREATE;
        }
        // 8 - page create
        if ($permsAddedUp >= self::PAGES_PERM_CREATE) {
            $this->permCreate = true;
            $permsAddedUp -= self::PAGES_PERM_CREATE;
        }
        // 4 - page delete
        if ($permsAddedUp >= self::PAGES_PERM_DELETE) {
            $this->permDelete = true;
            $permsAddedUp -= self::PAGES_PERM_DELETE;
        }
        // 2 - page edit
        if ($permsAddedUp >= self::PAGES_PERM_EDIT) {
            $this->permEdit = true;
            $permsAddedUp -= self::PAGES_PERM_EDIT;
        }
        // 1 - page show
        if ($permsAddedUp >= self::PAGES_PERM_SHOW) {
            $this->permShow = true;
        }
    }


    /**
     * mergePermissions
     * Add single values to one value
     *
     * @return void
     */
    protected function mergePermissions()
    {
        // reset permTotalCount and write new below
        $this->permsAddedUp = 0;

        // 1 - page show
        if ($this->permShow) {
            $this->permsAddedUp += self::PAGES_PERM_SHOW;
        }
        // 2 - page edit
        if ($this->permEdit) {
            $this->permsAddedUp += self::PAGES_PERM_EDIT;
        }
        // 4 - page delete
        if ($this->permDelete) {
            $this->permsAddedUp += self::PAGES_PERM_DELETE;
        }
        // 8 - page create
        if ($this->permCreate) {
            $this->permsAddedUp += self::PAGES_PERM_CREATE;
        }
        // 16 - page content edit
        if ($this->permContentCreate) {
            $this->permsAddedUp += self::PAGES_PERM_CONTENT_CREATE;
        }
    }


    /**
     * mergeAndSetPermissions
     * is called by every setter and sets the new value directly to the pages element
     *
     * @return void
     * @throws \RKW\RkwNewsletter\Exception
     */
    protected function mergeAndSetPermissions()
    {
        $this->mergePermissions();
        
        // get rights from $pages and set it to this class
        $permSetter = 'setPerms' . ucfirst($this->permTarget);

        if (!method_exists($this->pages, $permSetter)) {
            throw new \RKW\RkwNewsletter\Exception("Setter method does not exist!", 1540558505);
        }
        
        $this->pages->$permSetter($this->permsAddedUp);
    }
}