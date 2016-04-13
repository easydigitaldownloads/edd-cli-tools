<?php
/**
 * Plugin Name: Easy Digital Downloads - WP_CLI Tools
 * Plugin URI: https://easydigitaldownloads.com
 * Description: Adds a set of tools for managing EDD through WP_CLI
 * Author: Pippin Williamson and Company
 * Author URI: https://easydigitaldownloads.com
 * Version: 0.1
 * Text Domain: easy-digital-downloads
 * Domain Path: languages
 *
 * Easy Digital Downloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Easy Digital Downloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Digital Downloads. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package EDDCLITools
 * @category Extension
 * @author Chris Klosowski
 * @version 0.1
 */

class EDD_CLI_Tools {
	private static $instance;
	private $plugin_file;
	private $plugin_dir;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_CLI_Tools ) ) {
			self::$instance = new EDD_CLI_Tools;
		}

		return self::$instance;
	}

	private function __construct() {
		$this->paths();
		$this->includes();
	}

	private function paths() {
		$this->plugin_file = __FILE__;
		$this->plugin_dir  = trailingslashit( plugin_dir_path( $this->plugin_file ) );
	}

	private function includes() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once $this->plugin_dir . 'includes/class-edd-cli-tools.php';
		}
	}



}

function edd_cli_tools() {
	return EDD_CLI_Tools::instance();
}
add_action( 'plugins_loaded', 'edd_cli_tools', 10 );
