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

/*
*$file = fopen("/home/daniel/fichier.txt", "a");
*fwrite($file,"\n filter_str2 :   " . $filters->filter_str );
*fwrite($file, "\n---------------\n");
*/

use Analog\Analog;
use Galette\Core\Authentication;
use Galette\Core\Pagination;
use Galette\DynamicFields\Choice;
use Galette\DynamicFields\DynamicField;
use Galette\Entity\Adherent;
use Galette\Entity\DynamicFieldsHandle;
use Galette\Entity\Group;
use Galette\Filters\AdvancedMembersList;
use Galette\Filters\SavedSearchesList;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\LendObject;
use GaletteObjectsLend\Entity\LendRent;
use GaletteObjectsLend\Entity\LendStatus;
use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Repository\Objects;
use GaletteObjectsLend\Repository\Status;
use Galette\Repository\Groups;
use GaletteSki\Filters\FormFilter;
use GaletteSki\Filters\SkiMembersList;
use GaletteSki\Repository\Form;
use GaletteSki\Repository\FormRent;
use GaletteSki\Repository\SkiMembers;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

//galette's dashboard
$this->get(
    __('/dashboard', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $pt=_T("Dashboard");
        $params = [
            'page_title'        => $pt,
            'contentcls'        => 'desktop'
        ];

        $hide_telemetry = true;
        // display page
        $this->view->render(
            $response,
            'ski_desktop.tpl',
            $params
        );
        return $response;
    }
)->setName('ski_dashboard')->add($authenticate);

// Ajout pour SCB
$this->get(
    __('/ski_form', "ski") . '[/{form_id}]',
    function ($request, $response, $args) use ($module, $module_id) {
        // Init values

        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n\n\n 1---------------_routes.php /ski_form\n");


        $olendsprefs=new Preferences($this->zdb);

        $member = new Adherent($this->zdb, (int)$this->login->id);
        //check if requested member is part of managed groups
        $group[]='';
        if ((int)$this->login->id > 0) {
            $groups = $member->groups;

            $is_managed = false;

            foreach ($groups as $g) {
                $group[$g->getId()]=$g->getName();
            }
        }
        $date_end='NULL';
        $comment='NULL';
        $parent_id='NULL';
        $period='NULL';
        $duration='NULL';
        $form_status='Create'; // Create, Open, Change, Lock, Close
        $durations = array("WeekEnd", "4 jours", "Semaine" , "2 semaines", "3 semaines");
        $periods = array("Le Mont Dore" , "Meribel" , "Tignes" ,"Perso");
        if (date("l") == 'Wednesday') {
            $date_begin=date("d/m/Y");
            $date_forecast=date("d/m/Y", strtotime('next Wednesday'));
        } else {
            $date_begin=date("d/m/Y", strtotime('next Wednesday'));
            $date_forecast=date("d/m/Y", strtotime('next Wednesday') + 604800);
        }
        $title = _T("Lend Rental Form", "ski");
        $form_id=null;
        if (isset($args['form_id'])) {
            $form_id=$args['form_id'];
        }
      //
      // members
      //
        $m = new SkiMembers();
        $required_fields = array(
          'id_adh',
          'nom_adh',
          'prenom_adh',
          'parent_id'
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname = mb_strtoupper($member->nom_adh, 'UTF-8') .
                  ' ' . ucwords(mb_strtolower($member->prenom_adh, 'UTF-8'));
                $members[$member->$pk]['id_adh']= $member->id_adh;
                $members[$member->$pk]['sname']= $sname;
                $members[$member->$pk]['parent_id']= $member->parent_id;
            }
        }

      //
      // Forms
      //
        $forms = new Form($this -> zdb, $this->plugins, $olendsprefs);
        $list_forms = $forms->getFormList(true, null, false);

        fwrite($file, "\n ski_form ==> 1 $form_id");
        if (count($list_forms) > 0) {
            fwrite($file, "\n ski_form ==> 2");
            $last_form_id=0;
            foreach ($list_forms as $form) {
              // calcul form_id max
                if ($form->form_id > $last_form_id) {
                    $last_form_id=$form->form_id;
                }
                $form_irdb=$form->form_id;
                fwrite($file, "\n ski_form ==> form->form_id:" . $form_irdb);
                if ($form->form_id == $form_id) {
                    $date_begin=$form->date_begin;
                    $date_forecast=$form->date_forecast;
                    $period=$form->period;
                    $duration=$form->duration;
                    if ($this->preferences->pref_lang == 'en') {
                        $d_b=$date_begin;
                        $d_f=$date_forecast;
                    } else {
                        $d_b=date_format(DateTime::createFromFormat('Y-m-d', $date_begin), 'd/m/Y');
                        $d_f=date_format(DateTime::createFromFormat('Y-m-d', $date_forecast), 'd/m/Y');
                    }

                    $date_end=$form->date_end;
                    if ($date_end === null) {
                        $date_end='NULL';
                    }
                    $parent_id=$form->parent_id;
                    $comment=$form->comment;
                    $form_status=$form->form_status;
                }
            }
            $next_form_id=$last_form_id+1;
            if ($form_id == null) {
                $form_id = $next_form_id;
            }
        }
        fwrite($file, "\n form_status ==> $form_status");
    // form_status : Create, Open, Change, Lock, Close
        if ($form_status == "Create") {
            $categories_list = '';
            $objects_list='';
            $form_rents='';
        //} elseif ($form_status == "Open") {
        } else {
            //
            // Objects
            //
            $objects = new Objects($this->zdb, $this->plugins, $olendsprefs, null);
            $list_object = $objects->getObjectsList(true, null, true, false);

            //
            // Categories
            //
            $categories = new Categories($this->zdb, $this->login, $this->plugins);
            $categories_list = $categories->getCategoriesList(true);
            //
            // recuperer, et affecter les objets déja green par la Forme
            //
            $formrent =  new FormRent($this->zdb, $this->plugins, $olendsprefs);
            $lists = $formrent->getFormRentList(true, null, false);
            //
            // signaler les objets green avant,après, et pendant via une modification du nom de l'objet
            //
            $form_rents[]="";
            foreach ($list_object as $object) {
                $val1='';
                //
                // https://www.w3schools.com/charsets/ref_emoji.asp
                //
                $green='&#9989;';
                $space='&nbsp;';
                $red='&#10060;';
                $blue='&#11088;';
                $lists->buffer();
                $objects_list[$object->object_id]['state']="libre";
                $objects_list[$object->object_id]['object_id']=$object->object_id;
                $objects_list[$object->object_id]['name']=$green . $space . $object->name . " (" . $object->dimension . ")" . $space . $green;

                foreach ($lists as $rent) {
                    if ($rent->form_id == $form_id) {
                        $form_rents[$rent->id_adh][$rent->category_id]['object_id']=$rent->object_id;
                        $form_rents[$rent->id_adh][$rent->category_id]['form_rent_id']=$rent->form_rent_id;
                    }
                    //$rentlist[$rent->object_id][$rent->form_id]['date_begin']=$rent->date_begin;
                    //$rentlist[$rent->object_id][$rent->form_id]['date_forecast']=$rent->date_forecast;
                    //$rentlist[$rent->object_id][$rent->form_id]['date_end']=$rent->date_end;

                    if ($rent->object_id == $object->object_id) {
    //if ($rent->form_id == $form_id) {fwrite($file, "\n ski_form rent  form_id: " . $rent->form_id . " object_id: " . $object->object_id);}

                        $rdb=$rent->date_begin;
                        $db=$rent->date_forecast;
                        $rdf=$date_begin;
                        $df=$date_forecast;
                        if (isset($objects_list[$object->object_id]['state'])) {
                            $val=$objects_list[$object->object_id]['state'];
                        } else {
                            $val='libre';
                        }
    //if ($rent->form_id == $form_id) {fwrite($file, "       rdb:" . $rdb . " rdf:" . $rdf ." db:" . $db . " df:" . $df);}
                        if ($db <= $df and $rdb  >= $rdf) {
                            if ($rent->form_id != $form_id) {
                                fwrite($file, " during ==> " . $val . " " . $val1);
                                $val1='during';
                            } else {
                                $val1='used';
                            }
                        } elseif ($rdb == $df) {
                            if (! $val) {
                                fwrite($file, " before ==> " . $val . " " . $val1);
                                $val1='before';
                            } elseif ($val != 'before') {
                                $val1='during';
                            }
                        } elseif ($db == $rdf) {
                            if (! $val) {
                                fwrite($file, " before ==> " . $val . " " . $val1);
                                $val1='after';
                            } elseif ($val != 'after') {
                                fwrite($file, " during ==> " . $val . " " . $val1);
                                $val1='during';
                            }
                        } else {
                            if (! $val) {
                                fwrite($file, " libre ==> " . $val . " " . $val1);
                                $val1='libre';
                            } else {
                                $val1=$val;
                            }
                        }
                    }
                }
                fwrite($file, "\n ==> object_id:" . $object->object_id . " " . $object->name);
                if ($val1 == 'after') {
                    fwrite($file, "\n ==> object_id:" . $object->object_id . " after " . $object->name);
                    $objects_list[$object->object_id]['state']="after";
                    $objects_list[$object->object_id]['object_id']=$object->object_id;
                    $objects_list[$object->object_id]['category_id']=$object->category_id;
                    $objects_list[$object->object_id]['name']= $green . $space . $object->name . " (" . $object->dimension . ")" . $space . $red ;
                } elseif ($val1 == 'during') {
                    fwrite($file, "\n ==> object_id:" . $object->object_id . " during " . $object->name);
                    $bjects_list[$object->object_id]['state']="during";
                    $objects_list[$object->object_id]['object_id']=$object->object_id;
                    $objects_list[$object->object_id]['name']=$red . $space . $object->name . " (" . $object->dimension . ")" . $space . $red;
                    $objects_list[$object->object_id]['category_id']=$object->category_id;
                } elseif ($val1 == 'before') {
                    fwrite($file, "\n ==> object_id:" . $object->object_id . " before " . $object->name);
                    $objects_list[$object->object_id]['state']="before";
                    $objects_list[$object->object_id]['object_id']=$object->object_id;
                    $objects_list[$object->object_id]['category_id']=$object->category_id;
                    $objects_list[$object->object_id]['name']= $red . $space . $object->name . " (" . $object->dimension . ")" . $space . $green ;
                } elseif ($val1 == 'used') {
                    fwrite($file, "\n ==> object_id:" . $object->object_id . " used " . $object->name);
                    $objects_list[$object->object_id]['state']="before";
                    $objects_list[$object->object_id]['object_id']=$object->object_id;
                    $objects_list[$object->object_id]['category_id']=$object->category_id;
                    $objects_list[$object->object_id]['name']= $blue . $space . $object->name . " (" . $object->dimension . ")" . $space . $blue ;
                } else {
                    fwrite($file, "\n ==> object_id:" . $object->object_id . " libre " . $object->name);
                    $objects_list[$object->object_id]['state']="libre";
                    $objects_list[$object->object_id]['object_id']=$object->object_id;
                    $objects_list[$object->object_id]['name']= $green . $space . $object->name . " (" . $object->dimension . ")" . $space . $green;
                    $objects_list[$object->object_id]['category_id']=$object->category_id;
                }
            }
        }
        fwrite($file, "\n ski_form ==> x \n");


            $params = [
              'page_title'    => $title,
              'form_id'       => $form_id,
              'date_begin'    => $date_begin,
              'date_forecast' => $date_forecast,
              'date_end'      => $date_end,
              'period'  => $period,
              'duration'  => $duration,
              'periods'  => $periods,
              'durations'  => $durations,
              'parent_id'     => $parent_id,
              'form_status'   => $form_status,
              'group' => $group,
              'comment'       => $comment,
              'count' => count($list_forms),
              'next_form_id'  => $next_form_id,
              'members'       => $members,
              'categories'    => $categories_list,
              'objects'       => $objects_list,
              'form_rents'        => $form_rents,
              'require_calendar'  => true,
              'time'          => time()
            ];
        // display page
            $this->view->render(
                $response,
                'file:[' . $module['route'] . ']AddForm.tpl',
                $params
            );
            return $response;
    }
)->setName('ski_form')->add($authenticate);



//AddForm
$this->post(
    __('/do_add_form', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {

        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /do_add_form\n");
        $olendsprefs=new Preferences($this->zdb);

        $form = new Form($this -> zdb, $this->plugins, $olendsprefs);
        $formrent =  new FormRent($this->zdb, $this->plugins, $olendsprefs);
        //$lists = $formrent->getFormRentList(true, null, false);
        $member = new Adherent($this->zdb, (int)$this->login->id);
    //check if requested member is part of managed groups
        $groups = $member->groups;
        foreach ($groups as $g) {
            $group[$g->getId()]=$g->getName();
        }

        if (isset($_POST['form_id'])) {
            $form_id=intval($_POST['form_id']);
            $form->form_id=$form_id;

            if (isset($_POST['comment'])) {
                $comment=$_POST['comment'];
                $form->comment=$comment;
            }
            if (isset($_POST['parent_id'])) {
                $parent_id=$_POST['parent_id'];
                $form->parent_id=$parent_id;
            }
            if (isset($_POST['object'])) {
                $object=$_POST['object'];
            }
            if (isset($_POST['period'])) {
                $period=$_POST['period'];
                $form->period=$period;
            }
            if (isset($_POST['duration'])) {
                $duration=$_POST['duration'];
                $form->duration=$duration;
            }

            if (isset($_POST['form_status'])) {
                $form_status=$_POST['form_status'];
                $form->form_status=$form_status;
            } else {
                $form->form_status='Open';
            }

            if (isset($_POST['date_end'])) {
                $date_end=$_POST['date_end'];
                $form->date_end=$date_end;
            }

            fwrite($file, "\n 2---------------$form_status\n");

            if (in_array("SkiAdmin", $group)) {
              // Change Status
                fwrite($file, "\n 3---------------$form_status\n");
                $new=Form::storeForm($form);
            } else {
              // Create New Form
                fwrite($file, "\n 4---------------$form_status\n");
                $new=Form::newForm($form);
            }
        }
        fwrite($file, "\n new-------- $new -------_routes.php /do_add_form\n");
        // display page
        $success_detected = "Form Saved";
        $this->flash->addMessage('success_detected', $success_detected);
        return $response
        ->withStatus(301)
        ->withHeader(
            'Location',
            $this->router->pathFor('ski_form') . "/" . $form_id
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
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /do_add_object");
        $olendsprefs=new Preferences($this->zdb);

        if (isset($_POST['form_id'])) {
            $formrent =  new FormRent($this->zdb, $this->plugins, $olendsprefs);
            fwrite($file, "\n do_add_object ==>Y");
            $form_id=intval($_POST['form_id']);
            fwrite($file, "\n do_add_object ==>2");
            $formrent->form_id=$form_id;
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['id_adh'])) {
                $formrent->id_adh=$_POST['id_adh'];
            }
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['category_id'])) {
                $formrent->category_id=$_POST['category_id'];
            }
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['object_id'])) {
                $formrent->object_id=$_POST['object_id'];
            }
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['date_begin'])) {
                $formrent->date_begin=$_POST['date_begin'];
            }
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['date_forecast'])) {
                $formrent->date_forecast=$_POST['date_forecast'];
            }
            fwrite($file, "\n do_add_object ==>2");
            if (isset($_POST['date_end'])) {
                $formrent->date_end=$_POST['date_end'];
            }


            fwrite($file, "\n do_add_object ==>2");

            $lists = $formrent->getFormRentList(true, null, false);
            fwrite($file, "\n do_add_object ==>3");
            $formrent->form_rent_id=0;
            if (count($lists) > 0) {
                foreach ($lists as $list) {
                    $form_rent_id = $list->form_rent_id;
                    $id_adh = $list->id_adh;
                    $form_id= $list->form_id;
                    $category_id = $list->category_id;
                    fwrite($file, "\n\n\nform_rent_id:" . $form_rent_id);
                    fwrite($file, "\nid_adh:" . $id_adh);
                    fwrite($file, "\nform_id:" . $form_id);
                    fwrite($file, "\ncategory_id:" . $category_id);
                    if ($id_adh == $formrent->id_adh) {
                        if ($form_id == $formrent->form_id) {
                            if ($category_id == $formrent->category_id) {
                                fwrite($file, "\n do_add_object ==>6");
                                $formrent->form_rent_id=$form_rent_id;
                            }
                        }
                    }
                }
            }
            fwrite($file, "\n====>form_rent_id:" . $formrent->form_rent_id);
            fwrite($file, "\n do_add_object ==>7");

            $response=FormRent::newFormRent($formrent);
            if ($response !== true) {
                $error_detected = $response;
                $this->flash->addMessage('error_detected', $error_detected);
                return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('ski_form') . "/" . $form_id
                );
            } else {
                $success_detected = "Form Saved";
                $this->flash->addMessage('success_detected', $success_detected);
                return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('ski_form') . "/" . $form_id
                );
            }

            fwrite($file, "\n do_add_object ==> $response");
        }
    }
)->setName('ski_do_add_object')->add($authenticate);
//fin AddObject


$this->get(
    __('/ski_form_list', "ski") . '[/{option:' . __('page', 'routes') . '|' .
    __('order') . '|' . __('category') . '}/{value:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {

        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /ski_form_list\n");

        // Init values
        $option = null;
        $value = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        if (isset($args['value'])) {
            $value = $args['value'];
        }

        $olendsprefs=new Preferences($this->zdb);

        if (isset($this->session->ski_filter_forms)) {
            $filters = $this->session->ski_filter_forms;
        } else {
            $filters = new FormFilter();
        }
        if (isset($args['option'])) {
            $option = $args['option'];
            switch ($option) {
                case __('page', 'routes'):
                    $filters->current_page = (int)$value;
                    fwrite($file, "\n 2-------- $filters->current_page  -------_routes.php /ski_form_list\n");
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
        $m = new SkiMembers();
        $required_fields = array(
        'id_adh',
        'nom_adh',
        'prenom_adh',
        'parent_id'
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname = mb_strtoupper($member->nom_adh, 'UTF-8') .
                ' ' . ucwords(mb_strtolower($member->prenom_adh, 'UTF-8'));
                $members[$member->$pk]['id_adh']= $member->id_adh;
                $members[$member->$pk]['sname']= $sname;
                $members[$member->$pk]['parent_id']= $member->parent_id;
            }
        }
      //
        // Forms
        //
        $forms = new Form($this -> zdb, $this->plugins, $olendsprefs, $filters);

        $list_form = $forms->getFormList();
        $result = $list_form->toArray();

        //assign pagination variables to the template and add pagination links
        $filters->setViewCommonsFilters($olendsprefs, $this->view->getSmarty());
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
        $this->session->ski_filter_forms = $filters;

        $params = [
        'page_title'    => $title,
        'sforms'        => $forms,
        'nb_forms'     => count($list_form),
        'lform'        => $result,
        'members'       => $members,
        'require_calendar'  => true,
        'filters'               => $filters,
        'module_id'             => $module_id,
        'time'          => time()
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


$this->get(
    __('/ski_preferences', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /ski_preferences\n");
        fwrite($file, "\n 2---------------_routes.php /ski_preferences\n");
        $form_id=intval($_POST['form_id']);
    }
)->setName('ski_preferences')->add($authenticate);


//forms list filtering
$this->post(
    __('/form') . __('/filter', 'routes'),
    function ($request, $response) {

        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /form/filter\n");

        $post = $request->getParsedBody();
        $olendsprefs=new Preferences($this->zdb);
        if (isset($this->session->ski_filter_forms)) {
            $filters = $this->session->ski_filter_forms;
        } else {
            $filters = new FormFilter();
        }

        //
        //  Formater les dates selon la langue 24/09/2019 ==> 2019-08-24 ( à l'anglaise)
        //
    //        if ($this->preferences->pref_lang == 'en') {
    //            $d_b=$date_begin;
    //            $d_f=$date_forecast;
    //        } else {
    //            $d_b=date_format(DateTime::createFromFormat('d/m/Y', $date_begin), 'Y-m-d');
    //            $d_f=date_format(DateTime::createFromFormat('d/m/Y', $date_forecast), 'Y-m-d');
    //        }


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
                $filters->field_filter= $post['field_filter'];
            }
            //number of rows to show
            if (isset($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }
        $filters->setViewCommonsFilters($olendsprefs, $this->view->getSmarty());
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
        $this->session->ski_filter_forms = $filters;
        fwrite($file, "\n 2--$filters->filter_str--$filters->field_filter---$filters->show --------_routes.php /form/filter\n");
        return $response
        ->withStatus(301)
        ->withHeader('Location', $this->router->pathFor('ski_form_list'));
    }
)->setName('ski_filter_form')->add($authenticate);

$this->get(
    __('/ski_remove_form', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /ski_remove_form\n");
        fwrite($file, "\n 2---------------_routes.php /ski_remove_form\n");
        $form_id=intval($_POST['form_id']);
    }
)->setName('ski_remove_form')->add($authenticate);

$this->get(
    '/ski_members[/{option:page|order|edit}/{value:\d+}]',
    function ($request, $response, $args = []) {
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /ski_members\n");
        $parent_id='';
        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }

        if (isset($this->session->filter_members)) {
            $filters = $this->session->filter_members;
        } else {
            $filters = new SkiMembersList();
        }
        $page = 'ski_members.tpl';
        $page_title = _T("Membersmanagement", "ski");

        $page_title1 =_T("Familymanagement", "ski");
        $m= new SkiMembers($filters);
        $members = array();
        $members = $m->getMembersList(true);
        $g = new Groups($this->zdb, $this->login);
        $groups_list = $g->getList();
        $groups=[];
        $fuser=[];
        $dynval=[];
        $dynadh=[];
        $fvalues=[];
        if ($option !== null) {
            switch ($option) {
                case 'page':
                    $filters->current_page = (int)$value;
                    break;
                case 'order':
                    $filters->orderby = $value;
                    break;
                case 'edit':
                    fwrite($file, "\n 2---------------_routes.php /ski_members\n");
                    $page = 'ski_family.tpl';
                    $parent_id=$value;
                    $page_title = $page_title1;
                    //
                    $filters = new SkiMembersList();
                    $filters->field_filter = intval(SkiMembers::FILTER_PARENT);
                    $filters->filter_str = $parent_id;
                    //
                    $deps = ['dynamics'  => true];
                    $m = new Adherent($this->zdb, null, $deps);
                    //
                    $fields=$m->getDynamicFields()->getFields();
                    if (is_array($fields)) {
                        foreach ($fields as $field) {
                            $fid=intval($field->getId());
                            $dynval[$fid]['fname']=$field->getName();
                            $dynval[$fid]['values']=$field->getValues();
                        }
                    }
                    //
                    $m = new SkiMembers($filters);
                    $members = $m->getMembersList(true);
                    if (is_array($members)) {
                        fwrite($file, "\n 3---------------_routes.php /ski_members\n");
                        foreach ($members as $member) {
                            $m = new Adherent($this->zdb, (int)$member->id, $deps);
                            $fields=$m->getDynamicFields()->getFields();
                            if (is_array($fields)) {
                                fwrite($file, "\n 4---------------_routes.php /ski_members\n");
                                foreach ($fields as $field) {
                                    $fid=intval($field->getId());
                                    $ffield=$m->getDynamicFields()->getValues($fid);
                                    if (is_array($ffield)) {
                                        fwrite($file, "\n 5---------------_routes.php /ski_members\n");
                                        foreach ($ffield as $field_data) {
                                             $dynadh[(int)$member->id][intval($field->getId())]['fname']=$field->getName();
                                             $dynadh[(int)$member->id][intval($field->getId())]['fval']=$field_data['field_val'];
                                             $dynadh[(int)$member->id][intval($field->getId())]['ftext']=$field_data['text_val'];
                                        }
                                    }
                                }
                            }
                            //
                            $grp = $m->groups;
                            if (is_array($grp)) {
                                fwrite($file, "\n 6---------------_routes.php /ski_members\n");
                                foreach ($grp as $group) {
                                    $groups[(int)$member->id][$group->getId()]=$group->getIndentName();
                                }
                            }
                        }
                    }
                    break;
            }
        }
        fwrite($file, "\n 7------$option ----$value-----_routes.php /ski_members\n");

        //assign pagination variables to the template and add pagination links
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
        $filters->setViewCommonsFilters($this->preferences, $this->view->getSmarty());

        $this->session->filter_members = $filters;

        // display page
        $this->view->render(
            $response,
            $page,
            array(
                'page_title'            => $page_title,
                'require_dialog'        => true,
                'require_calendar'      => true,
                'require_mass'          => true,
                'members'               => $members,
                '$parent_id'            => $parent_id,
                'filter_groups_options' => $groups_list,
                'groups'                => $groups,
                'filters'               => $filters,
                'dynval'                => $dynval,
                'dynadh'                => $dynadh,
                'adv_filters'           => $filters instanceof AdvancedMembersList
            )
        );
        return $response;
    }
)->setName('ski_members')->add($authenticate);

//members list filtering
$this->post(
    '/ski_members/filter',
    function ($request, $response) {
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n\n\n 1---------------_routes.php ski_members/filter\n");
        $post = $request->getParsedBody();

        $m = new SkiMembers();
        $required_fields = array(
        'id_adh',
        'nom_adh',
        'parent_id'
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $members[$member->$pk]['name']= $member->nom_adh;
                $members[$member->$pk]['parent_id'] = $member->parent_id;
            }
        }


        if (isset($this->session->filter_members)) {
            //CAUTION: this one may be simple or advanced, display must change
            $filters = $this->session->filter_members;
        } else {
            $filters = new SkiMembersList();
        }

        //reintialize filters
        if (isset($post['clear_filter'])) {
            $filters = new SkiMembersList();
        } else {
            //field to filter
            if (isset($post['field_filter'])) {
                if (is_numeric($post['field_filter'])) {
                      $ffield=intval($post['field_filter']);
                    $filters->field_filter = $ffield;
                }
            }
            //group filter
            if (isset($post['group_filter'])
                && $post['group_filter'] > 0
            ) {
                $filters->group_filter = (int)$post['group_filter'];
            }
            //string to filter
            if (isset($post['filter_str'])) { //filter search string
                $ffstr = stripslashes(htmlspecialchars($post['filter_str'], ENT_QUOTES));
                fwrite($file, "\n\n\n == field_filter:$ffield ---- fgroup:$ffstr -----------_routes.php ski_members/filter\n");
                $fgroup=intval(SkiMembers::FILTER_PARENT);
                if ($ffield == $fgroup) {
                    $val = strtolower(($post['filter_str']));
                    fwrite($file, "\n\n\n 3 -------xx $val xxx--------_routes.php ski_members/filter\n");
                    foreach ($members as $m) {
                        $name=strtolower($m['name']);
                        $parent_id=$m['parent_id'];
                        fwrite($file, "\n\n\n ---name:$name ----parent_id:$parent_id--------_routes.php ski_members/filter\n");
                        if (strstr($name, $val) != '') {
                            fwrite($file, "\n\n\n -------xxxxx--------_routes.php ski_members/filter\n");
                            $filters->filter_str = $parent_id;
                            break;
                        }
                    }
                } else {
                    $filters->filter_str = $ffstr;
                }
            }

            //membership to filter

            //number of rows to show
            if (isset($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }


        $this->session->filter_members = $filters;
        fwrite($file, "\n\n\n Fin---------------_routes.php /filter\n");
        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('ski_members'));
    }
)->setName('filter-ski_members')->add($authenticate);


$this->get(
    __('/ski_form_printform', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $file = fopen("/home/daniel/fichier.txt", "a");
        fwrite($file, "\n 1---------------_routes.php /ski_remove_form\n");
        fwrite($file, "\n 2---------------_routes.php /ski_remove_form\n");
        $form_id=intval($_POST['form_id']);
    }
)->setName('ski_form_printform')->add($authenticate);
