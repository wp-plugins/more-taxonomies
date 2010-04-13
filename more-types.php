<?php
/*
Plugin Name: More Types
Version: 1.0&beta;2
Author URI: http://labs.dagensskiva.com/
Plugin URI: http://labs.dagensskiva.com/
Description:  Add more post types to your WordPress installation. 
Author: Henrik Melin, Kal Ström

	USAGE:

	See http://labs.dagensskiva.com/

	LICENCE:

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    
*/

// Reset
if (0) update_option('more_types', array());

// Plugin settings
$fields = array(
		'var' => array('description', 'menu_icon', 'public', 'label', 'singular_label', 'name', 'exclude_from_search', 'publicly_queryable', 'show_ui', 'inherit_type', 'capability_type', 'hierarchical', 'template', 'rewrite_bool', 'rewrite_slug'),
		'array' => array('supports', 'more_edit_type_cap', 'more_edit_cap', 'more_edit_others_cap', 'more_publish_others_cap', 'more_read_cap', 'more_delete_cap', 'taxonomies')
);
$default = array(
		'show_ui' => true,
		'publicly_queryable' => true,
		'hierarchical' => false,
		'public' => true,
		'capability_type' => 'post', 
		'supports' => array('title', 'editor'),
		'rewrite_bool' => false,
);
$settings = array(
		'name' => 'More Types', 
		'option_key' => 'more_types',
		'fields' => $fields,
		'default' => $default,
);

// Always on components
include('more-types-object.php');
$more_types = new more_types_object($settings);

// Load admin components
if (is_admin()) {
	include('more-plugins/more-plugins-common.php');
	// include(ABSPATH . '/wp-content/plugins/more-plugins-common.php');
	include('more-types-settings-object.php');
	$more_types_settings = new more_types_settings($settings);
}

// Function to register programatically
function more_types__($t) {
	global $more_types;
	$r = json_decode($t, true);
	foreach($r as $key => $b)
		$more_types->saved[$key] = $b;
}


?>
