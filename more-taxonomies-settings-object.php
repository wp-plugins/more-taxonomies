<?php

class more_taxonomies_admin extends more_plugins_admin_object_sputnik_3 {

	function validate_sumbission() {
		if ($this->action == 'save') {
				
			$a = attribute_escape($_POST['labels,name']);
			$b = attribute_escape($_POST['labels,singular_name']);
			if (!$a && !$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need both a plural and singular label for the taxonomy!', 'more-plugins')); 
			}
			if (!$a) {
				$this->set_navigation('taxonomy');
				return $this->error(__('You need to enter a plural name for the taxonomy!', 'more-plugins')); 
			}
			if (!$b) {
				$this->set_navigation('taxonomy');
				return $this->error(__("You need to enter a singular name for the taxonomy!", 'more-plugins')); 
			}
			// Default slug
			if (!$_POST['rewrite_base']) $_POST['rewrite_base'] = sanitize_title($a);
			// Handle change of name
//			$name_old = sanitize_title($_POST['name']);
//			if ($name_old && $name_old != $name) {
//				unset($this->data[$name_old]);
//			}
			$defaults = array('Category' => 'category', 'Post Tags' => 'post_tag', 'Navigation Menu' => 'nav_menu', 'Category' => 'link_category');
			$_POST['index'] = $this->get_index('labels,singular_name');

		}
		
		// If all is OK
		return true;
	}
	function read_data() {
		global $more_taxonomies, $wp_taxonomies;

		return $more_taxonomies->load_data();

		$data = $more_taxonomies->load_data();

		if ($this->action != 'add') $this->data = $data;
		
//		if (empty($data)) $data = $this->object_to_array($wp_taxonomies);		
//		if ($data['link_category']) $data['link_category']['label'] = __('Link Categories');

		return $data;
		
	}
	function default_data () {
		global $wp_taxonomies;
		return $this->object_to_array($wp_taxonomies);
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