<?php
$more_common = 'MORE_PLUGINS_COMMON_BETA';
if (!defined($more_common)) {

 	class more_plugins_common_object_beta {
		var $name, $slug, $settings_file, $dir, $options_url, $option_key, $data, $url;
	
		var $action, $navigation, $message, $error;
		/*
		**
		**
		*/
		function more_plugins_common_object_beta ($settings) {

			$this->name = $settings['name'];
			$this->slug = sanitize_title($settings['name']);
			$this->fields = $settings['fields'];
			if (isset($settings['settings_file'])) 
				$this->settings_file = $settings['settings_file'];
			else $this->settings_file = $this->slug . '-settings.php';
			$this->dir = WP_PLUGIN_DIR . '/' . $this->slug . '/';
			$this->url = get_option('siteurl') . '/wp-content/plugins/' . $this->slug . '/';
			$this->options_url = 'options-general.php?page=' . $this->slug;
			$this->settings_url = $this->options_url;
			$this->option_key = $settings['option_key'];
			$this->default = $settings['default'];

			// Create Settins Menu
			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_action('admin_head', array(&$this, 'admin_head'));

			// Handle requests			
			add_action('settings_page_' . $this->slug, array(&$this, 'request_handler'));
			
			// Add JS & css on settings page
			add_action('admin_head-settings_page_' . $this->slug, array(&$this, 'settings_head'));
			add_action('admin_print_scripts-settings_page_' . $this->slug, array(&$this, 'settings_init'));
			
			add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);

			add_action('init', array(&$this, 'admin_init'), 11);
			
//			add_action('admin_print_scripts-' . $page, 'my_plugin_admin_styles');

			
			$this->add_actions();

			// $this->data = $this->read_data();
		}
		function admin_init() {
			$this->data = $this->read_data();
		}
		function add_actions() {
			// empty
		}
		function plugin_row_meta ($links, $file) {
			if (strpos('padding' . $file, $this->slug)) {
				$links[] = '<a href="' . $this->settings_url . '">' . __('Settings','more-plugins') . '</a>';
				$links[] = '<a href="http://labs.dagensskiva.com/forum/forum/' . $this->slug . '/">' . __('Support','more-plugins') . '</a>';
				$links[] = '<a href="http://labs.dagensskiva.com/donate/">' . __('Donate','sitemap') . '</a>';
			}
			return $links;
		}
		
		/*
		**
		**
		*/
		function admin_menu () {
			add_options_page($this->name, $this->name, 8, $this->slug, array(&$this, 'options_page'));
		}
		
		/*
		**
		**
		*/
		function admin_head () {		
		
		}
		
		function is_plugin_installed() {
		
		}
		/*
		**
		**
		*/
		function options_page() {
			$this->options_page_wrapper_header();
			
			// Errors trump notifications
			if ($this->error) echo '<div class="updated fade error"><p><strong>' . $this->error . '</strong></p></div>';
			else if ($this->message) echo '<div class="updated fade"><p><strong>' . $this->message . '</strong></p></div>';

			// Load the settings file
			if (!$this->footed) 
				if ($this->settings_file)
					require($this->dir . $this->settings_file);
			$this->options_page_wrapper_footer();
		}
		
		function export_data() {
			$this->options_page_wrapper_header();
			$data = $this->get_data();
			foreach (array_reverse($this->keys) as $key) $data = array($key => $data);
			$function = str_replace('-', '_', $this->slug);
			$export = '<?php ' . $function . '__(\'' . json_encode($data) . '\'); ?>';
			$filename = implode('-', $this->keys) . '.php';
			$dir = $this->dir . 'saved/';

			if (false) {			
				$file = $this->dir . 'registered/' . $filename;
				 if (!$handle = fopen($file, 'a')) {
					echo "Cannot open file ($filename)";
					exit;
				}
				// Write $somecontent to our opened file.
				if (fwrite($handle, $export) === FALSE) {
					echo "Cannot write to file ($filename)";
					exit;
				}
				fclose($handle);
			} 

			$this->navigation_bar(array('Export'));
			?>	
				<p><?php printf(__('The %s plugin can read objects from a file. The default location for these files is in the %s directory. To create a file object, copy the text below (<code>CTRL/CMD + c</code>), paste it into a text file and save it as %s to the aforementioned directory. If an object exists both in the %s settings and as a file, the file will override the data stored in the database.', 'more-plugins'), $this->name, "<code>$dir</code>", "<code>$filename</code>", $this->name); ?></p>
				<p><textarea rows="15" class="large-text readonly" name="rules" id="rules" readonly="readonly"><?php echo esc_html($export); ?></textarea></p>
			<?php
			$this->options_page_wrapper_footer();
		}
		
		/*
		**
		**
		*/
		function get_data($s = array()) {
			if (empty($s)) $s = $this->keys;
			if (count($s) == 0) return $this->data;
			if (count($s) == 1) return $this->data[$s[0]];
			if (count($s) == 2) return $this->data[$s[0]][$s[1]];
			if (count($s) == 3) return $this->data[$s[0]][$s[1]][$s[2]];
			if (count($s) == 4) return $this->data[$s[0]][$s[1]][$s[2]][$s[3]];
			return $this->data;
		}		
		function set_data($value, $s = array()) {
			if (empty($s)) $s = $this->keys;
			if (count($s) == 0) $this->data = $value;
			if (count($s) == 1) $this->data[$s[0]] = $value;
			if (count($s) == 2) $this->data[$s[0]][$s[1]] = $value;
			if (count($s) == 3) $this->data[$s[0]][$s[1]][$s[2]] = $value;
			if (count($s) == 4) $this->data[$s[0]][$s[1]][$s[2]][$s[3]] = $value;
			return $this->data;
		}
		function unset_data($s = array()) {
			if (empty($s)) $s = $this->keys;
			if (count($s) == 1) unset($this->data[$s[0]]);
			if (count($s) == 2) unset($this->data[$s[0]][$s[1]]);
			if (count($s) == 3) unset($this->data[$s[0]][$s[1]][$s[2]]);
			if (count($s) == 4) unset($this->data[$s[0]][$s[1]][$s[2]][$s[3]]);
			return $this->data;
		}
		function request_handler () {
		
			// Ponce!
			if ($nonce = attribute_escape($_GET['_wpnonce']))
				check_admin_referer($this->nonce_action());

			$keys = attribute_escape($_GET['keys']);
			if ($keys) $this->keys = explode(',', $keys);
			if (!$this->keys) $this->keys = array();

if (0) {
	echo '<pre>';
	echo $id;
	print_r($_GET);
	print_r($_POST);
	echo '</pre>';
}
			// Get basic parameters
			//$this->selected = attribute_escape($_GET['id']);
			// print_r($this->selected);
			
			$this->action = attribute_escape($_GET['action']);
			$this->navigation = attribute_escape($_GET['navigation']);
			$data = $this->read_data();


			// Check whatever you want
			if (!($this->validate_sumbission())) {
				// If saving fails
				if ($this->action == 'save') {
					$temp_key = 'saving_temp';
					$this->data[$temp_key] = $this->extract_submission();
					$this->keys = $temp_key;
				}
				return false;
			}
			
			if ($this->navigation == 'export') {
				$this->export_data();
			}
			
			if ($this->action == 'move') {
				if ($move_keys = attribute_escape($_GET['move_keys'])) {
					$move_keys = explode(',', $move_keys);
				} else $move_keys = array();

				$data = $this->get_data($move_keys);
		
				if (empty($data)) {	
					$this->error(__('Someting has gone awry. Sorry.', 'more-plugins'));
					return false;
				}
				
				$row = attribute_escape($_GET['row']);
				$up = ('up' == attribute_escape($_GET['direction'])) ? true : false;
				$data = $this->move_field($data, $row);
				
				$this->set_data($data, $move_keys, $up);

				$this->save_data($this->data);
			}
			if ($this->action == 'save') {
			//	$arr = array();
				
				//if (!($name = attribute_escape($_POST['name']))) 
				//	$_POST['name'] = sanitize_title();
				
				// The $_POST['name'] needs to be set externally, this is
				// the key(s) to be used. 
				$arr = $this->extract_submission();
				
				$name = attribute_escape($_POST['name']);
				$originating_action = attribute_escape($_POST['originating_action']);

				$id = attribute_escape($_POST['id']);	

				// Create the keys
				$originating_keys = explode(',', attribute_escape($_POST['originating_keys']));
				if ($originating_keys[0] == '') $originating_keys = array();

				if ($originating_action == 'edit') {
					$old = array_pop($originating_keys);

					// If the item changed name
					if ($old != $name) {
						// $this->message = __('The name was changed!', 'more-plugins');
						$this->unset_data($old_keys);
					}
				}
				// Set appropriate focus
				array_push($originating_keys, $name);
				$this->keys = $originating_keys;

				if ($this->save_keys) $this->keys = $this->save_keys;
								
				if ($name) {
					// $data[$name] = $arr;
					$this->set_data($arr);
					$this->save_data($this->data);
					$this->message = __('Saved!');
				}
			}
			if ($this->action == 'add') {
				$temp_key = 'default_option_temp';
				$this->data[$temp_key] = $this->default;
				array_push($this->keys, $temp_key);
				// print_r($this->data);
			}
			if ($this->action == 'delete') {
				// unset($data[$this->selected]);
				$this->unset_data();
				$this->save_data($this->data);
				$this->message = __('Deleted!', 'more-plugins');
			}
		}
		function extract_submission() {
			$arr = array();
			
			foreach($this->fields['var'] as $field) 
				$arr[$field] = attribute_escape($_POST[$field]);
			foreach($this->fields['array'] as $field) {
				$arr[$field] = $_POST[$field];
				if (!$_POST[$field]) $arr[$field] = array();
			}
			return $arr;
		}
		
		function object_to_array($data) {
			if (is_array($data) || is_object($data)) {
				$result = array(); 
				foreach($data as $key => $value) $result[$key] = $this->object_to_array($value); 
    			return $result;
  			}
			return $data;
		}
		function default_data() {
			return array();
		}

		/*
		**
		**
		*/
		function read_data() {
		/*
			if (empty($this->data) || !isset($this->data)) {
				$this->data = get_option($this->option_key);
			}
			if (empty($this->data)) {
				$this->data = $this->default_data();
			}
			return $this->data;
		*/
			return array();
		}
		/*
		**
		**
		*/
		function save_data($data) {
			foreach ($data as $key => $d) if ($d['saved']) unset($data[$key]);
			update_option($this->option_key, $data);
		}
		
		
		/*
		**
		**	Overwrite this function in subclass to validate
		**	the submission data.
		*/		
		function validate_sumbission () {
			// Somthing
			return true;
		}
		function error($error) {
			$this->error = $error;
			return false;
		}
		function set_navigation($navigation) {
			$_GET['navigation'] = $navigation;
			$_POST['navigation'] = $navigation;
			$this->navigation = $navigation;
			return $navigation;
		}	
		/*
		**
		**
		*/
		function options_page_wrapper_header () {
			if ($this->headed) return false;
			$url = get_option('siteurl');
			?>
				<div class="wrap">
				<div id="more-plugins" class="metabox-holder has-right-sidebar">		
				
					<div id="icon-options-general" class="icon32"><br /></div>
					<h2><?php echo $this->name; ?></h2>
	
					<div class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
				
							<div id="more-fields-information" class="postbox">
								<h3 class="hndle"><span><?php _e('About this Plugin', 'more-plugins'); ?>:</span></h3>
								<div class="inside">
								
									<ul>
										<li><a href="http://labs.dagensskiva.com/plugins/<?php echo $this->slug; ?>/">Plugin homepage</a></li>
										<li><a href="http://labs.dagensskiva.com/forum/">Plugin support forum</a></li>
										<li><a href="http://wordpress.org/tags/<?php echo $this->slug; ?>?forum_id=10">Wordpress Forum</a></li>
										<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&#38;business=h.melin%40gmail.com&#38;item_name=<?php echo str_replace(' ', '%20', $this->name); ?>%20Plugin&#38;no_shipping=0&#38;no_note=1&#38;tax=0&#38;currency_code=USD&#38;bn=PP%2dDonationsBF&#38;charset=UTF%2d8&#38;lc=US">Donate with PayPal</a></li>
									</ul>
							
								</div>
							</div>
							
							<div id="more-plugins-box" class="postbox">
								<h3 class="hndle"><span>More Plugins:</span></h3>
								<div class="inside">
								
									<ul>
										<li><a href="<?php echo $url; ?>/wp-admin/plugin-install.php?tab=plugin-information&#38;plugin=more-fields&#38;TB_iframe=true&#38;width=640&#38;height=679" class="thickbox" title="Install More Fields">More Fields</a></li>
										<li><a href="<?php echo $url; ?>/wp-admin/plugin-install.php?tab=plugin-information&#38;plugin=more-types&#38;TB_iframe=true&#38;width=640&#38;height=679" class="thickbox" title="Install More Types">More Types</a></li>
										<li><a href="<?php echo $url; ?>/wp-admin/plugin-install.php?tab=plugin-information&#38;plugin=more-taxonomies&#38;TB_iframe=true&#38;width=640&#38;height=679" class="thickbox" title="Install More Taxonomies">More Taxonomies</a></li>
										<!--<li><a href="#">More Thumbnails</a></li>-->
										<!--<li><a href="#">More Roles</a></li>-->
									</ul>
							
								</div>
							</div>
				
						</div>
					</div>
	
					<div id="post-body">
						<div id="post-body-content" class="has-sidebar-content">
					<?php
				$this->headed = true;
	
		}

		/*
		**
		**
		*/
		function options_page_wrapper_footer() {
			if ($this->footed) return false;
			?>
						</div> 
					</div>
				<!-- more-plugins --></div>
			<!-- /wrap --></div>
			<?php
			$this->footed = true;
		}
		
		/*
		**
		**
		*/
		function condition($condition, $message, $type = 'error') {
	
			if (!isset($this->is_ok)) $this->is_ok = true;
	
			// If there is an error already return
			if (!$this->is_ok && $type = 'error') return $this->is_ok;
	
			if ($condition == false && $type != 'silent') {
				echo '<div class="updated fade"><p>' . $message . '</p></div>';
	
				// Don't set the error flag if this is a warning.
				if ($type == 'error') $this->is_ok = false;
			}
		
			return ($condition == true);
		}
		
		/*
		**
		**
		*/
		function checkboxes($name, $title, $values, $arr) {
			?>
			<tr>
				<th scope="row" valign="top"><?php echo $title; ?></th>
				<td>
					<?php foreach ($values as $key => $title2) : 
		// 					$selected = ($arr[$name] == $key) ? ' checked="checked"'	: '';	
							$checked = (in_array($key, (array) $arr[$name])) ? " checked='checked'" : '';
		
					?>
						<label><input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo $key; ?>" <?php echo $checked; ?>> <?php echo $title2; ?></label>
					<?php endforeach; ?>
					<input type="hidden" name="<?php echo $name; ?>_values" value="<?php implode(',', $arr); ?>">
				</td>
			</tr> 	
			<?php
		}

		/*
		**
		**
		*/

		function bool_var($name, $title, $arr) {
			?>
			<tr>
				<th scope="row" valign="top"><?php echo $title; ?></th>
				<td>
					<?php
							$true = ($arr[$name]) ? " checked='checked'" : '';
							$false = ($true) ?  '' : " checked='checked'";
					?>
						<label><input type="radio" name="<?php echo $name; ?>" value="true" <?php echo $true; ?>> <?php echo $title2; ?> Yes</label>
						<label><input type="radio" name="<?php echo $name; ?>" value="false" <?php echo $false; ?>> <?php echo $title2; ?> No</label>
				</td>
			</tr> 	
			<?php
		
		}
		
		/*
		**
		**
		*/
		function move_field ($data, $nbr, $up = true) {
	
			// Are we moving out of bounds?
			if (count($data) == 1) return $data;
			if ($nbr >= count($data) - 1 && !$up) return $data;
			if ($nbr == 0 && $up) return $data;
	
			$new = array();
			$ctr = 0;
			$offset = ($up) ? 0 : 1;
			foreach ($data as $key => $arr) {
				if ($ctr == $nbr - 1 + $offset) $tmp_key = $key;
				else $new[$key] = $arr;
				if ($ctr == $nbr + $offset) $new[$tmp_key] = $data[$tmp_key];
				$ctr++;
			}

			return $new;

		}

		/*
		**
		**
		*/
		function updown_link ($nbr, $total, $args = array()) {
			$html = '';
			$link = array('row' => $nbr, 'navigation' => $this->navigation, 'action' => 'move');

			// Are we adding more stuff to our link?
			if (!empty($args)) $link = array_merge($link, $args);

			// Build the links
			if ($nbr > 0) $html .= ' | ' . $this->settings_link('&uarr', array_merge($link, array('direction' => 'up')));
			if ($nbr < $total - 1) $html .= ' | ' . $this->settings_link('&darr', array_merge($link, array('direction' => 'down')));
			return $html;
		}
		
		/*
		**
		**
		*/
		function action_link($text, $action, $id, $extra = array()) {
			$link = $this->options_url . 'action=' . $action . '&#38id='  . urlencode($id);

			// Additional stuff
			$link_extra = '&#38';
			foreach ($extra as $key => $value) 
				$link_extra .= $key . '=' . urlencode($value) . '&#38';
			$link .= $link_extra;

			// Add a default class
			$class = 'more-common-' . $action;
			$html = "<a class='$class' href='$link'>$text</a>";
			return $html;
		}
		
		/*
		**
		**
		*/
		function settings_link ($text, $args) {
			$link = $this->options_url;
			foreach ($args as $key => $value) {
				if ($key == 'class') continue;
				if (!$value) continue;
				$link .= '&' . $key . '=' . $value;
			}
			$link = wp_nonce_url($link, $this->nonce_action($args));
			$class = ($c = $args['class']) ? $c : 'more-common';
			$html = "<a class='$class' href='$link'>$text</a>";
			if (!$text) return $link;
			return $html;
		
		}

		/*
		**
		**
		*/
		function nonce_action($args = array()) {

			if (empty($args)) $args = $_GET;

			$action = $this->slug . '-action_';
			if ($a = attribute_escape($args['navigation'])) $action .= $a;			
			if ($a = attribute_escape($args['action'])) $action .= $a;

			return $action;		
		}
		/*
		**
		**
		*/
		function table_header($titles) {
			?>
			<table class="widefat">
				<thead>
					<tr>
						<?php foreach ($titles as $title) : ?>
						<th><?php echo $title; ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
			<?php
		}

		/*
		**
		**
		*/
		function table_footer($titles) {
			?>
				</tbody>
				<tfoot>
					<tr>
						<?php foreach ($titles as $title) : ?>
						<th><?php echo $title; ?></th>
						<?php endforeach; ?>
					</tr>
				</tfoot>
			</table>
			<?php
		}

		/*
		**
		**
		*/
		function table_row($contents, $nbr, $class = '') {
			$class .= ($nbr++ % 2) ? '' : ' alternate ' ;
			?>
			<tr class="<?php echo $class; ?>">
				<?php foreach ($contents as $content) : ?>
				<td><?php echo $content; ?></td>
				<?php endforeach; ?>
			</tr>
			<?php
		}

		/*
		**
		**
		*/
		function setting_row($cols, $class = '') {
			?>
				<tr class="<?php echo $class; ?>">
					<th scope="row" valign="top"><?php echo array_shift($cols); ?></th>
					<?php foreach ($cols as $col) : ?>
						<td>
							<?php echo $col; ?>
		 				</td>
					<?php endforeach; ?>
	 			</tr>
			<?php
		}


		function get_val($name, $s = array()) {
			if (empty($s)) $s = $this->keys;
			if (count($s) == 0) return $this->data[$name];
			if (count($s) == 1) return $this->data[$s[0]][$name];
			if (count($s) == 2) return $this->data[$s[0]][$s[1]][$name];
			if (count($s) == 3) return $this->data[$s[0]][$s[1]][$s[2]][$name];
			if (count($s) == 4) return $this->data[$s[0]][$s[1]][$s[2]][$s[3]][$name];
			return $value;
		}
		/*
		**
		**
		*/
		function settings_input($name) {
			$value = $this->get_val($name);			
			$html = "<input class='input-text' type='text' name='$name' value='$value'>";		
			return $html;
		}

		/*
		**
		**
		*/
		function settings_bool($name) {
			$vars = array(true => 'Yes', false => 'No');
			$html = $this->settings_radiobuttons($name, $vars);
			return $html;
		}

		function settings_radiobuttons($name, $vars, $comments = array()) {
			$html = '';
			$set = $this->get_val($name);
			foreach ($vars as $key => $value) {
				$checked = ($key == $set) ? ' checked="checked"' : '';
				$html .= "<label><input class='input-radio' type='radio' name='$name' value='$key' $checked /> $value</label> ";		
					if ($c = $comments[$key]) $html .= $this->format_comment($c);
			}
			return $html;
		}

		/*
		**
		**
		*/
		function get_roles() {
			global $wp_roles;	
			$user_levels = array();
			foreach($wp_roles->roles as $role) { 
				$name = str_replace('|User role', '', $role['name']);
				$value = sanitize_title($name); 
				if ($value) $user_levels[$value] = $name;
			}
			return $user_levels;
		}

		/*
		**
		**
		*/
		function checkbox_list($name, $vars) {
			$values = (array) $this->get_val($name);
			$html = '';
			foreach ($vars as $key => $val) {
				$checked = (in_array($key, $values)) ? ' checked="checked"' : '';
				$html .= "<label><input class='input-check' type='checkbox' value='$key' name='${name}[]' $checked> $val</label>";
			}
			$html .= '<input type="hidden" name="' . $name . '_values" value="' . implode(',', array_keys($vars)) . '">';
			return $html;		
		}
		
		function settings_select($name, $vars) {
			$values = $this->get_val($name);
			$html = "<select class='input-select' name='$name'>";
			foreach ($vars as $key => $val) {
				$checked = ($key == $values) ? ' selected="selected"' : '';
				$html .= "<option value='$key' $checked> $val</option>";
			}
			$html .= "</select>";
			return $html;		
		}
		function settings_textarea($name) {
			$value = $this->get_val($name);
			$html = "<textarea class='input-textarea' name='$name'>$value</textarea>";
			return $html;
		
		}


		/*
		**
		**
		*/
		function add_button ($options) {
			?>
			<form method="GET" ACTION="<?php echo $this->options_url; ?>">
			<input type="hidden" name="page" value="<?php echo $this->slug; ?>">
			<input type="hidden" name="navigation" value="<?php echo $options['navigation']; ?>">
			<input type="hidden" name="action" value="<?php echo $options['action']; ?>">
			<p><input class="button-primary" type="submit" value="<?php echo $options['title']; ?>"></p>

			<?php
		
		}
		
		/*
		**
		**
		*/
		function navigation_bar($levels) {
		?>
			<ul id="more-plugins-edit">
			<li><a href="<?php echo $this->settings_url; ?>"><?php echo $this->name; ?></a></li>
			<?php 
				for ($i=0; $i<count($levels); $i++) {
					$selected = ($i == count($levels) - 1) ? ' selected="selected"' : '';
					echo '<li ' . $selected . '">' . $levels[$i] . '</li>';
				}
			 ?>
			</ul>
		<?php
		}

		/*
		**
		**	The scripts we are using.
		*/
		function settings_init() {

		//	wp_enqueue_style( 'plugin-install' );
		//	wp_enqueue_script( 'plugin-install' );
		//	add_thickbox();
			// wp_enqueue_script('thickbox');
		}

		/*
		**
		**
		*/
		function settings_head () {
			?>
			<script type="text/javascript">
			//<![CDATA[
				jQuery(document).ready(function($){
					$("a.more-common-delete").click(function(){
						return confirm("<?php _e('Are you sure you want to delete?'); ?>");
					});

					$(".more-advanced-settings-toggle").click(function(){
						$('div.more-advanced-settings').slideToggle();
						return false;
					});

				});
			//]]>
			</script>
			<?php
			$css = $this->url . 'more-plugins/more-plugins.css';
			?>
				<link rel='stylesheet' type='text/css' href='<?php echo $css; ?>' />
			<?php
		}
		function settings_form_header($args = array()) {
			$defaults = array('action' => 'save', 'keys' => $_GET['keys']);
			$args = wp_parse_args($args, $defaults);
			?>
			<?php $url = $this->settings_link(false, $args); ?>
			<form method="post" action="<?php echo $url; ?>">
			<?php 
		}
		function format_comment($comment) {
			return '<em>' . $comment . '</em>';
		}
		function settings_save_button() {
		?>
			<input type="hidden" name="originating_action" value="<?php echo $_GET['action']; ?>" />
			<input type="hidden" name="originating_keys" value="<?php echo $_GET['keys']; ?>" />
			<input type="hidden" name="action" value="save" />
			<input type="submit" class="button" value="<?php _e('Save', 'more-plugins'); ?>" />		
			</form>

		<?php
		}
		
	} // end class

} // endif defined
define($more_common, true);




?>