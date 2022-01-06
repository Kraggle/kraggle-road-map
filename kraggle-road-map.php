<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://kragglesites.com
 * @since             
 * @package           Kraggle-Road-Map
 *
 * @wordpress-plugin
 * Plugin Name:       Kraggles Road Map
 * Plugin URI:        http://kragglesites.com
 * Description:       Shortcode and section items to create roadmaps
 * Version:           1.0.1
 * Author:            Kraggle
 * Author URI:        http://kragglesites.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       kraggle-road-map
 * Domain Path:       /
 */



// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('KRM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('KRM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KRM_PLUGIN_URL', plugin_dir_url(__FILE__));

function cc_mime_types($mimes) {
	$mimes['json'] = 'application/json';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function krm_shortcode($attrs) {
	$version = '1.0.2';

	extract(shortcode_atts(
		array(
			'mapID' => 1
		),
		$attrs
	));

	$query = new WP_Query([
		'post_type' => 'road-map-items',
		'meta_key' => 'road_map_item_order',
		'meta_query' => [
			'key' => 'road_map_item_map',
			'value' => $mapID,
			'compare' => '='
		],
		'meta_type' => 'number',
		'order' => 'ASC',
		'orderby' => 'meta_value_num'
	]);

	wp_enqueue_style('krm', KRM_PLUGIN_URL . 'style/style.css', [], $version);
	wp_enqueue_script('module-krm', KRM_PLUGIN_URL . 'js/script.js', ['jquery'], $version);
	wp_localize_script('module-krm', 'krmNft', [
		'url' => KRM_PLUGIN_URL
	]);

	ob_start(); ?>

	<div class="krm-container">
		<?php $i = 0;
		foreach ($query->posts as $post) { ?>
			<div class="krm-item <?= ($i % 2 == 0 ? 'left' : 'right') ?>">
				<span class="krm-title"><?= $post->post_title ?></span>
				<div class="krm-content">
					<?= $post->post_content ?>
				</div>
				<span class="krm-digit"><?= $i + 1 ?></span>
			</div>
		<?php
			$i++;
		} ?>
	</div>

<?php $html = ob_get_contents();
	ob_end_clean();

	return $html;
}
add_shortcode('road-map', 'krm_shortcode');

function krm_script_as_module($tag, $handle, $src) {
	if (preg_match('/^module-/', $handle)) {
		$tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '"></script>';
	}

	return $tag;
}
add_filter('script_loader_tag', 'krm_script_as_module', 10, 3);


add_action('init', function () {
	$plural = 'Road Map Items';
	$single = 'Road Map Item';
	register_post_type('road-map-items', [
		'labels' => [
			'name'               => _x($plural, 'post type general name', 'boilerplate'),
			'singular_name'      => _x($single, 'post type singular name', 'boilerplate'),
			'menu_name'          => _x($plural, 'admin menu', 'boilerplate'),
			'name_admin_bar'     => _x($single, 'add new on admin bar', 'boilerplate'),
			'add_new'            => _x('Add New', 'optional_extras', 'boilerplate'),
			'add_new_item'       => __("Add New $single", 'boilerplate'),
			'new_item'           => __("New $single", 'boilerplate'),
			'edit_item'          => __("Edit $single", 'boilerplate'),
			'view_item'          => __("View $single", 'boilerplate'),
			'all_items'          => __($plural, 'boilerplate'),
			'search_items'       => __("Search $plural", 'boilerplate'),
			'parent_item_colon'  => __("Parent $plural:", 'boilerplate'),
			'not_found'          => __("No $plural found.", 'boilerplate'),
			'not_found_in_trash' => __("No $plural found in Trash.", 'boilerplate'),
		],
		'supports'            => ['title', 'editor'], // 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-index-card',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'map_meta_cap'        => true,
		'capabilities'        => ['edit_posts'],
		'yarpp_support'       => false,
		'taxonomies' 		  => []
	]);
});

add_action('admin_init', function () {
	add_meta_box('road-map-item-options', __('Road Map Item Options', 'boilerplate'), 'road_map_item_options', 'road-map-items', 'normal');
});

function road_map_item_options($post) {
	wp_nonce_field('road-map-nonce', 'road-map-nonce');
	$value = get_post_meta($post->ID, 'road_map_item_map', true);
	if (!$value) $value = '1';
	echo "<label>Map ID: </label><input type=\"number\" min=1 value=\"$value\" name=\"road_map_item_map\"><br>";
	$value = get_post_meta($post->ID, 'road_map_item_order', true);
	if (!$value) $value = '1';
	echo "<label>Item Order: </label><input type=\"number\" min=1 value=\"$value\" name=\"road_map_item_order\">";
}

add_action('save_post', function ($post_id) {
	// Check if our nonce is set.
	if (!isset($_POST['road-map-nonce'])) {
		return;
	}

	// Verify that the nonce is valid.
	if (!wp_verify_nonce($_POST['road-map-nonce'], 'road-map-nonce')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if (isset($_POST['road_map_item_order'])) {
		// Sanitize user input.
		$my_data = sanitize_text_field($_POST['road_map_item_order']);

		// Update the meta field in the database.
		update_post_meta($post_id, 'road_map_item_order', $my_data);
	}

	// Make sure that it is set.
	if (isset($_POST['road_map_item_map'])) {
		// Sanitize user input.
		$my_data = sanitize_text_field($_POST['road_map_item_map']);

		// Update the meta field in the database.
		update_post_meta($post_id, 'road_map_item_map', $my_data);
	}
});

add_filter('manage_road-map-items_posts_columns', function ($columns) {
	$new = array();
	foreach ($columns as $key => $column) {
		$new[$key] = $column;

		if ($key == 'title') {
			$new['item-map'] = __('Map ID', 'boilerplate');
			$new['item-order'] = __('Item Order', 'boilerplate');
		}
	}

	return $new;
});

add_action('manage_road-map-items_posts_custom_column', function ($column, $post_id) {
	switch ($column) {

		case 'item-map':
			echo get_post_meta($post_id, 'road_map_item_map', true);
			break;
		case 'item-order':
			echo get_post_meta($post_id, 'road_map_item_order', true);
			break;
	}
}, 10, 2);
