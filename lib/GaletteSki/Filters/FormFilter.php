<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Form list filters and paginator
 *
 * PHP version 5
 *
 * Copyright © 2017 The Galette Team
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
 * @category  Plugins
 * @package   Ski
 * @author    Daniel BABAULT <daniel@babault.net>
 * @copyright 2020  Daniel BABAULT
 * Copyright © 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   1.0-dev'
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.9.3
 *
 *
 */

namespace GaletteSki\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use GaletteSki\Repository\Form;
use GaletteObjectsLend\Entity\Preferences;

/**
 * Form list filters and paginator
 *
 * @name      FormFilter
 * @category  Filters
 * @package   GaletteSki
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class FormFilter extends Pagination
{
    //filters
    private $filter_str;
    private $category_filter;
    private $active_filter;
    private $field_filter;
    private $selected;
//an empty contributions dynamic field criteria to begin
    private $_contrib_dynamic = array();
    private $_adh_dynamic = array();

    protected $query;

    protected $formlist_fields = array(
        'filter_str',
        'category_filter',
        'active_filter',
        'field_filter',
        'selected',
        'query'
    );
    const FILTER_ID = 0;
    const FILTER_BDATE = 1;
    const FILTER_FDATE = 2;
    const FILTER_EDATE = 3;
    const FILTER_NAME = 4;
    const FILTER_STATUS = 5;
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
        return 'name';
    }

    /**
     * Reinit default parameters
     *
     * @return void
     */
    public function reinit()
    {
        parent::reinit();
        $this->filter_str = null;
        $this->category_filter = null;
        $this->active_filter = null;
        $this->field_filter = null;
        $this->selected = array();
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return form the called property
     */
    public function __get($name)
    {
        if (in_array($name, $this->pagination_fields)) {
            return parent::__get($name);
        } else {
            if (in_array($name, $this->formlist_fields)) {
                return $this->$name;
            } else {
                Analog::log(
                    '[FormFilter] Unable to get proprety `' .$name . '`',
                    Analog::WARNING
                );
            }
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param form $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->pagination_fields)) {
            parent::__set($name, $value);
        } else {
            Analog::log(
                '[FormFilter] Setting property `' . $name . '`',
                Analog::DEBUG
            );
            switch ($name) {
                case 'selected':
                    if (is_array($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[FormFilter] Value for property `' . $name .
                            '` should be an array (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'filter_str':
                    $this->$name = $value;
                    break;
                case 'category_filter':
                    if (is_numeric($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[FormFilter] Value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    } else {
                        $this->$name = null;
                    }
                    break;
                case 'active_filter':
                    switch ($value) {
                        case Form::ALL_FORM:
                        case Form::ACTIVE_FORM:
                        case Form::INACTIVE_FORM:
                            $this->active_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[FormFilter] Value for active filter should be either ' .
                                FormSki::ACTIVE . ' or ' .
                                FormSki::INACTIVE . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'field_filter':
                    if (is_numeric($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[FormFilter] Value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    Analog::log(
                        '[FormFilter] Unable to set proprety `' . $name . '`',
                        Analog::WARNING
                    );
                    break;
            }
        }
    }

    /**
     * Add SQL limit
     *
     * @param Select $select Original select
     *
     * @return <type>
     */
    public function setLimit($select)
    {
        return $this->setLimits($select);
    }

    /**
     * Set counter
     *
     * @param int $c Count
     *
     * @return void
     */
    public function setCounter($c)
    {
        $this->counter = (int)$c;
        $this->countPages();
    }

    /**
     * Set commons filters for templates
     *
     * @param SkiPreferences $prefs Preferences instance
     * @param Smarty           $view  Smarty template reference
     *
     * @return void
     */
    public function setViewCommonsFilters($prefs, \Smarty $view)
    {
        //$prefs = $prefs->getPreferences();

        $options = [
            FormFilter::FILTER_NAME    => _T("Name", "ski"),
            FormFilter::FILTER_STATUS    => _T("Status", "ski"),
            FormFilter::FILTER_BDATE  => _T("Begin Date", "ski"),
            FormFilter::FILTER_FDATE  => _T("Forecast Date", "ski"),
            FormFilter::FILTER_EDATE  => _T("End  Date", "ski"),
            FormFilter::FILTER_ID      => _T("Id", "ski"),
        ];

        $view->assign(
            'field_filter_options',
            $options
        );
    }
}
