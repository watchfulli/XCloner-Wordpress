<?php
/**
 * XCloner - Backup and Restore backup plugin for Wordpress
 *
 * class-xcloner-deactivator.php
 * @author Liuta Ovidiu <info@thinkovi.com>
 *
 *        This program is free software; you can redistribute it and/or modify
 *        it under the terms of the GNU General Public License as published by
 *        the Free Software Foundation; either version 2 of the License, or
 *        (at your option) any later version.
 *
 *        This program is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *        GNU General Public License for more details.
 *
 *        You should have received a copy of the GNU General Public License
 *        along with this program; if not, write to the Free Software
 *        Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *        MA 02110-1301, USA.
 *
 * @link https://github.com/ovidiul/XCloner-Wordpress
 *
 * @modified 7/31/18 3:28 PM
 *
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Xcloner
 * @subpackage Xcloner/includes
 * @author     Liuta Ovidiu <info@thinkovi.com>
 * @link       http://www.thinkovi.com
 */

class Xcloner_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		global $xcloner_plugin;

		if (is_a($xcloner_plugin, 'Xcloner')) {
			try {
				$xcloner_plugin->get_xcloner_filesystem()->cleanup_tmp_directories();
			}catch (Exception $e) {
				$xcloner_plugin->trigger_message_notice($e->getMessage());
			}

			$xcloner_scheduler = $xcloner_plugin->get_xcloner_scheduler();
			$xcloner_scheduler->deactivate_wp_cron_hooks();
		}
	}

}
