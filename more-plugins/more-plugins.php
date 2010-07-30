<?php

$more_plugins = 'MORE_PLUGINS_SPUTNIK_3';
if (!defined($more_plugins)) {
	
	class more_plugins_object_sputnik_3 {
	
	
		function more_plugins_object_sputnik_3($settings) {
			$this->settings = $settings;
			$this->slug = sanitize_title($settings['name']);
			$this->init($settings);
			$this->filter = str_replace('-', '_', sanitize_title($this->settings['name'])) . '_saved';
			$this->data_default = array();
			$this->data_modified = array();
			$this->data_loaded = array();
		}
		function init($settings) {
		}
		function object_to_array($data) {
		
   			if (is_object($data)) $data = get_object_vars($data);
    		return is_array($data) ? array_map(array(&$this, 'object_to_array'), $data) : $data;
		/*
			if (is_array($data) || is_object($data)) {
				$result = array(); 
				foreach($data as $key => $value) $result[$key] = $this->object_to_array($value); 
    			return $result;
  			}
			return $data;
		*/
		}
		function get_data($keys = array()) {
			if (empty($this->data_loaded)) $this->data_loaded = $this->load_data();
			if (!empty($keys)) {
				$ret = array();
				foreach ($keys as $key) {
					foreach ((array) $this->data_loaded[$key] as $name => $var) {					
						$ret[$name] = $this->data_loaded[$key][$name];
					}
				}
				return $ret;	
			}
			return $this->data_loaded;
		}
		function load_data($data = array()) {
			$data['_plugin'] = get_option($this->settings['option_key'], array());

			$data['_plugin_saved'] = $this->saved_data();

			foreach ((array) $this->data_modified as $key => $item) {
				// Remove the defaults
				if (array_key_exists($key, $this->data_default)) 
					unset($this->data_modified[$key]);
				/*
				if (array_key_exists($key, $data['_plugin'])) 
					unset($this->data_modified[$key]);		
				if (array_key_exists($key, (array) $data['_plugin_saved'])) 
					unset($this->data_modified[$key]);					
				*/
			}
			$data['_other'] = $this->object_to_array($this->data_modified);
			$data['_default'] = $this->object_to_array($this->data_default);
			$this->data_loaded = $data;
			return $data;
		
		}
		function saved_data() {
			return apply_filters($this->filter, $saved);

			$saved = array();
			$saved = apply_filters($this->filter, $saved);
			foreach ($saved as $key => $type) {
				$data[$key] = $type;
				$data[$key]['file'] = true;
			}
			return $data;		
		}
		function elsewhere_data($data, $wpdata) {
			// Get the stuff defined elsewhere
			foreach ($wpdata as $key => $item) {
				if (in_array($key, (array) $this->settings['default_keys'])) continue;
				$item = $this->object_to_array($item);
				$data[$key] = $item;
				$data[$key]['file'] = true;
				$data[$key]['other'] = true;
			}
			return $data;
		}
		
		
		/*
		**		POPULATE
		**
		*/
		function populate ($page, $options = array()) {

			// These are the single value variables
			foreach ((array) $this->settings['fields']['var'] as $field)
				if (array_key_exists($field, (array) $page)) 
					$options[$field] = $page[$field];
				else $options[$field] = false;
				
			// Arrays, may be associative
			foreach ((array) $this->settings['fields']['array'] as $k => $f) {	

				if (!is_array($f)) {
					 if (array_key_exists($f, (array) $page)) 
					 	$options[$f] = $page[$f];
					// $options[$f] = (array_key_exists($f, (array) $page)) ? $page[$f] : array();
				} else {
					foreach((array) $f as $f2) {
						if (array_key_exists($f2, (array) $page[$k])) 
							$options[$k][$f2] = $page[$k][$f2];
						// $options[$k][$f2] = (array_key_exists($f2, (array) $page[$k])) ? $page[$k][$f2] : false;				
					}
				}
			}
			return $options;			
		}	


	
	
	}
}

define($more_plugins, true);


?>