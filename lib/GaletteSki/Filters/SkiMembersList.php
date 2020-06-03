<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * SkiMembers list filters and paginator
 *
 * PHP version 5
 *
 * Copyright © 2009-2014 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Filters
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     march, 3rd 2009
 */

namespace GaletteSki\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use Galette\Entity\Group;
use GaletteSki\Repository\SkiMembers;
use GaletteObjectsLend\Entity\Preferences;

/**
 * SkiMembers list filters and paginator
 *
 * @name      SkiMembersList
 * @category  Filters
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class SkiMembersList extends Pagination
{
    //filters
    private $_filter_str;
    private $_field_filter;
    private $_membership_filter;
    private $_filter_account;
    private $_email_filter;
    private $_group_filter;

    private $_selected;
    private $_unreachable;

    protected $query;

    protected $memberslist_fields = array(
        'filter_str',
        'field_filter',
        'membership_filter',
        'filter_account',
        'email_filter',
        'group_filter',
        'selected',
        'unreachable',
        'query'
    );

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->reinit();
    }

    /**
     * Returns the field we want to default set order to
     *
     * @return string field name
     */
    protected function getDefaultOrder()
    {
        return 'nom_adh';
    }

    /**
     * Reinit default parameters
     *
     * @return void
     */
    public function reinit()
    {
        global $preferences;

        parent::reinit();
        $this->_filter_str = null;
        $this->_field_filter = null;
        $this->_membership_filter = null;
        $this->_filter_account = $preferences->pref_filter_account;
        $this->_email_filter = SkiMembers::FILTER_DC_EMAIL;
        $this->_group_filter = null;
        $this->_selected = array();
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return object the called property
     */
    public function __get($name)
    {

        Analog::log(
            '[SkiMembersList] Getting property `' . $name . '`',
            Analog::DEBUG
        );

        if (in_array($name, $this->pagination_fields)) {
            return parent::__get($name);
        } else {
            if (in_array($name, $this->memberslist_fields)) {
                if ($name === 'query') {
                    return $this->$name;
                } else {
                    $name = '_' . $name;
                    return $this->$name;
                }
            } else {
                Analog::log(
                    '[SkiMembersList] Unable to get proprety `' .$name . '`',
                    Analog::WARNING
                );
            }
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param object $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->pagination_fields)) {
            parent::__set($name, $value);
        } else {
            Analog::log(
                '[SkiMembersList] Setting property `' . $name . '`',
                Analog::DEBUG
            );

            switch ($name) {
                case 'selected':
                case 'unreachable':
                    if (is_array($value)) {
                        $name = '_' . $name;
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[SkiMembersList] Value for property `' . $name .
                            '` should be an array (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'filter_str':
                    $name = '_' . $name;
                    $this->$name = $value;
                    break;
                case 'field_filter':
                case 'membership_filter':
                case 'filter_account':
                    if (is_numeric($value)) {
                        $name = '_' . $name;
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[SkiMembersList] Value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'email_filter':
                    switch ($value) {
                        case SkiMembers::FILTER_DC_EMAIL:
                        case SkiMembers::FILTER_W_EMAIL:
                        case SkiMembers::FILTER_WO_EMAIL:
                            $this->_email_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[SkiMembersList] Value for email filter should be either ' .
                                SkiMembers::FILTER_DC_EMAIL . ', ' .
                                SkiMembers::FILTER_W_EMAIL . ' or ' .
                                SkiMembers::FILTER_WO_EMAIL . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'group_filter':
                    if (is_numeric($value) && $value > 0) {
                        //check group existence
                        $g = new Group();
                        $res = $g->load($value);
                        if ($res === true) {
                            $this->_group_filter = $value;
                        } else {
                            Analog::log(
                                'Group #' . $value . ' does not exists!',
                                Analog::WARNING
                            );
                        }
                    } elseif ($value !== null && $value !== '0') {
                        Analog::log(
                            '[SkiMembersList] Value for group filter should be an '
                            .'integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    Analog::log(
                        '[SkiMembersList] Unable to set proprety `' . $name . '`',
                        Analog::WARNING
                    );
                    break;
            }
        }
    }

    /**
     * Set commons filters for templates
     *
     * @param Preferences $prefs Preferences instance
     * @param Smarty      $view  Smarty template reference
     *
     * @return void
     */
    public function setViewCommonsFilters($prefs, \Smarty $view)
    {
        $filter_options = array(
            SkiMembers::FILTER_NAME            => _T("Name"),
            SkiMembers::FILTER_COMPANY_NAME    => _T("Company name"),
            SkiMembers::FILTER_ADDRESS         => _T("Address"),
            SkiMembers::FILTER_MAIL            => _T("Email,URL,IM"),
        SkiMembers::FILTER_JOB             => _T("Job"),
        SkiMembers::FILTER_PARENT    => _T("Parent"),
            SkiMembers::FILTER_INFOS           => _T("Infos")
        );

        if ($prefs->pref_show_id) {
            $filter_options[SkiMembers::FILTER_NUMBER] = _T("Member number");
        }

        $view->assign(
            'field_filter_options',
            $filter_options
        );

        $view->assign(
            'membership_filter_options',
            array(
                SkiMembers::MEMBERSHIP_ALL     => _T("All members"),
                SkiMembers::MEMBERSHIP_UP2DATE => _T("Up to date members"),
                SkiMembers::MEMBERSHIP_NEARLY  => _T("Close expiries"),
                SkiMembers::MEMBERSHIP_LATE    => _T("Latecomers"),
                SkiMembers::MEMBERSHIP_NEVER   => _T("Never contributed"),
                SkiMembers::MEMBERSHIP_STAFF   => _T("Staff members"),
                SkiMembers::MEMBERSHIP_ADMIN   => _T("Administrators"),
                SkiMembers::MEMBERSHIP_NONE    => _T("Non members")
            )
        );

        $view->assign(
            'filter_accounts_options',
            array(
                SkiMembers::ALL_ACCOUNTS       => _T("All accounts"),
                SkiMembers::ACTIVE_ACCOUNT     => _T("Active accounts"),
                SkiMembers::INACTIVE_ACCOUNT   => _T("Inactive accounts")
            )
        );
    }
}
