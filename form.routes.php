<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Ski routes
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
 * @version   1.0-dev
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.9.3
 */

use Analog\Analog;
use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Filters\CategoriesList;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Repository\Objects;
use GaletteSki\Filters\FormFilter;
use GaletteSki\Filters\FormRentFilter;
use GaletteSki\Repository\Form;
use GaletteSki\Repository\FormRent;
use Galette\Entity\Adherent;
use Galette\Entity\Contribution;
use Galette\Filters\AdvancedMembersList;
use Galette\Filters\MembersList;
use Galette\Repository\Groups;
use Galette\Repository\Members;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';


$this->get(
    __('/ski_form', "ski") . '[/{form_id}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $form_id = null;
        if (isset($args['form_id'])) {
            $form_id = $args['form_id'];
        }
        $olendsprefs = new Preferences($this->zdb);
        $deps = array(
            'groups' => true,
            'parent' => true,
            'children' => true,
            'dynamics' => true,
        );
        $adhs = new Adherent($this->zdb, (int) $this->login->id, $deps);
        //check if requested member is part of managed groups
        $group[] = '';
        if ((int) $this->login->id > 0) {
            $groups = $adhs->groups;
            $is_managed = false;
            foreach ($groups as $g) {
                $group[$g->getId()] = $g->getName();
            }
        }
        $date_end = 'NULL';
        $comment = 'NULL';
        $parent_id = 'NULL';
        $period = 'NULL';
        $duration = 'NULL';
        $next_form_id = '1';
        $form_status = 'Create'; // Create, Open, Change, Lock, Close
        $c = new Contribution($this->zdb, $this->login);
        // chargement des périodes et des durées (champs dynamiques contribution)
        $fields = $c->getDynamicFields()->getFields();
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $fid = intval($field->getId());
                $fform = $field->getForm();
                $fname = $field->getName();
                $fvalue = $field->getValues();
                $$fname = $fvalue;
            }
        }
        if (date("l") == 'Wednesday') {
            $date_begin = date("d/m/Y");
            $date_forecast = date("d/m/Y", strtotime('next Wednesday'));
        } else {
            $date_begin = date("d/m/Y", strtotime('next Wednesday'));
            $date_forecast = date("d/m/Y", strtotime('next Wednesday') + 604800);
        }
        $title = _T("Lend Rental Form", "ski");
        $m = new Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh',
            'parent_id',
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname = mb_strtoupper($member->nom_adh, 'UTF-8') .
                ' ' . ucwords(mb_strtolower($member->prenom_adh, 'UTF-8'));
                $members[$member->$pk]['id_adh'] = $member->id_adh;
                $members[$member->$pk]['sname'] = $sname;
                if ($member->parent_id != null) {
                    $members[$member->$pk]['parent_id'] = $member->parent_id;
                } else {
                    $members[$member->$pk]['parent_id'] = $member->id_adh;
                }
            }
        }
        $forms = new Form($this->zdb, $this->plugins, $olendsprefs);
        $list_forms = $forms->getFormList(true, null, false);
        if (count($list_forms) > 0) {
            $last_form_id = 0;
            foreach ($list_forms as $form) {
                // calcul form_id max
                if ($form->form_id > $last_form_id) {
                    $last_form_id = $form->form_id;
                }
                $form_irdb = $form->form_id;
                if ($form->form_id == $form_id) {
                    $date_begin = $form->date_begin;
                    $date_forecast = $form->date_forecast;
                    $period = $form->period;
                    $duration = $form->duration;
                    if ($this->preferences->pref_lang == 'en') {
                        $d_b = $date_begin;
                        $d_f = $date_forecast;
                    } else {
                        $d_b = date_format(DateTime::createFromFormat('Y-m-d', $date_begin), 'd/m/Y');
                        $d_f = date_format(DateTime::createFromFormat('Y-m-d', $date_forecast), 'd/m/Y');
                    }

                    $date_end = $form->date_end;
                    if ($date_end === null) {
                        $date_end = 'NULL';
                    }
                    $parent_id = $form->parent_id;
                    $comment = $form->comment;
                    $form_status = $form->form_status;
                }
            }
            $next_form_id = $last_form_id + 1;
            if ($form_id == null) {
                $form_id = $next_form_id;
            }
        }
        // form_status : Create, Open, Change, Lock, Close
        if ($form_status == "Create") {
            $categories_list = '';
            $objects_list = '';
            $form_rents = '';
        } else {
            //
            // Objects
            //
            $filters = new ObjectsList();
            $filters->orderby = constant('GaletteObjectsLend\Repository\Objects::ORDERBY_SERIAL');
            $objects = new Objects($this->zdb, $this->plugins, $olendsprefs, $filters);
            $list_object = $objects->getObjectsList(true, null, true, false);

            //
            // Categories
            //
            $filters = new CategoriesList();
            $filters->orderby = constant('GaletteObjectsLend\Repository\Objects::ORDERBY_NAME');
            $categories = new Categories($this->zdb, $this->login, $this->plugins, $filters);
            $categories_list = $categories->getCategoriesList(true);
            //
            // recuperer, et affecter les objets déja green par la Forme
            //
            $filters = new FormRentFilter();
            $filters->orderby = constant('GaletteSki\Repository\FormRent::ORDER_DESC');
            $filters->field_filter = constant('GaletteSki\Repository\FormRent::FILTER_B_F_DATE');
            $filters->begin = $date_begin;
            $filters->filter_str = "BFdate";
            $filters->forecast = $date_forecast;
            $formrent = new FormRent($this->zdb, $this->plugins, $olendsprefs, $filters);
            $lists = $formrent->getFormRentList(true, null, false);
            $output = count($lists);

            //
            // signaler les objets green avant,après, et pendant via une modification du nom de l'objet
            //
            $form_rents[] = "";
            foreach ($list_object as $object) {
                $before = 0;
                $after = 0;
                $during = 0;
                $used = 0;
                $before1 = "";
                $after1 = "";
                $during1 = "";
                $used1 = "";
                $val1 = '';
                $val = '';
                //
                // https://www.w3schools.com/charsets/ref_emoji.asp
                //
                $green = '&#9989;'; //libre
                $space = '&nbsp;';
                $red = '&#10060;'; // pris sur une autre fiche
                $gray = '&#10071;'; // pris sur cette fiche
                $yellow = '&#9995;'; // pris avant ou après
                $lists->buffer();
                $objects_list[$object->object_id]['state'] = "libre";
                foreach ($lists as $rent) {
                    //if ($rent->form_id == $form_id) {
                    $form_rents[$rent->form_id][$rent->id_adh][$rent->category_id]['object_id'] = $rent->object_id;
                    $form_rents[$rent->form_id][$rent->id_adh][$rent->category_id]['form_rent_id'] = $rent->form_rent_id;
                    $form_rents[$rent->form_id][$rent->id_adh][$rent->category_id]['date_begin'] = $rent->date_begin;
                    $form_rents[$rent->form_id][$rent->id_adh][$rent->category_id]['date_forecast'] = $rent->date_forecast;
                    $form_rents[$rent->form_id][$rent->id_adh][$rent->category_id]['date_end'] = $rent->date_end;
                    //}
                    $id_adh = "";
                    if ($rent->object_id == $object->object_id) {
                        $obj = $object->object_id;
                        $rdb = $rent->date_begin;
                        $rdf = $rent->date_forecast;
                        $db = $date_begin;
                        $df = $date_forecast;
                        // val : statut rent's précédents
                        // val1 : statut rent  courant
                        if (isset($objects_list[$object->object_id]['state'])) {
                            $val = $objects_list[$object->object_id]['state'];
                        } else {
                            $val = 'libre';
                        }
                        // début calcul val1
                        if ($db < $rdf and $df > $rdb) {
                            if ($rent->form_id != $form_id) {
                                $val1 = 'during';
                                $during = $during + 1;
                                if (strpos($during1, $rent->form_id) == false) {
                                    $during1 = $during1 . " " . $rent->form_id ;
                                    $objects_list[$object->object_id][$rent->form_id] = $val1;
                                }
                            } else {
                                $val1 = 'used';
                                $used = $used + 1;
                            }
                        } elseif ($df == $rdb) {
                            $val1 = 'after';
                            $after = $after + 1;
                            if (strpos($after1, $rent->form_id) == false) {
                                $after1 = $after1 . " " . $rent->form_id;
                                $objects_list[$object->object_id][$rent->form_id] = $val1;
                            }
                        } elseif ($db == $rdf) {
                            $val1 = 'before';
                            $before = $before + 1;
                            if (strpos($before1, $rent->form_id) == false) {
                                $before1 = $before1 . " " . $rent->form_id;
                                $objects_list[$object->object_id][$rent->form_id] = $val1;
                            }
                        } else {
                            $val1 = 'libre';
                        } // fin calcul val1
                        // calcul new val
                        if ($val == 'libre') {
                            $val = $val1;
                        } elseif ($val == 'used') {
                            $val = $val1;
                        } elseif ($val == 'before' and $val1 == 'after') {
                            $val = 'before-after';
                        } elseif ($val == 'after' and $val1 == 'before') {
                            $val = 'before-after';
                        } elseif ($val == 'during') {
                            $val = 'during';
                        } elseif ($val1 == 'during') {
                            $val = 'during';
                        }
                        $objects_list[$object->object_id]['state'] = $val;
                    } //if ($rent->object_id == $object->object_id)
                } //foreach ($lists as $rent)
                $val = $objects_list[$object->object_id]['state'];

                if ($val == 'libre') {
                    $b = $green . '&emsp;';
                    $a = '&emsp;' . $green;
                } elseif ($val == 'before') {
                    $b = $yellow . '[' . $before1 . ']&emsp;';
                    $a = '&emsp;' . $green;
                } elseif ($val == 'after') {
                    $b = $green . '&emsp;';
                    $a = $space . '&emsp;[' . $after1 . ']' . $yellow;
                } elseif ($val == 'before-after') {
                    $b = $yellow . '[' . $before1 . ']&emsp;';
                    $a = $space . '&emsp;[' . $after1 . ']' . $yellow;
                } elseif ($val == 'used') {
                    $a = '&emsp;' . $gray;
                    $b = $gray . '&emsp;';
                } elseif ($val == 'during') {
                    $b = $red . '&ensp;' ;
                    $a = '&ensp;' . $red  . '&emsp;[fiche ' . $during1 . ']';
                }

                if (GALETTE_MODE == 'DEV') {
                    $D = ' {' . $object->object_id . '}';
                } else {
                    $D = "";
                }
                //$objects_list[$object->object_id]['name']= $b . $object->name . " (" . $object->dimension . ")" . $a ;
                if ($object->name == "casque") {
                    $objects_list[$object->object_id]['name'] = $b . $object->serial_number . " (" . $object->dimension . ")" . $D . $a;
                    $objects_list[$object->object_id]['name1'] = $object->serial_number . " (" . $object->dimension . ")" . $D  ;
                } else {
                    $objects_list[$object->object_id]['name'] = $b . $object->dimension . $D  . $a;
                    $objects_list[$object->object_id]['name1'] = $object->dimension . $D;
                }



                $objects_list[$object->object_id]['object_id'] = $object->object_id;
                $objects_list[$object->object_id]['serial_number'] = $object->serial_number;
                $objects_list[$object->object_id]['category_id'] = $object->category_id;
                if ($object->serial_number == "000") {
                    $objects_list[$object->object_id]['name'] = "";
                    $objects_list[$object->object_id]['name1'] = "";
                }
                if ($object->name == "baton") {
                    $objects_list[$object->object_id]['name'] = $object->dimension ;
                    $objects_list[$object->object_id]['name1'] = $object->dimension ;
                        $objects_list[$object->object_id]['state'] = 'used';
                }
            } //foreach ($list_object as $object)
        } // if ($form_status == "Create")

            $tpage = "AddForm.tpl";
        $params = [
            'page_title' => $title,
            'form_id' => $form_id,
            'date_begin' => $date_begin,
            'date_forecast' => $date_forecast,
            'date_end' => $date_end,
            'period' => $period,
            'duration' => $duration,
            'periods' => $periods,
            'durations' => $durations,
            'parent_id' => $parent_id,
            'form_status' => $form_status,
            'group' => $group,
            'adhs' => $adhs,
            'comment' => $comment,
            'next_form_id' => $next_form_id,
            'members' => $members,
            'categories' => $categories_list,
            'objects' => $objects_list,
            'form_rents' => $form_rents,
            'require_calendar' => true,
            'time' => time(),
        ];
        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']' . $tpage,
            $params
        );
        return $response;
    }
)->setName('ski_form')->add($authenticate);

//AddForm
$this->post(
    __('/do_add_form', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $olendsprefs = new Preferences($this->zdb);
        $form = new Form($this->zdb, $this->plugins, $olendsprefs);
        $formrent = new FormRent($this->zdb, $this->plugins, $olendsprefs);
        $deps = array(
            'groups' => true,
            'parent' => true,
            'children' => true,
            'dynamics' => true,
        );
        $member = new Adherent($this->zdb, (int) $this->login->id, $deps);
        $groups = $member->groups;
        //check if requested member is part of managed groups
        if (is_array($groups)) {
            foreach ($groups as $g) {
                $group[$g->getId()] = $g->getName();
            }
        }
        if (isset($_POST['form_id'])) {
            $form_id = intval($_POST['form_id']);
            $form->parent_id = $form_id;
            $form->form_id = $form_id;
            foreach ($_POST as $k => $val) {
                $form->$k = "$val";
                $$k = "$val";
            }
            $m1 = new Adherent($this->zdb, (int)$form->parent_id);
            if (isset($m1->parent->sname)) {
                $form->parent_sname = $m1->parent->name . " " . $m1->parent->surname;
            } else {
                $form->parent_sname = $m1->name . " " . $m1->surname;
            }
            $date_begin == date('Y-m-d', strtotime($date_begin));
            $date_forecast == date('Y-m-d', strtotime($date_forecast)) ;

           //$output =print_r($form, true);
            
            if (in_array("SkiAdmin", $group)) {
                if ($_POST['form_status'] == "Create") {
                    $form->form_status = 'Open';
                    $new = Form::newForm($form);
                   //$output =print_r($new, true);
                } else {
                    $new = Form::storeForm($form);
                }
            } else {
                $new = Form::storeForm($form);
            }
        }
        // display page
        if ($new == true) {
            $success_detected = "Form Saved " . $_POST['form_status'];
            $this->flash->addMessage('success_detected', $success_detected);
        } else {
            $error_detected = "Error";
            $this->flash->addMessage('error_detected', $error_detected);
        }
        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_form_list')
            );
    }
)->setName('ski_do_add_form')->add($authenticate);
//fin AddForm

$this->post(
    __('/done_form', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {

        // display page
        $success_detected = "Form Saved";
        $this->flash->addMessage('success_detected', $success_detected);
        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_form_list')
            );
    }
)->setName('ski_done_form')->add($authenticate);

//AddForm
$this->post(
    __('/do_add_object', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $pst = explode(":", $_POST['object']);
        $olendsprefs = new Preferences($this->zdb);
        $formrent = new FormRent($this->zdb, $this->plugins, $olendsprefs);

        foreach ($pst as $val) {
            $v = explode("=", $val);
            if (array_key_exists(1, $v)) {
                $a = $v[0];
                $b = $v[1];
                $formrent->$a = "$b";
                $$a = "$b";
            }
        }
        if (isset($form_id)) {
            $lists = $formrent->getFormRentList(true, null, false);
            $formrent->form_rent_id = 0;
            if (count($lists) > 0) {
                foreach ($lists as $list) {
                    $form_rent_id = $list->form_rent_id;
                    $id_adh = $list->id_adh;
                    $form_id = $list->form_id;
                    $category_id = $list->category_id;
                    if ($id_adh == $formrent->id_adh) {
                        if ($form_id == $formrent->form_id) {
                            if ($category_id == $formrent->category_id) {
                                $formrent->form_rent_id = $form_rent_id;
                            }
                        }
                    }
                }
            }
            $res = FormRent::newFormRent($formrent);
            if ($res !== true) {
                $error_detected[] = _T("An error occurred while renting the object.");
                $this->flash->addMessage('error_detected', $error_detected);
            } else {
                $success_detected = "Object has been rent";
                $this->flash->addMessage('success_detected', $success_detected);
            }
            return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_form') . "/" . $form_id
            );
        }
    }
)->setName('ski_do_add_object')->add($authenticate);
//fin AddObject

$this->get(
    __('/ski_form_list', "ski") . '[/{option:' . __('page', 'routes') . '|' .
    __('order') . '|' . __('category') . '}/{value:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        // Init values
        $option = null;
        $value = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        if (isset($args['value'])) {
            $value = $args['value'];
        }
        $olendsprefs = new Preferences($this->zdb);
        if (isset($this->session->ski_filter_forms)) {
            $filters = $this->session->ski_filter_forms;
        } else {
            $filters = new FormFilter();
        }
        if (isset($args['option'])) {
            $option = $args['option'];
            switch ($option) {
                case __('page', 'routes'):
                    $filters->current_page = (int) $value;
                    break;
                case __('order', 'routes'):
                    $filters->orderby = $value;

                    break;
                case __('category'):
                    if ($value == 0) {
                        $value = null;
                    }
                    $filters->category_filter = $value;
                    break;
            }
        }
        if (isset($args['value'])) {
            $value = $args['value'];
        }
        $title = _T("Ski Form List", "ski");
        //
        // members
        //
        $m = new Members($filters);
        $m = new Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh',
            'parent_id',
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname = mb_strtoupper($member->nom_adh, 'UTF-8') .
                ' ' . ucwords(mb_strtolower($member->prenom_adh, 'UTF-8'));
                $members[$member->$pk]['id_adh'] = $member->id_adh;
                $members[$member->$pk]['sname'] = $sname;
                $members[$member->$pk]['parent_id'] = $member->parent_id;
            }
        } else {
            $members[]['id_adh'] = '';
            $members[]['sname'] = '';
            $members[]['id_adh'] = '';
        }
        //
        // Forms
        //
        $forms = new Form($this->zdb, $this->plugins, $olendsprefs, $filters);
        $list_form = $forms->getFormList();
        $result = $list_form->toArray();
        //assign pagination variables to the template and add pagination links
        $filters->setViewCommonsFilters($olendsprefs, $this->view->getSmarty());
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
        $this->session->ski_filter_forms = $filters;
        $params = [
            'page_title' => $title,
            'sforms' => $forms,
            'nb_forms' => count($list_form),
            'lform' => $result,
            'members' => $members,
            'require_calendar' => true,
            'filters' => $filters,
            'module_id' => $module_id,
            'time' => time(),
        ];
        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']ListForm.tpl',
            $params
        );
        return $response;
    }
)->setName('ski_form_list')->add($authenticate);


//forms list filtering
$this->post(
    __('/form') . __('/filter', 'routes'),
    function ($request, $response) {
        $post = $request->getParsedBody();
        $m = new Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'parent_id',
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                if ($member->parent_id == '') {
                    $member->parent_id = $member->id;
                }
                $members[$member->$pk]['name'] = $member->nom_adh;
            }
        }
        $olendsprefs = new Preferences($this->zdb);
        if (isset($this->session->ski_filter_forms)) {
            $filters = $this->session->ski_filter_forms;
        } else {
            $filters = new FormFilter();
        }
        //reintialize filters
        if (isset($post['clear_filter'])) {
            $filters->reinit();
        } else {
            //string to filter
            if (isset($post['filter_str'])) { //filter search string
                $filters->filter_str = stripslashes(
                    htmlspecialchars($post['filter_str'], ENT_QUOTES)
                );
            }
            //activity to filter
            if (is_numeric($post['field_filter'])) {
                $filters->field_filter = $post['field_filter'];
            }
            //number of rows to show
            if (isset($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }
        $filters->setViewCommonsFilters($olendsprefs, $this->view->getSmarty());
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
        $this->session->ski_filter_forms = $filters;
        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('ski_form_list'));
    }
)->setName('ski_filter_form')->add($authenticate);

$this->get(
    __('/ski_remove_form', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $form_id = intval($_POST['form_id']);
    }
)->setName('ski_remove_form')->add($authenticate);


$this->get(
    __('/ski_form_printform', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $form_id = intval($_POST['form_id']);
    }
)->setName('ski_form_printform')->add($authenticate);
