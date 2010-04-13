<?php


class more_types_object {
	
	var $settings, $saved, $wp_post_types;

	function more_types_object ($settings) {
		$this->settings = $settings;
		add_action('init', array(&$this, 'init_post_types'));
		add_filter('template_redirect', array(&$this, 'template_redirect'), 9);
	
	
	}
	function template_redirect() {
		if (is_single()) {
			$pt = get_post_type();
			$mt = $this->read_data();
			if ($template = $mt[$pt]['template']) {
				$file = TEMPLATEPATH . '/' .$template;
				if (file_exists($file)) {
					include($file);
					exit(0);
				}
				return false;
			}
		}
	}
	function read_data() {

		// Get data from db
		$data = get_option($this->settings['option_key'], array());

		foreach ((array) $this->saved as $key => $type) {
				$data[$key] = $type;
				$data[$key]['saved'] = true;
		}

		return $data;
	}
	function init_post_types() {
 		global $wp_roles, $wp_post_types, $_wp_post_type_features;
 		$this->wp_post_types = $wp_post_types;
 		$this->wp_post_type_features = $_wp_post_type_features;

//		print_r($_wp_post_type_features);

		// Read in saved files	
		$dir = WP_PLUGIN_DIR . '/more-types/saved/';
		$ls = scandir($dir);
		$pts = array();
		foreach ($ls as $l) if (strpos($l, '.php')) $pts[] = $l;
		foreach ($pts as $file) require($dir . $file);
				
		$pages = $this->read_data(); // 

		//print_r($pages);

		if (!empty($pages)) $wp_post_types = array();

		$defaults = array('post', 'page', 'revision', 'media');

		$caps = array(
			'edit_cap' => 'edit_%',
			'edit_type_cap' => 'edit_%s', 
			'edit_others_cap' => 'edit_others_%s',
			'publish_others_cap' => 'publish_%s',
			'read_cap' => 'read_%',
			'delete_cap' => 'delete_%'
		);
/*
 * edit_cap - The capability that controls editing a particular object of this post type. Defaults to "edit_$capability_type" (edit_post).
 * edit_type_cap - The capability that controls editing objects of this post type as a class. Defaults to "edit_ . $capability_type . s" (edit_posts).
 * edit_others_cap - The capability that controls editing objects of this post type that are owned by other users. Defaults to "edit_others_ . $capability_type . s" (edit_others_posts).
 * publish_others_cap - The capability that controls publishing objects of this post type. Defaults to "publish_ . $capability_type . s" (publish_posts).
 * read_cap - The capability that controls reading a particular object of this post type. Defaults to "read_$capability_type" (read_post).
 * delete_cap - The capability that controls deleting a particular object of this post type. Defaults to "delete_$capability_type" (delete_post).
 */


		foreach($pages as $name => $page) {

			$options = array();

			foreach ($caps as $cap_key => $template) {
				// Create the capability name
				$capability = str_replace('%', $name, $template);

				// Add capabilities to the post type if there are defined roles
				if (!empty($page['more_' . $cap_key])) 
					$options[$cap_key] = $capability;

				// Add capability!
				foreach ((array) $page['more_' . $cap_key] as $role) {				
					$wp_roles->add_cap($role, $capability);
				}

			}

			foreach ((array) $this->settings['fields']['var'] as $field)
				$options[$field] = $page[$field];
			foreach ((array) $this->settings['fields']['array'] as $field)
				$options[$field] = $page[$field];

			if ($page['rewrite_bool'] && ($rw = $page['rewrite_slug'])) $options['rewrite'] = array('slug' => $rw);
			else $options['rewrite'] = false;
						
			// Is this one of the default ones?
			// if (in_array($name, $defaults)) $options['_builtin'] = true;

			register_post_type($name, $options);
			

       	 	foreach ((array) $page['taxonomies'] as $taxonomy)  {
        		register_taxonomy_for_object_type($taxonomy, $name);
			}
		
		}

		//exit();

	}
	
} // End class


?>