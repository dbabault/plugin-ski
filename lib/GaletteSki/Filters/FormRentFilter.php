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
use GaletteSki\Repository\FormRent;
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

class FormRentFilter extends Pagination
{
    //filters
    private $filter_str;
    private $category_filter;
    private $active_filter;
    private $field_filter;
    private $selected;
    private $begin;
    private $forecast;

    protected $query;

    protected $formlist_fields = array(
        'filter_str',
        'category_filter',
        'active_filter',
        'field_filter',
        'selected',
        'begin',
        'forecast',
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
        $this->begin = null;
        $this->forecast = null;
        $this->selected = array();
    }

    /**
     * global getter method
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
                analog::log(
                    '[formfilter] unable to get proprety `' . $name . '`',
                    analog::WARNING
                );
            }
        }
    }

    /**
     * global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param form $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->form_list_fields)) {
            parent::__set($name, $value);
        } else {
            analog::log(
                '[formrentfilter] setting property `' . $name . '`',
                analog::DEBUG
            );
            switch ($name) {
                case 'selected':
                    if (is_array($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        analog::log(
                            '[formrentfilter] value for property `' . $name .
                            '` should be an array (' . gettype($value) . ' given)',
                            analog::WARNING
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
                        analog::log(
                            '[formfilter] value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            analog::WARNING
                        );
                    } else {
                        $this->$name = null;
                    }
                    break;
                case 'active_filter':
                    switch ($value) {
                        case formrent::all_form:
                        case formrent::active_form:
                        case formrent::inactive_form:
                            $this->active_filter = $value;
                            break;
                        default:
                            analog::log(
                                '[formfilter] value for active filter should be either ' .
                                formrent::active . ' or ' .
                                formrent::inactive . ' (' . $value . ' given)',
                                analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'field_filter':
                    if (is_numeric($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        analog::log(
                            '[formfilter] value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            analog::WARNING
                        );
                    }
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                case 'begin':
                    $this->$name = $value;
                    break;
                case 'forecast':
                    $this->$name = $value;
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    analog::log(
                        '[formfilter] unable to set proprety `' . $name . '`',
                        analog::WARNING
                    );
                    break;
            }
        }
    }

    /**
     * add sql limit
     *
     * @param select $select original select
     *
     * @return <type>
     */
    public function setlimit($select)
    {
        return $this->setlimits($select);
    }

    /**
     * set counter
     *
     * @param int $c count
     *
     * @return void
     */
    public function setcounter($c)
    {
        $this->counter = (int)$c;
        $this->countpages();
    }

    /**
     * set commons filters for templates
     *
     * @param skipreferences $prefs preferences instance
     * @param smarty           $view  smarty template reference
     *
     * @return void
     */
    public function setviewcommonsfilters($prefs, \smarty $view)
    {
        //$prefs = $prefs->getpreferences();

        $options = [
            formfilter::fILTER_ID_ADH    => _T("Id_adh", "ski"),
            FormFilter::FILTER_CATEGORY_ID    => _T("Category", "ski"),
            FormFilter::FILTER_BDATE  => _T("Begin Date", "ski"),
            FormFilter::FILTER_FDATE  => _T("Forecast Date", "ski"),
            FormFilter::FILTER_EDATE  => _T("End  Date", "ski"),
            FormFilter::FILTER_B_F_DATE  => _T("Begin End  Date", "ski"),
            FormFilter::FILTER_FORM_ID      => _T("Form Id", "ski"),
            FormFilter::FILTER_OBJECT_ID      => _T("Object Id", "ski"),
        ];

        $view->assign(
            'field_filter_options',
            $options
        );
    }
}
