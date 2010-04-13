<?php

class more_types_settings extends more_plugins_common_object_beta {

	function add_actions () {
		add_action('admin_menu', array(&$this, 'admin_menu_prune'));
	}

	function admin_menu_prune() {
		global $menu;
		$types = $this->read_data();
		$defaults = array('post' => 5, 'page' => 20, 'attachment' => 10);
		foreach ($defaults as $key => $nbr) {
			// Remove default post types that do not exist
			if (!array_key_exists($key, $types)) unset($menu[$nbr]);
		}
	

	}

	function get_boxes() {
		// POST 'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions')
		// PAGE 'supports' => array('title', 'editor', 'author', 'thumbnail', 'page-attributes', 'custom-fields', 'comments', 'revisions')

		$divs = array(
			'title' => __('Title'),
			'editor' => __('Editor'),
			'post-thumbnails' => __('Thumbnail'),
			'excerpt' => __('Excerpt'),
			'custom-fields' => __('Custom Fields'),
			'author' => __('Author'),
			'comments' => __('Comments'),
			'revisions' => __('Revisions'),
			'page-attributes' => __('Page Attributes'),
		);
		return $divs;
	}

	function validate_sumbission() {
		if ($this->action == 'save') {
		
			// Set the index to save to
			$name = sanitize_title($_POST['singular_label']);
			/*
			if (array_key_exists($name, $this->data)) {
				$this->set_navigation('taxonomy');
				return $this->error(__('This taxonomy already exists!', 'more-plugins'));
			}
			*/

			$_POST['name'] = $name;

			$rwslug = attribute_escape($_POST['rewrite_slug']);
			if (!$rwslug) $_POST['rewrite_slug'] = sanitize_title(attribute_escape($_POST['singular_label']));
		
			$a = attribute_escape($_POST['label']);
			$b = attribute_escape($_POST['singular_label']);
			if (!$a && !$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need both a plural and singular label for the post type!', 'more-plugins')); 
			}
			if (!$a) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need a label for the post type!', 'more-plugins')); 
			}
			if (!$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__("You need a singular label for the post type! E.g. 'Cat' for the taxonomy 'Cats'", 'more-plugins')); 
			}

		}
		
		// If all is OK
		return true;
	}
	function read_data() {
		global $more_types;
		
		$data = $more_types->read_data();
		
		// Default values
		if (empty($data)) {
			$defaults = $this->object_to_array($more_types->wp_post_types);
			foreach ($defaults as $key => $default) {
				$data[$key] = $default;
				$data[$key]['supports'] = array_keys($more_types->wp_post_type_features[$key]);
				$data[$key]['taxonomies'] = get_object_taxonomies($key);
			}
		}
		
		return $data;
	}
	
	function default_data () {
		global $wp_post_types;
		return $this->object_to_array($wp_post_types);
	}	
	function get_post_types() {
		global $wp_post_types;
		$ret = array();
		foreach ($wp_post_types as $key => $pt) $ret[$key] = $pt->label;
		return $ret;
	}
	function get_templates() {
		$templates = get_page_templates();
		foreach ((array) $templates as $k => $t) {
			$arr[$t] = $k;
		}
		array_unshift($arr, '');
		return $arr;
	}
	
} // End class


?>