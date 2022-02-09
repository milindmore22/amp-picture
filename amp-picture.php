<?php
/**
 * AMP plugin name compatibility plugin bootstrap.
 *
 * @package   Google\AMP_Picture_Compat
 * @author    milindmore22, rtCamp
 * @license   GPL-2.0-or-later
 * @copyright 2020 Google Inc.
 *
 * @wordpress-plugin
 * Plugin Name: AMP Picture Compat
 * Plugin URI: https://wpindia.co.in/
 * Description: Plugin add compatibility for picture element, to be removed once <a href="https://github.com/ampproject/amp-wp/issues/6676">#6676</a>
 * Version: 0.1
 * Author: milindmore22, Google
 * Author URI: https://yoursite.com
 * License: GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Google\AMP_Picture_Compat;

/**
 * Add sanitizers to convert non-AMP functions to AMP components.
 *
 * @see https://amp-wp.org/reference/hook/amp_content_sanitizers/
 */
add_filter( 'amp_content_sanitizers', __NAMESPACE__ . '\filter_sanitizers' );

/**
 * Add sanitizer to fix up the markup.
 *
 * @param array $sanitizers Sanitizers.
 * @return array Sanitizers.
 */
function filter_sanitizers( $sanitizers ) {
	require_once __DIR__ . '/sanitizers/class-sanitizer.php';
	$sanitizers[ __NAMESPACE__ . '\Sanitizer' ] = array();
	return $sanitizers;
}
