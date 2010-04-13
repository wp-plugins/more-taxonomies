<?php

global $more_taxonomies_settings, $wp_taxonomies;

//print_r($wp_taxonomies);

if (!$this->navigation) {

//	$more_taxonomies_settings->navigation_bar(array());

	echo '<p>';
	_e('Here you can create and edit taxonomies. Taxonomy is classification in essence, and taxonomies can be used to organize data and information.', 'more-plugins');
	echo '</p>';

	$defaults = array('category', 'post_tag', 'nav_menu', 'link_category'); //array_keys($more_taxonomies_settings->default_data());

	$titles = array('Taxonomy', 'Actions');
	$more_taxonomies_settings->table_header($titles);
	$nbr = 0;
	foreach ($more_taxonomies_settings->read_data() as $name => $tax) {

		$class = (in_array($name, $defaults)) ? 'default-taxonomy' : '';
		$warning = ($class) ? '<em>' . __('*', 'more-plugins') . '</em>' : '';
		$data = array(
				$more_taxonomies_settings->settings_link($tax['label'], array('navigation' => 'taxonomy', 'action' => 'edit', 'keys' => $tax['name'])) . $warning,	
				$more_taxonomies_settings->settings_link('Edit', array('navigation' => 'taxonomy', 'action' => 'edit', 'keys' => $tax['name'])) . ' | ' .
				$more_taxonomies_settings->settings_link('Delete', array('action' => 'delete','keys' => $tax['name'], 'class' => 'more-common-delete')) . ' | ' .
				$more_taxonomies_settings->settings_link('Export', array('navigation' => 'export', 'keys' => $tax['name'])) . 
				$more_taxonomies_settings->updown_link($nbr, count($more_taxonomies_settings->data))
			);
		$more_taxonomies_settings->table_row($data, $nbr++, $class);
	}

	$more_taxonomies_settings->table_footer($titles);

	$options = array('title' => 'Add Taxonomy', 'action' => 'add', 'navigation' => 'taxonomy');
	$more_taxonomies_settings->add_button($options);


} else if ($this->navigation == 'taxonomy') {

	// Set up the navigation	
	$navtext = $more_taxonomies_settings->get_val('label');
	if (!$navtext) $navtext = __('Add new', 'more-plugins');
	$more_taxonomies_settings->navigation_bar(array($navtext));
	
	?>
	<?php $url = $more_taxonomies_settings->settings_link(false, array('action' => 'save', 'keys' => implode(',', $more_taxonomies_settings->keys))); ?>
	<form method="post" action="<?php echo $url; ?>">
	<table class="form-table">
	<?php
	
		$comment = __('This is the plural name of the taxonomy, e.g. \'People\'.', 'more-plugins');
		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Taxonomy name plural', 'more-plugins'), $more_taxonomies_settings->settings_input('label') . $comment);
		$more_taxonomies_settings->setting_row($row);

		$comment = __('This is the singular name of the taxonomy, e.g. \'Person\'.', 'more-plugins');
		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Taxonomy name singular', 'more-plugins'), $more_taxonomies_settings->settings_input('singular_label') . $comment);
		$more_taxonomies_settings->setting_row($row);

		$comment = __('Create permalink structure for this taxonomy. In order for this to work permalinks must be enabled.', 'more-plugins');

		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Allow permalinks', 'more-plugins'), $more_taxonomies_settings->settings_bool('rewrite') . $comment);
		$more_taxonomies_settings->setting_row($row);

		$comment = __("If 'Allow permalinks' is set to true, then set the base permalink of this taxonomy here.", 'more-plugins');
		if ($base = $more_taxonomies_settings->get_val('rewrite_base')) {
			$comment .= ' ' . __('It is currently', 'more-plugins') . ' <code>' . get_option('siteurl') .  '/' . $base . '/</code>';
		}
		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Taxonomy slug', 'more-plugins'), $more_taxonomies_settings->settings_input('rewrite_base') . $comment);
		$more_taxonomies_settings->setting_row($row);

		$comment = __('Enables taxonomy items to be children of other items of the same taxonomy. E.g. in a standard WordPress installation, tags are not heirarchical whilst categories are.', 'more-plugins');
		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Hierarchical', 'more-plugins'), $more_taxonomies_settings->settings_bool('hierarchical') . $comment);
		$more_taxonomies_settings->setting_row($row);

		$comment = __('\'No\' to prevent the taxonomy being listed in the Tag Cloud Widget.', 'more-plugins');
		$comment = $more_taxonomies_settings->format_comment($comment);
		$row = array(__('Show tag cloud', 'more-plugins'), $more_taxonomies_settings->settings_bool('show_tagcloud') . $comment);
		$more_taxonomies_settings->setting_row($row);

		if (!is_plugin_active('more-types/more-types.php')) {
			$types = $more_taxonomies_settings->get_post_types();
			$row = array(__('Available to', 'more-plugins'), $more_taxonomies_settings->checkbox_list('object_type', $types));
			$more_taxonomies_settings->setting_row($row);
		} else {
			$comment = sprintf(__('To link taxonomies to post types use %s!', 'more-plugins'), '<a href="options-general.php?page=more-types">More Types</a>');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Available to', 'more-plugins'), $comment);
			$more_taxonomies_settings->setting_row($row);
		}
	?>

	</table>

	<div class="more-plugins-advanced-settings">
		<h3 class="more-advanced-settings-toggle"><a href="#">Advanced settings <span>show/hide</span></a></h3>
		<div class="more-advanced-settings">
		<table class="form-table">
	
		<?php

			$comment = __('Show the default taxonomy WordPress UI.', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Show UI', 'more-plugins'), $more_taxonomies_settings->settings_bool('show_ui') . $comment);
			$more_taxonomies_settings->setting_row($row);
		
			$comment = __('Allow this taxonomy to be publically queriable', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Allow queries', 'more-plugins'), $more_taxonomies_settings->settings_bool('query_var_bool') . $comment);
			$more_taxonomies_settings->setting_row($row);

			$comment = __("If queries are allowed, then this is the variable to be used when querying this taxonomy.", 'more-plugins');
			if ($query_var = $more_taxonomies_settings->get_val('query_var')) {
				$comment .= ' ' . __('Usage: ', 'more-plugins') . '<code>'. get_option('siteurl') . '/?' . $query_var . '=term_to_find</code>';
			}
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Query variable', 'more-plugins'), $more_taxonomies_settings->settings_input('query_var') . $comment);
			$more_taxonomies_settings->setting_row($row);

		
			$comment = __('Make this taxonomy available publically on your WordPress installation.', 'more-plugins');
			$comment = '<em>' . $comment . '</em>';
			$row = array(__('Public', 'more-plugins'), $more_taxonomies_settings->settings_bool('public') . $comment);
			$more_taxonomies_settings->setting_row($row);

			$roles = $more_taxonomies_settings->get_roles();

			$comment = __('The roles that can manage this taxonomy.', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Manage capability', 'more-plugins') . $comment, $more_taxonomies_settings->checkbox_list('more_manage_cap', $roles));
			$more_taxonomies_settings->setting_row($row);
		
			$comment = __('The roles that can manage this taxonomy.', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Edit capability', 'more-plugins') . $comment, $more_taxonomies_settings->checkbox_list('more_edit_cap', $roles));
			$more_taxonomies_settings->setting_row($row);
	
			$comment = __('The roles that can manage this taxonomy.', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Delete capability', 'more-plugins') . $comment, $more_taxonomies_settings->checkbox_list('more_delete_cap', $roles));
			$more_taxonomies_settings->setting_row($row);
		
			$comment = __('The roles that can manage this taxonomy.', 'more-plugins');
			$comment = $more_taxonomies_settings->format_comment($comment);
			$row = array(__('Assign capability', 'more-plugins') . $comment, $more_taxonomies_settings->checkbox_list('more_assign_cap', $roles));
			$more_taxonomies_settings->setting_row($row);
		?>
	
		</table>
		</div>
	</div>
		
	<input type="hidden" name="keys" value="<?php echo implode(',', $more_taxonomies_settings->keys); ?>">
	<input type="hidden" name="name" value="<?php echo $more_taxonomies_settings->selected; ?>">
	<input type="hidden" name="originating_action" value="<?php echo $_GET['action']; ?>">
	<input type="hidden" name="originating_keys" value="<?php echo $_GET['keys']; ?>">
	<input type="hidden" name="action" value="save">
	<input type="submit" class="button" value="<?php _e('Save'); ?>">
	</form>

	<?php
}
global $wp_post_types;

	$arr = array();
	foreach ($wp_post_types as $key => $type) $arr[$key] = $type->taxonomies;

global $wp_plugins;
	echo '<pre>';
	print_r($wp_plugins);
	echo '</pre>';



?>