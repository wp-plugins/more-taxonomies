<?php


class more_taxonomies_object {
	
	var $settings;

	function more_taxonomies_object ($settings) {
		$this->settings = $settings;
		add_action('init', array(&$this, 'load_taxonomies'));
	}
	/*
	function load_data() {
		global $_more_taxonomies_registered;
		$data = (array) get_option($this->settings['option_key']);


		return $data;
	
	}
	*/
	function read_data() {
		global $wp_taxonomies;

		$data = get_option($this->settings['option_key'], array());
		
		return $data;
	
	}
	function load_taxonomies() {	
		global $wp_roles;
		$data = $this->read_data();

		// Give More Types priority
		$plugins = get_option( 'active_plugins', array());
		$more_types = 'more-types/more-types.php';

		$caps = array(
			'manage_cap' => 'manage_%', 
			'edit_cap' => 'edit_%', 
			'delete_cap' => 'delete_%'
		);

		foreach ($data as $name => $taxonomy) {
				
			foreach ($caps as $cap_key => $template) {
				// Create the capability name
				$capability = str_replace('%', $name, $template);

				// Add capabilities to the post type if there are defined roles
				if (!empty($taxonomy['more_' . $cap_key])) 
					$taxonomy[$cap_key] = $capability;

				// Add capability!
				foreach ((array) $taxonomy['more_' . $cap_key] as $role) 
					if (is_object($wp_roles))
						$wp_roles->add_cap($role, $capability);
			}	
		
			// Configure slug
			if ($taxonomy['rewrite'] && ($slug = $taxonomy['rewrite_base'])) 
				$taxonomy['rewrite'] = array('slug' => $slug);
				
			// If more types is installed don't associate with any particular post type. 
			if (in_array($more_types, $plugins)) {
				register_taxonomy($name, '', $taxonomy);
			} else {
				// Link taxonomy to particular objects
				foreach ((array) $taxonomy['object_type'] as $type) {
					register_taxonomy($name, '', $taxonomy);
				}
			}
		}

	}
	
} // End class


?>