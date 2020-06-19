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

// Ajout pour SCB

$this->get(
    '/ski_members[/{option:page|order|edit}/{value:\d+}]',
    function ($request, $response, $args = []) {
        $post = $_POST;
        $parent_id = '';
        $option = '';
        $groups = [];
        $fuser = [];
        $dynval = [];
        $dynadh = [];
        $fvalues = [];
        $dyn = [];
        $members = [];
        $list_members = [];
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
            $parent_id = (int)$value;
        }
        $page = 'ski_members.tpl';
        $page_title = _T("Membersmanagement", "ski");
        $page_title1 = _T("Familymanagement", "ski");
        $g = new Groups($this->zdb, $this->login);
        $groups_list = $g->getList();
        $deps = array(
            'parent' => true,
            'children' => true,
            'dynamics' => true,
            'groups' => true,
        );
        if ($option == 'edit') {
            $id = (int)$value;
            $a = new Adherent($this->zdb, $id, $deps);
            if ($a->id != '') {
                $exist = true;
            } else {
                $exist = false;
                $option = '';
            }
        }

        // chargement des différents tarifs, licences et années d'adhésion (champs dynamiques adherent)
        $a = new Adherent($this->zdb, null, $deps);
        $fields = $a->getDynamicFields()->getFields();
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $fid = intval($field->getId());
                $dynval[$fid]['fform'] = $field->getForm();
                $dynval[$fid]['fname'] = $field->getName();
                $dynval[$fid]['values'] = $field->getValues();
            }
        }
        if (isset($this->session->filter_members)) {
            $filters = $this->session->filter_members;
        } else {
            $filters = new MembersList();
        }
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh',
            'parent_id',
        );
        if ($option !== '') {
            switch ($option) {
                case 'page':
                    $filters->current_page = (int) $value;
                    //assign pagination variables to the template and add pagination links
                    $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
                    $filters->setViewCommonsFilters($this->preferences, $this->view->getSmarty());
                    $m = new Members($filters);
                    $list_members = $m->getMembersList(true, null, false, false, false, false, false);
                    foreach ($list_members as $member) {
                        $mid = $member->id;
                        $c = new Adherent($this->zdb, $mid, $deps);
                        $members[$mid]['id_adh'] = $mid;
                        $has_parent = $c->hasParent();
                        if ($has_parent) {
                            $members[$mid]['parent_id'] = $c->parent->id;
                            $members[$mid]['parent_name'] =  $c->parent->name . " " . $c->parent->surname;
                        } else {
                            $members[$mid]['parent_id'] = '';
                            $members[$mid]['parent_name'] = '';
                        }
                    }
                    //$members=$list_members->toArray();
                    break;
                case 'order':
                    $filters->orderby = $value;
                    //assign pagination variables to the template and add pagination links
                    $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
                    $filters->setViewCommonsFilters($this->preferences, $this->view->getSmarty());
                    $m = new Members($filters);
                    $list_members = $m->getMembersList(true, null, false, false, false, false, false);
                    foreach ($list_members as $member) {
                        $mid = $member->id;
                        $c = new Adherent($this->zdb, $mid, $deps);
                        $members[$mid]['id_adh'] = $mid;
                        $has_parent = $c->hasParent();
                        if ($has_parent) {
                            $members[$mid]['parent_id'] = $c->parent->id;
                            $members[$mid]['parent_name'] =  $c->parent->name . " " . $c->parent->surname;
                        } else {
                            $members[$mid]['parent_id'] = '';
                            $members[$mid]['parent_name'] = '';
                        }
                    }
                    //$members=$list_members->toArray();
                    break;
                case 'edit':
                    $filters = new MembersList();
                    $page = 'ski_family.tpl';
                    $id = (int)$value;
                    $page_title = $page_title1;
                    $a = new Adherent($this->zdb, $id, $deps);
                    $has_children = $a->hasChildren();
                    $has_parent = $a->hasParent();
                    $parent_id = $id;
                    if ($has_parent) {
                        // je suis chez l'enfant
                        $parent_id = $a->parent->id;
                    }
                    $a = new Adherent($this->zdb, $parent_id, $deps);
                    $MID[] = $parent_id;
                    $parent_name = $a->name;
                    if ($has_children) {
                        foreach ($a->children as $children) {
                            // je recupere les id de la fanille
                            $cid = $children->id;
                            $MID[] = $cid;
                        }
                    }
                    foreach ($MID as $cid) {
                        $b = new Adherent($this->zdb, $cid, $deps);
                        $members[$cid]['id_adh'] = $cid;
                        $members[$cid]['parent_id'] = $parent_id;
                        $members[$cid]['parent_name'] = $parent_name;
                        $members[$cid]['nom_adh'] = $b->name;
                        $members[$cid]['prenom_adh'] = $b->surname;
                        $members[$cid]['adresse_adh'] = $b->address;
                        $members[$cid]['cp_adh'] = $b->zipcode;
                        $members[$cid]['ville_adh'] = $b->town;
                        $members[$cid]['email_adh'] = $b->email;
                        $members[$cid]['tel_adh'] = $b->phone;
                        $members[$cid]['gsm_adh'] = $b->gsm;
                        $members[$cid]['info_public_adh'] = $b->others_infos;
                        $members[$cid]['info_adh'] = $b->others_infos_admin;
                        $members[$cid]['sexe_adh'] = $b->gender;
                        $members[$cid]['titre_adh'] = $b->titre_adh;
                        $members[$cid]['ddn_adh'] = $b->birthdate;
                        $d = DateTime::createFromFormat('d/m/Y', $b->birthdate);
                        $members[$cid]['age'] = str_replace(
                            '%age',
                            $d->diff(new \DateTime())->y,
                            _T(' (%age years old)')
                        );
                    }
                    break;
                default:
                    $filters->current_page = (int) $value;
                    //assign pagination variables to the template and add pagination links
                    $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
                    $filters->setViewCommonsFilters($this->preferences, $this->view->getSmarty());
                    $m = new Members($filters);
                    $list_members = $m->getMembersList(true);
                    foreach ($list_members as $member) {
                        $mid = $member->id;
                        $c = new Adherent($this->zdb, $mid, $deps);
                        $members[$mid]['id_adh'] = $mid;
                        $has_parent = $c->hasParent();
                        if ($has_parent) {
                            $members[$mid]['parent_id'] = $c->parent->id;
                            $members[$mid]['parent_name'] =  $c->parent->name . " " . $c->parent->surname;
                        } else {
                            $members[$mid]['parent_id'] = '';
                            $members[$mid]['parent_name'] = '';
                        }
                    }
                    break;
            }
        } else {
            $filters->current_page = (int) $value;
            $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);
            $filters->setViewCommonsFilters($this->preferences, $this->view->getSmarty());
            $m = new Members($filters);
            $list_members = $m->getMembersList(true);
            foreach ($list_members as $member) {
                $mid = $member->id;
                $c = new Adherent($this->zdb, $mid, $deps);
                $members[$mid]['id_adh'] = $mid;
                $has_parent = $c->hasParent();
                if ($has_parent) {
                    $members[$mid]['parent_id'] = $c->parent->id;
                    $members[$mid]['parent_name'] =  $c->parent->name . " " . $c->parent->surname;
                } else {
                    $members[$mid]['parent_id'] = '';
                    $members[$mid]['parent_name'] = '';
                }
            }
        }
        $this->session->filter_members  = $filters;
        if (is_array($members)) {
            foreach ($members as $member) {
                $mid = $member['id_adh'];
                // chargement des tarifs, licences et années d'adhésion de l'adhérent (champs dynamiques adherent)
                $a = new Adherent($this->zdb, $mid, $deps);
                foreach ($fields as $field) {
                    $fid = intval($field->getId());
                    $ffield = $a->getDynamicFields()->getValues($fid);
                    if (is_array($ffield)) {
                        foreach ($ffield as $k => $field_data) {
                            $f1 = intval($field->getId());
                            $f2 = 'info_field_' . $f1 . '_1';
                            if (array_key_exists('field_val', $field_data)) {
                                $fv = $field_data['field_val'];
                            } else {
                                $fv = '';
                            }
                            if (array_key_exists('text_val', $field_data)) {
                                $tv = $field_data['text_val'];
                            } else {
                                $tv = '';
                            }
                            $dynadh[$mid][intval($field->getId())]['fname'] = $field->getName();
                            $dynadh[$mid][intval($field->getId())]['fval'] = $fv;
                            $dynadh[$mid][intval($field->getId())]['ftext'] = $tv;
                            $members[$mid][$f2] = $fv;
                        }
                    }
                }
                //
                $grp = $a->groups;
                if (is_array($grp)) {
                    foreach ($grp as $group) {
                        $groups[$mid][$group->getId()] = $group->getIndentName();
                        $members[$mid]['group'][$group->getId()] = $group->getIndentName();
                    }
                }
            }
        }


        $this->session->filter_members = $filters;
        // display page
        $this->view->render(
            $response,
            $page,
            array(
                'page_title' => $page_title,
                'require_dialog' => true,
                'require_calendar' => true,
                'require_mass' => true,
                'members' => $members,
                'list_members' => $list_members,
                'pid' => $parent_id,
                'filter_groups_options' => $groups_list,
                'groups' => $groups,
                'filters' => $filters,
                'dynval' => $dynval,
                'dynadh' => $dynadh,
                'adv_filters' => $filters instanceof AdvancedMembersList,
            )
        );
        return $response;
    }
)->setName('ski_members')->add($authenticate);

//members list filtering
$this->post(
    '/ski_members/filter',
    function ($request, $response) {
        $post = $request->getParsedBody();
        //reintialize filters
        if (isset($this->session->filter_members)) {
            $filters = $this->session->filter_members;
        } else {
            $filters = new MembersList();
        }
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
        $members = new Members($filters);
        if (!$this->login->isAdmin() && !$this->login->isStaff()) {
            if ($this->login->isGroupManager()) {
                $members_list = $members->getManagedMembersList(true);
            } else {
                Analog::log(
                    str_replace(
                        ['%id', '%login'],
                        [$this->login->id, $this->login->login],
                        'Trying to list group members without access from #%id (%login)'
                    ),
                    Analog::ERROR
                );
                throw new Exception('Access denied.');
            }
        } else {
            $members_list = $members->getMembersList(true);
        }

        //assign pagination variables to the template and add pagination links
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);

        $this->session->filter_members = $filters;
        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('ski_members'));
    }
)->setName('ski_filter_members')->add($authenticate);



$this->post(
    __('/family/store', "ski"),
    function ($request, $response, $args) {
        $parent_id = '';
        if ((isset($_POST['save'])) || (isset($_POST['plus']))) {
            foreach ($_POST['members'] as $k => $m) {
                if (isset($_POST['plus'])) {
                    if ($m['id_adh'] == $m['parent_id']) {
                        foreach ($m as $kv => $val) {
                            if ($val != 'null') {
                                $$kv = $val;
                                $posts[$k][$kv] = $val;
                            }
                        }
                        $posts[$k]['parent_id'] = $m->id_adh;
                    }
                } else {
                    foreach ($m as $kv => $val) {
                        if ($val != 'null') {
                            $$kv = $val;
                            $posts[$k][$kv] = $val;
                        }
                    }
                    // un parent n'est pas son propre parent ...
                    if ($m['id_adh'] == $m['parent_id']) {
                        $posts[$k]['parent_id'] = null;
                    }
                }
            }
            $deps = array(
                'picture' => true,
                'groups' => true,
                'dues' => true,
                'parent' => true,
                'children' => true,
                'dynamics' => true,
            );
            foreach ($posts as $kp => $post) {
                $member = new Adherent($this->zdb, null, $deps);
                $member->setDependencies(
                    $this->preferences,
                    $this->members_fields,
                    $this->history
                );
                $success_detected = [];
                $warning_detected = [];
                $error_detected = [];
                $member->load($post['id_adh']);
                // flagging required fields
                $fc = $this->fields_config;
                $fc->setNotRequired('mdp_adh');
                if (
                    $member->hasParent() && !isset($post['detach_parent'])
                    || isset($post['parent_id']) && !empty($post['parent_id'])
                ) {
                    $parent_fields = $member->getParentFields();
                    foreach ($parent_fields as $field) {
                        if ($fc->isRequired($field)) {
                            $fc->setNotRequired($field);
                        }
                    }
                }
                // flagging required fields invisible to members
                if ($this->login->isAdmin() || $this->login->isStaff()) {
                    $fc->setNotRequired('activite_adh');
                    $fc->setNotRequired('id_statut');
                }
                $form_elements = $fc->getFormElements(
                    $this->login,
                    $member->id == '',
                    isset($args['self'])
                );
                $fieldsets = $form_elements['fieldsets'];
                $required = array();
                $disabled = array();
                foreach ($fieldsets as $category) {
                    foreach ($category->elements as $field) {
                        if ($field->required == true) {
                            $required[$field->field_id] = true;
                        }
                        if ($field->disabled == true) {
                            $disabled[$field->field_id] = true;
                        } elseif (!isset($post[$field->field_id])) {
                            switch ($field->field_id) {
                                //unchecked booleans are not sent from form
                                case 'bool_admin_adh':
                                case 'bool_exempt_adh':
                                case 'bool_display_info':
                                    $post[$field->field_id] = 0;
                                    break;
                            }
                        }
                    }
                }
                if (isset($_POST['plus'])) {
                    $new = true;
                    $post['id_adh'] = '';
                    $post['prenom_adh'] = '?';
                    $post['ddn_adh'] = '01/01/2000';
                    $post['date_crea_adh'] = date("Y-m-d");
                    $post['email_adh'] = '';
                } else {
                    $new = false;
                }
                $real_requireds = array_diff(array_keys($required), array_keys($disabled));
                $valid = $member->check($post, $required, $disabled);
                $member->addesse_adh = $post['adresse_adh'];
                if ($valid !== true) {
                    $error_detected = array_merge($error_detected, $valid);
                }
                if (count($error_detected) == 0) {
                    //all goes well, we can proceed
                    $store = $member->store();
                    if ($store === true) {
                        //member has been stored :)
                        if ($new) {
                            $success_detected[] = _T("New member has been successfully added.");
                        } else {
                            $success_detected[] = _T("Member account has been modified.") . " (" . $post['id_adh'] . ")";
                        }
                    } else {
                        //something went wrong :'(
                        $error_detected[] = _T("An error occurred while storing the member.");
                    }
                }
                //if (count($error_detected) == 0) {
                //    $files_res = $member->handleFiles($_FILES);
                //    if (is_array($files_res)) {
                //        $error_detected = array_merge($error_detected, $files_res);
                //    }
                //}
                if (count($error_detected) > 0) {
                    foreach ($error_detected as $error) {
                        if (strpos($error, '%member_url_') !== false) {
                            preg_match('/%member_url_(\d+)/', $error, $matches);
                            $url = $this->router->pathFor('member', ['id' => $matches[1]]);
                            $error = str_replace(
                                '%member_url_' . $matches[1],
                                $url,
                                $error
                            );
                        }
                        $this->flash->addMessage(
                            'error_detected',
                            $error
                        );
                    }
                }

                if (count($warning_detected) > 0) {
                    foreach ($warning_detected as $warning) {
                        $this->flash->addMessage(
                            'warning_detected',
                            $warning
                        );
                    }
                }
                if (count($success_detected) > 0) {
                    foreach ($success_detected as $success) {
                        $this->flash->addMessage(
                            'success_detected',
                            $success
                        );
                    }
                    //$success_detected = "Member modified";
                    //$this->flash->addMessage('success_detected', $success_detected);
                }
            }
        }
        if ($parent_id != '') {
            return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_members') . "/edit/" . $parent_id
            );
        } else {
            return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_members') . "/edit/" . $_POST['parent_id']
            );
        }
    }
)->setName('ski_store_family')->add($authenticate);



$this->get(
    __('/ski_family', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
                    $post['id_adh'] = '';
                    $post['parent_id'] = '';
                    $post['nom_adh'] = '?';
                    $post['prenom_adh'] = '?';
                    $post['adresse_adh'] = '?';
                    $post['cp_adh'] = '?';
                    $post['titre_adh'] = '1';
                    $post['sexe_adh'] = '1';
                    $post['ville_adh'] = '?';
                    $post['email_adh'] = '';
                    $post['tel_adh'] = '?';
                    $post['gsm_adh'] = '';
                    $post['ddn_adh'] = '01/01/2000';
                    $post['date_crea_adh'] = date("Y-m-d");

            $deps = array(
                'picture' => true,
                'groups' => true,
                'dues' => true,
                'parent' => true,
                'children' => true,
                'dynamics' => true,
            );
                $member = new Adherent($this->zdb, null, $deps);
                $member->setDependencies(
                    $this->preferences,
                    $this->members_fields,
                    $this->history
                );
                $member->load($post['id_adh']);
                // flagging required fields
                $fc = $this->fields_config;
                $fc->setNotRequired('mdp_adh');
                // flagging required fields invisible to members
        if ($this->login->isAdmin() || $this->login->isStaff()) {
            $fc->setNotRequired('activite_adh');
            $fc->setNotRequired('id_statut');
        }
                $form_elements = $fc->getFormElements(
                    $this->login,
                    $member->id == '',
                    isset($args['self'])
                );
                $fieldsets = $form_elements['fieldsets'];
                $required = array();
                $disabled = array();
        foreach ($fieldsets as $category) {
            foreach ($category->elements as $field) {
                if ($field->required == true) {
                    $required[$field->field_id] = true;
                }
                if ($field->disabled == true) {
                    $disabled[$field->field_id] = true;
                } elseif (!isset($post[$field->field_id])) {
                    switch ($field->field_id) {
                        //unchecked booleans are not sent from form
                        case 'bool_admin_adh':
                        case 'bool_exempt_adh':
                        case 'bool_display_info':
                            $post[$field->field_id] = 0;
                            break;
                    }
                }
            }
        }
                $real_requireds = array_diff(array_keys($required), array_keys($disabled));
                $valid = $member->check($post, $required, $disabled);
        if ($valid == true) {
            //all goes well, we can proceed
            $store = $member->store();
            $id = 0;
            if ($store === true) {
                //member has been stored :)
                    $success = _T("New member has been successfully added.");
            } else {
                //something went wrong :'(
                $error = _T("An error occurred while storing the member.");
            }
            $this->flash->addMessage(
                'success_detected',
                $success
            );
        } else {
            $this->flash->addMessage(
                'error_detected',
                $error
            );
        }

                    
            return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('ski_members') . "/edit/" . $member->id
            );
    }
)->setName('ski_family')->add($authenticate);
$this->get(
    __('/ski_print_member', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $form_id = intval($_POST['form_id']);
    }
)->setName('ski_print_member')->add($authenticate);
