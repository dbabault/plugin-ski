<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Form list
 *
 * PHP version 5
 *
 * Copyright © 2017-2018 The Galette Team
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
 *$file = fopen("/home/daniel/fichier.txt", "a");
 *fwrite($file,"\n filter_str2 :   " . $filters->filter_str );
 *fwrite($file, "\n---------------\n");
 *
 */

namespace GaletteSki\Repository;

use Galette\Entity\DynamicFields;

use Analog\Analog;
use Galette\Core\Db;
use Zend\Db\Sql\Expression;
use Galette\Core\Plugins;
use Galette\Entity\Adherent;
use Galette\Repository\Repository;

use GaletteSki\Filters\FormFilter;
use GaletteObjectsLend\Entity\Preferences;

/**
 * Form list
 *
 * @name Form
 * @category  Repository
 * @package   GaletteSki
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */
class Form
{
    const TABLE = "form";
    const PK = "form_id";

    const FILTER_ID = 0;
    const FILTER_BDATE = 1;
    const FILTER_FDATE = 2;
    const FILTER_EDATE = 3;
    const FILTER_NAME = 4;
    const FILTER_STATUS = 5;
    const FILTER_PERIOD= 6;
    const FILTER_DURATION = 6;

    const ALL_FORM = 0;
    const ACTIVE_FORM = 1;
    const INACTIVE_FORM = 2;

    const ORDERBY_FORM = 0;
    const ORDERBY_BDATE = 1;
    const ORDERBY_FDATE = 2;
    const ORDERBY_EDATE = 3;
    const ORDERBY_NAME= 4;
    const ORDERBY_STATUS=5;
    const ORDERBY_PERIOD=6;
    const ORDERBY_DURATION=7;


    private $fields = array(
        'form_id' => 'integer',
        'date_begin' => 'datetime',
        'date_forecast' => 'datetime',
        'date_end' => 'datetime',
        'comment' => 'varchar(200)',
        'parent_id' => 'integer',
        'form_status' => 'enum',
        'period' => 'varchar(20)',
        'duration' => 'varchar(20)'
    );



    private $filters = false;
    private $count = null;
    private $errors = array();
    private $prefs;
    private $plugins;

    /**
     * Default constructor
     *
     * @param Db          $zdb     Database instance
     * @param Plugins     $plugins Plugins instance
     * @param Preferences $lprefs  Lends preferences instance
     * @param FormFilter $filters Filtering
     */
    public function __construct(Db $zdb, Plugins $plugins, Preferences $lprefs, FormFilter $filters = null)
    {
        $this->zdb = $zdb;
        $this->plugins = $plugins;
        $this->prefs = $lprefs;

/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*fwrite($file, "\n 1---------------lib/Repository/Form.php\n");*/

/*fwrite($file, "\n 2---------------lib/Repository/Form.php\n");*/

        $now = date('Y-m-d');
        $this->_date_begin = $now;
        $this->_date_forecast = $now;

        if ($filters === null) {
            $this->filters = new FormFilter();
        } else {
            $this->filters = $filters;
        }
    }

    /**
     * Get Form list
     *
     * @param boolean $as_Form return the results as an array of
     *                          Forms.
     * @param array   $fields     field(s) name(s) to get. Should be a string or
     *                            an array. If null, all fields will be
     *                            returned
     * @param boolean $count      true if we want to count members
     * @param boolean $limit      true if we want records pagination
     *
     * @return Form[]|ResultSet
     */
    public function getFormList(
        $fields = null,
        $count = true,
        $limit = true
    ) {
        try {
/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*fwrite($file, "\n 1---------------lib/Repository/Form.php getFormList \n");*/

/*fwrite($file, "\n 2---------------lib/Repository/Form.php getFormList \n");*/
            $select = $this->buildSelect($fields, $count);
            //add limits to retrieve only relevant rows
            if ($limit === true) {
                $this->filters->setLimit($select);
            }

            $rows = $this->zdb->execute($select);
            $this->filters->query = $this->zdb->query_string;

            $forms = array();

            $forms = $rows;

            return $forms;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot list Forms | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }


    /**
     * Builds the SELECT statement
     *
     * @param array $fields fields list to retrieve
     * @param bool  $count  true if we want to count members, defaults to false
     *
     * @return Select SELECT statement
     */
    private function buildSelect($fields, $count = false)
    {
        global $zdb, $login;
        try {
            $select = $zdb->select(SKI_PREFIX . self::TABLE, 'o');

            $fieldsList = ($fields != null)
                          ? ((!is_array($fields) || count($fields) < 1) ? (array)'*'
                          : $fields) : (array)'*';
            $select->columns($fieldsList);


            if ($this->filters !== false) {
                $this->buildWhereClause($select);
            }
            $this->buildOrderClause($fields);
            $select->order($this->buildOrderClause($fields));

            if ($count) {
                $this->proceedCount($select);
            }

            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for Form | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }
    /**
     * Builds where clause, for filtering on simple list mode
     *
     * @param Select $select Original select
     *
     * @return string SQL WHERE clause
     */
    private function buildWhereClause($select)
    {
        global $login;

        try {
            if ($this->filters->filter_str != '') {
                $token = $this->zdb->platform->quoteValue(
                    '%' . strtolower($this->filters->filter_str) . '%'
                );
                switch ($this->filters->field_filter) {
                    case self::SKIFILTER_NAME:
                        $select->where(
                            'o.parent_id LIKE ' . $token
                        );
                        break;
                    case self::SKIFILTER_BDATE:
                            $select->where(
                                'o.date_begin LIKE ' . $token
                            );
                        break;
                    case self::SKIFILTER_FDATE:
                            $select->where(
                                'o.date_forecast LIKE ' . $token
                            );
                        break;
                    case self::SKIFILTER_EDATE:
                            $select->where(
                                'o.date_end LIKE ' . $token
                            );
                        break;
                    case self::SKIFILTER_STATUS:
                        $select->where(
                            'LOWER(form_status) LIKE ' . $token
                        );
                        break;
                    case self::SKIFILTER_ID:
                        $select->where->equalTo('o.' . Form::PK, $this->filters->filter_str);
                        break;
                }
            }
        } catch (\Exception $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }



    /**
     * Builds the order clause
     *
     * @param array $fields Fields list to ensure ORDER clause
     *                      references selected fields. Optionnal.
     *
     * @return string SQL ORDER clause
     */
    private function buildOrderClause($fields = null)
    {
/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*fwrite($file, "\n 1---------------lib/Repository/Form.php buildOrderClause \n");*/
        $order = array();
        switch ($this->filters->orderby) {
            case self::ORDERBY_FORM:
                if ($this->canOrderBy('form_id', $fields)) {
                    $order[] = 'form_id ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_NAME:
                if ($this->canOrderBy('parent_id', $fields)) {
                    $order[] = 'parent_id ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_BDATE:
                if ($this->canOrderBy('date_begin', $fields)) {
                    $order[] = 'date_begin ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_FDATE:
                if ($this->canOrderBy('date_forecast', $fields)) {
                    $order[] = 'date_forecast ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_EDATE:
                if ($this->canOrderBy('date_end', $fields)) {
                    $order[] = 'date_end ' . $this->filters->getDirection();
                }
                break;

            case self::ORDERBY_STATUS:
                if ($this->canOrderBy('form_status', $fields)) {
                    $order[] = 'form_status ' . $this->filters->getDirection();
                }
                break;

            case self::ORDERBY_PERIOD:
                if ($this->canOrderBy('period', $fields)) {
                    $order[] = 'period ' . $this->filters->getDirection();
                }
                break;

            case self::ORDERBY_DURATION:
                if ($this->canOrderBy('period', $fields)) {
                    $order[] = 'duration ' . $this->filters->getDirection();
                }
                break;
        }

        return $order;
    }

    /**
     * Count members from the query
     *
     * @param Select $select Original select
     *
     * @return void
     */
    private function proceedCount($select)
    {
        global $zdb;
        try {
            $countSelect = clone $select;
            $countSelect->reset($countSelect::COLUMNS);
            $countSelect->reset($countSelect::ORDER);
            $countSelect->reset($countSelect::HAVING);
            $countSelect->reset($countSelect::JOINS);
            $countSelect->columns(
                array(
                    'count' => new Expression('count(o.' . self::PK . ')')
                )
            );

            $have = $select->having;
            if ($have->count() > 0) {
                foreach ($have->getPredicates() as $h) {
                    $countSelect->where($h);
                }
            }

            $joins = $select->getRawState($select::JOINS);
            foreach ($joins as $join) {
                $countSelect->join(
                    $join['name'],
                    $join['on'],
                    [],
                    $join['type']
                );
            }

            $results = $zdb->execute($countSelect);

            $this->count = $results->current()->count;
            if (isset($this->filters) && $this->count > 0) {
                $this->filters->setCounter($this->count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count Form | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Is field allowed to order? it shoulsd be present in
     * provided fields list (those that are SELECT'ed).
     *
     * @param string $field_name Field name to order by
     * @param array  $fields     SELECTE'ed fields
     *
     * @return boolean
     */
    private function canOrderBy($field_name, $fields)
    {
        if (!is_array($fields)) {
            return true;
        } elseif (in_array($field_name, $fields)) {
            return true;
        } else {
            Analog::log(
                'Trying to order by ' . $field_name  . ' while it is not in ' .
                'selected fields.',
                Analog::WARNING
            );
            return false;
        }
    }

    /*
     *
     *
     *
    * @return boolean True si OK, False si une erreur SQL est survenue
    */
    public function changeFormStatus($form_id, $form_status)
    {
      /*$file = fopen("/home/daniel/fichier.txt", "a");*/
      /*fwrite($file, "\n 1---------------lib/Repository/Form.php changeFormStatus \n");*/

      /*fwrite($file, "\n 2---------------lib/Repository/Form.php changeFormStatus \n");*/
    }

    /*
    *
    *
    * @return boolean True si OK, False si une erreur SQL est survenue
    */
    public static function newForm($f)
    {
        $success = false;

        global $zdb;

        try {
          /*$file = fopen("/home/daniel/fichier.txt", "a");*/
          /*fwrite($file, "\n 1---------------lib/Repository/Form.php newForm \n");*/

          /*fwrite($file, "\n 2---------------lib/Repository/Form.php newForm \n");*/
            $zdb->connection->beginTransaction();


            $values = array();



            $values['form_id']=$f->form_id;
            $values['date_begin']=$f->date_begin;
            $values['date_forecast']=$f->date_forecast;
            $values['comment']=$f->comment;
            $values['parent_id']=$f->parent_id;
            $values['form_status']=$f->form_status;
            $values['period']=$f->period;
            $values['duration']=$f->duration;


            unset($values[self::PK]);
            $insert = $zdb->insert(SKI_PREFIX . self::TABLE)
                        ->values($values);

            $result = $zdb->execute($insert);

            if ($result->count() > 0) {
                Analog::log(
                    'New Form #' . $f->form_id,
                    Analog::DEBUG
                );
            } else {
                throw new \Exception(_T("Form has not been added", "ski"));
            }
            $zdb->connection->commit();


            return true;
        } catch (\Exception $e) {
            $zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
        fclose($file);
    }

    public static function storeForm($f)
    {
        global $zdb;

        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------lib/Repository/Form.php storeForm \n");


        try {
            $zdb->connection->beginTransaction();
            $values = array();
            fwrite($file, "\n 2---------------lib/Repository/Form.php storeForm \n");
            $values['form_status']=$f->form_status;
            unset($values[self::PK]);
            fwrite($file, "\n 3-----$f->form_id-----$f->form_status-----lib/Repository/Form.php storeForm \n");


            $update = $zdb->update(SKI_PREFIX .self::TABLE);
            $update->set($values);
            $update->where(
                self::PK . '=' . $f->form_id
            );
            $zdb->execute($update);
            fwrite($file, "\n 4---------------lib/Repository/Form.php storeForm \n");
            $zdb->connection->commit();
            return true;
        } catch (\Exception $e) {
            $zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Get date
     *
     * @param string  $prop      Property to use
     * @param boolean $formatted Return date formatted, raw if false
     *
     * @return string
     */
    private function getDate($prop, $formatted = true)
    {
        if ($formatted === true) {
            $date = new \DateTime($this->$prop);
            return $date->format(__("Y-m-d"));
        } else {
            return $this->$prop;
        }
    }

    /**
     * Get begin date
     *
     * @param boolean $formatted Return date formatted, raw if false
     *
     * @return string
     */
    public function getBeginDate($formatted = true)
    {
        return $this->getDate('_date_begin', $formatted);
    }
    public function getEndDate($formatted = true)
    {
        return $this->getDate('_date_forecast', $formatted);
    }
}
