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
include 'form.routes.php';
include 'member.routes.php';

//galette's dashboard
$this->get(
    __('/dashboard', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
        $pt = _T("Dashboard");
        $params = [
            'page_title' => $pt,
            'contentcls' => 'desktop',
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

$this->get(
    __('/ski_preferences', "ski"),
    function ($request, $response, $args) use ($module, $module_id) {
    }
)->setName('ski_preferences')->add($authenticate);
