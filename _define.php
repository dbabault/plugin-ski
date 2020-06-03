<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Configuration file for ObjectsLend plugin
 *
 * PHP version 5
 *
 * Copyright © 2013-2016 Mélissa Djebel
 * Copyright © 2017-2018 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * ObjectsLend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ObjectsLend is distributed in the hope that it will be useful,
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
 */

$this->register(
    'Galette Ski ',             //Name
    'Manage ski rent',       //Short description
    'Daniel BABAULT', //Author
    '1.0-dev',                          //Version
    '0.9.3',                            //Galette version compatibility
    'ski',                      //routing name and translation domain
    '2020-02-24',                       //Date
    [   //Permissions needed - not yet implemented
      'ski_do_add_form'   => 'staff',
      'ski_do_add_object' => 'staff',
      'ski_dashboard'     => 'staff',
      'ski_form_list'     => 'staff',
      'ski_preferences'   => 'staff',
      'ski_filter_form'  => 'staff',
      'ski_remove_form'   => 'staff',
      'ski_form_printform' => 'staff',
      'ski_done_form' => 'staff',
      'ski_members' => 'staff',
      'ski_member' => 'staff',
      'ski_family' => 'staff',
      'filter-ski_members' => 'staff',
      'ski_form'          => 'staff'
    ]
);
