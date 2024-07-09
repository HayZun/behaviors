<?php
/**
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivoglpi plugin for GLPI.

 xivoglpi is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 xivoglpi is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with xivoglpi. If not, see <http://www.gnu.org/licenses/>.

 @package   xivoglpi
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2023 xivoglpi plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/xivoglpi
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_xivoglpi() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   Plugin::registerClass('PluginXivoglpiConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['xivoglpi'] = 'front/config.form.php';

   $PLUGIN_HOOKS['csrf_compliant']['xivoglpi'] = true;

}


function plugin_version_xivoglpi() {

   return ['name'           => __('Xivoglpi', 'xivoglpi'),
           'version'        => '0.0.1',
           'license'        => 'AGPLv3+',
           'author'         => 'Remi Collet, Nelly Mahu-Lasson',
           'homepage'       => 'https://github.com/yllen/xivoglpi',
           'minGlpiVersion' => '10.0.5',
           'requirements'   => ['glpi' => ['min' => '10.0.5',
                                           'max' => '10.1.0']]];
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_xivoglpi_check_config($verbose=false) {
   return true;
}
