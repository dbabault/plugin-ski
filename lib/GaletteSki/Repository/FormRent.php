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
 use Laminas\Db\Sql\Expression;
 use Galette\Core\Plugins;
 use Galette\Entity\Adherent;
 use Galette\Repository\Repository;

 use GaletteSki\Filters\FormFilter;
 use GaletteObjectsLend\Entity\Preferences;

/**
 * FormRent list
 *
 * @name FormRent
 * @category  Repository
 * @package   GaletteSki
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class FormRent
{
    const TABLE = 'form_rent';
    const PK = 'form_rent_id';

    private $fields = array(
        'form_rent_id' => 'integer',
        'form_id' => 'integer',
        'category_id' => 'integer',
        'object_id' => 'integer',
        'date_begin' => 'datetime',
        'date_forecast' => 'datetime',
        'date_end' => 'datetime'
    );


    const ALL_FORM = 0;
    const ACTIVE_FORM = 1;
    const INACTIVE_FORM = 2;

    const FILTER_NAME = 0;
    const FILTER_SERIAL = 1;
    const FILTER_DIM = 2;
    const FILTER_ID = 3;

    const ORDERBY_FORM = 0;
    const ORDERBY_BDATE = 1;
    const ORDERBY_FDATE = 2;


    private $count = null;
    private $errors = array();
    private $plugins;

    /**
     * Default constructor
     *
     * @param Db          $zdb     Database instance
     * @param Plugins     $plugins Plugins instance
     */
    public function __construct(Db $zdb, Plugins $plugins, Preferences $lprefs, FormFilter $filters = null)
    {
        $this->zdb = $zdb;
        $this->plugins = $plugins;

/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*fwrite($file, "\n 1---------------lib/Repository/FormRent.php\n");*/

/*fwrite($file, "\n 2---------------lib/Repository/FormRent.php\n");*/
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
     *                            Object object.
     * @param array   $fields     field(s) name(s) to get. Should be a string or
     *                            an array. If null, all fields will be
     *                            returned
     * @param boolean $count      true if we want to count members
     *
     * @return Form[]|ResultSet
     */
    public function getFormRentList(
        $fields = null,
        $count = true,
        $limit = false
    ) {
/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*/*fwrite($file, "\n 1---------------lib/Repository/FormRent.php getFormRentList \n");*/

        try {
/*fwrite($file, "\n getFormRentList ==> 1");*/
            $select = $this->buildSelect($fields, $count);
/*fwrite($file, "\n getFormRentList ==> 2");*/
            if ($limit === true) {
                $this->filters->setLimit($select);
/*fwrite($file, "\n getFormRentList ==> 21");*/
            }
/*fwrite($file, "\n getFormRentList ==> 3");*/
            $rows = $this->zdb->execute($select);
/*fwrite($file, "\n getFormRentList ==> 4");*/
            $FormRent = array();
/*fwrite($file, "\n getFormRentList ==> 5");*/
            $FormRent = $rows;
/*fwrite($file, "\n getFormRentList ==> 6");*/

/*fwrite($file, "\n 2---------------lib/Repository/FormRent.php getFormRentList \n");*/
            return $FormRent;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot list FormRent | ' . $e->getMessage(),
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
/*$file = fopen("/home/daniel/fichier.txt", "a");*/
/*fwrite($file, "\n 1---------------lib/Repository/FormRent.php buildSelect \n");*/
            $select = $zdb->select(SKI_PREFIX . self::TABLE, 'o');
/*fwrite($file, "\n buildSelect ==>2 \n");*/
            $fieldsList = ($fields != null)
                          ? ((!is_array($fields) || count($fields) < 1) ? (array)'*'
                          : $fields) : (array)'*';
/*fwrite($file, "\n buildSelect ==>3 \n");*/
            $select->columns($fieldsList);
/*fwrite($file, "\n buildSelect ==>4 \n");*/

            if ($this->filters !== false) {
/*fwrite($file, "\n 41 \n");*/
                $this->buildWhereClause($select);
            }
/*fwrite($file, "\n buildSelect ==>5 \n");*/
            $this->buildOrderClause($fields);
            $select->order($this->buildOrderClause($fields));
/*fwrite($file, "\n buildSelect ==>6 \n");*/
            if ($count) {
                $this->proceedCount($select);
            }
/*fwrite($file, "\n 2---------------lib/Repository/FormRent.php buildSelect \n");*/
            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for Form | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /*
    *
    *
    * @return boolean True si OK, False si une erreur SQL est survenue
    */
    public static function newFormrent($f)
    {
        $success = false;
        global $zdb;

        try {
              /*$file = fopen("/home/daniel/fichier.txt", "a");*/
      /*fwrite($file, "\n 1---------------lib/Repository/FormRent.php newFormrent \n");*/
              $zdb->connection->beginTransaction();
              $values = array();
              $values['form_id']=$f->form_id;
              $values['id_adh']=$f->id_adh;
              $values['category_id']=$f->category_id;
              $values['object_id']=$f->object_id;
              $values['date_begin']=$f->date_begin;
              $values['date_forecast']=$f->date_forecast;
              $values['date_end']=$f->date_end;
            if ($f->date_end == 'NULL') {
                $values['date_end']='0000-00-00';
            }
      /*fwrite($file, "\n newFormrent date_end :" . $values['date_end'] .":");*/
            unset($values[self::PK]);
            if ($f->form_rent_id > 0) {
      /*fwrite($file, "\n newFormrent ==> update" );*/
                $update = $zdb->update(SKI_PREFIX . self::TABLE);
                $update->set($values)->where(self::PK . '=' . $f->form_rent_id);
                $result = $zdb->execute($update);
            } else {
/*fwrite($file, "\n newFormrent ==> insert" );*/
                $insert = $zdb->insert(SKI_PREFIX . self::TABLE)->values($values);
                $result = $zdb->execute($insert);
            }

            if ($result->count() > 0) {
                Analog::log(
                    'New Form #' . $f->form_id,
                    Analog::DEBUG
                );
            } else {
                throw new \Exception(_T("FormRent has not been added", "ski"));
            }
            $zdb->connection->commit();
            return true;
        } catch (\Exception $e) {
          //$zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }
/*
*
*
* @return boolean True si OK, False si une erreur SQL est survenue
*/
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
          /*$file = fopen("/home/daniel/fichier.txt", "a");*/
    /*fwrite($file, "\n 1---------------lib/Repository/FormRent.php buildWhereClause \n");*/
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
    /*fwrite($file, "\n 2---------------lib/Repository/FormRent.php buildWhereClause \n");*/
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
    /*fwrite($file, "\n 1---------------lib/Repository/FormRent.php buildOrderClause \n");*/
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
        }
    /*fwrite($file, "\n 2---------------lib/Repository/FormRent.php buildOrderClause \n");*/
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
    /*$file = fopen("/home/daniel/fichier.txt", "a");*/
    /*fwrite($file, "\n 1---------------lib/Repository/FormRent.php proceedCount \n");*/
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
    /*$file = fopen("/home/daniel/fichier.txt", "a");*/
    /*fwrite($file, "\n 1---------------lib/Repository/FormRent.php canOrderBy \n");*/
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
}
