<?php
/**
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2023 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_behaviors() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   Plugin::registerClass('PluginBehaviorsConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['behaviors'] = 'front/config.form.php';

   $PLUGIN_HOOKS['csrf_compliant']['behaviors'] = true;

}


function plugin_version_behaviors() {

   return ['name'           => __('Behaviours', 'behaviors'),
           'version'        => '2.7.3',
           'license'        => 'AGPLv3+',
           'author'         => 'Remi Collet, Nelly Mahu-Lasson',
           'homepage'       => 'https://github.com/yllen/behaviors',
           'minGlpiVersion' => '10.0.5',
           'requirements'   => ['glpi' => ['min' => '10.0.5',
                                           'max' => '10.1.0']]];
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_behaviors_check_config($verbose=false) {
   return true;
}
