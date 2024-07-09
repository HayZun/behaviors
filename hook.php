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
 @copyright Copyright (c) 2010-2022 xivoglpi plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/xivoglpi
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
 */


function plugin_xivoglpi_install() {

   $migration = new Migration(270);

   // No autoload when plugin is not activated
   require_once('inc/config.class.php');
   PluginXivoglpiConfig::install($migration);

   $migration->executeMigration();

   return true;
}


function plugin_xivoglpi_uninstall() {

   // No autoload when plugin is not activated
   require 'inc/config.class.php';

   $migration = new Migration(270);

   PluginxivoglpiConfig::uninstall($migration);

   $migration->executeMigration();

   return true;
}
