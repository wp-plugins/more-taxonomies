<?php

class more_taxonomies_settings extends more_plugins_common_object_beta {

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
		
			$a = attribute_escape($_POST['label']);
			$b = attribute_escape($_POST['singular_label']);
			if (!$a && !$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need both a plural and singular label for the taxonomy!', 'more-plugins')); 
			}
			if (!$a) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need a name for the taxonomy!', 'more-plugins')); 
			}
			if (!$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__("You need a singular name for the taxonomy!", 'more-plugins')); 
			}
			// Default slug
			if (!$_POST['rewrite_base']) $_POST['rewrite_base'] = sanitize_title($a);
			// Handle change of name
//			$name_old = sanitize_title($_POST['name']);
//			if ($name_old && $name_old != $name) {
//				unset($this->data[$name_old]);
//			}
			$_POST['name'] = $name;



		}
		
		// If all is OK
		return true;
	}
	function read_data() {
		global $more_taxonomies;

		$data = $more_taxonomies->read_data();

		if (empty($data)) $data = $this->object_to_array($wp_taxonomies);
		
		if ($data['link_category']) $data['link_category']['label'] = __('Link Categories');

		return $data;
		
	}
	function default_data () {
		global $wp_taxonomies;
		return $this->object_to_array($wp_taxonomies);
	}	
	function get_post_types() {
		global $wp_post_types;
		$ret = array();
		foreach ($wp_post_types as $key => $pt) $ret[$key] = $pt->label;
		return $ret;
	}
	function get_post_type_taxonomies() {
		global $wp_post_types;
		$arr = array();
		foreach ($wp_post_types as $key => $type) $arr[$key] = $type->taxonomies;
// 		foreach ($this->data as $key)

		return $arr[$this->keys[0]];
	}
	
} // End class


?>