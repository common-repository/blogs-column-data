<?php
/**
 * Plugin Name: Blogs Column Data
 * Description: <a href="https://bestwpdeveloper.com/" target="_blank">Blogs Column Data</a> A simple and nice plugin to see some informative pages and posts column.
 * Plugin URI:  https://bestwpdeveloper.com/
 * Version:     1.4
 * Author:      Best WP Developer
 * Author URI:  https://bestwpdeveloper.com/
 * Text Domain: blogs-column-data
 * Elementor tested up to: 5.8.0
 * Domain Path: /languages
 */

 if ( ! defined( 'WPINC' ) ) {
	die;
}

// Loaded Plugin text-domain
function bcd_load_textdomain(){
    load_plugin_textdomain('blogs-column-data', false, dirname(__FILE__). '/languages');
}
add_action('plugins_loaded', 'bcd_load_textdomain');

function blogs_column_data() {

	function bcd_custom_columns_list($columns) {

		print_r($columns);
		unset($columns['date']);
		
		$columns['id'] = esc_html(__('Post ID', 'blogs-column-data'));
		$columns['wordcount'] = esc_html(__('Wordcount', 'blogs-column-data'));
		$columns['thumbnail_validation'] = esc_html(__('Thumbnail Validation', 'blogs-column-data'));
		$columns['thumbnail'] = esc_html(__('Featured Image', 'blogs-column-data'));
		$columns['date'] = esc_html(__('Publish Date', 'blogs-column-data'));
		return $columns;
	}
	add_filter( 'manage_posts_columns', 'bcd_custom_columns_list' );

	function bcd_custom_columns_list_pages($columns) {

		print_r($columns);
		unset($columns['date']);
		
		$columns['id'] = esc_html(__('Pages ID', 'blogs-column-data'));
		$columns['wordcount'] = esc_html(__('Wordcount', 'blogs-column-data'));
		$columns['thumbnail_validation'] = esc_html(__('Thumbnail Validation', 'blogs-column-data'));
		$columns['thumbnail'] = esc_html(__('Featured Image', 'blogs-column-data'));
		$columns['date'] = esc_html(__('Publish Date', 'blogs-column-data'));
		return $columns;
	}
	add_filter( 'manage_pages_columns', 'bcd_custom_columns_list_pages' );

	function bcd_data_showing($column, $post_id){
		if('id' == $column){
			echo esc_html($post_id);
		}elseif('thumbnail' == $column){
			if(get_the_post_thumbnail($post_id)){
				echo get_the_post_thumbnail($post_id, array(100, 100));
			}else{
				echo 'Featured Image Not Added';
			}
		}elseif('thumbnail_validation' == $column){
			if(get_the_post_thumbnail($post_id)){
				echo 'Yes';
			}else{
				echo 'No';
			}
		}elseif('wordcount' == $column){
			$_post = get_post($post_id);
			$content = $_post->post_content;
			$wordcnt = str_word_count(strip_tags($content));
			$wordn = get_post_meta( $post_id, 'wordn', true );
			echo esc_html($wordcnt);
		}
	}
	add_action( 'manage_posts_custom_column', 'bcd_data_showing', 10, 2 );
	add_action( 'manage_pages_custom_column', 'bcd_data_showing', 10, 2 );

	function bcd_sortable( $columns ) {
		$columns['wordcount'] = esc_html(__('wordbcd', 'blogs-column-data'));
		return $columns;
	}
	add_filter( 'manage_edit-post_sortable_columns', 'bcd_sortable' );

	// Check just has thumbnail or no thumbnail
	function bcd_thumbnail_filter(){
		$filter_values = isset($_GET['bcd_thumbnail_filter']) ? sanitize_key($_GET['bcd_thumbnail_filter']) : '';
		$valus = array(
			'0' => esc_html(__('--- Select ---', 'blogs-column-data')),
			'1' => esc_html(__('Has Thumbnail', 'blogs-column-data')),
			'2' => esc_html(__('No Thumbnail', 'blogs-column-data')),
		);
		?>
			<select name="bcd_thumbnail_filter">
				<?php 
					foreach($valus as $keys => $valu){
						printf("<option value='%s' %s>%s</option>", $keys,
					$keys == $filter_values ? "selected = 'selected'" : '',
					$valu
					);
					}
				?>
			</select>
		<?php
	}
	add_action('restrict_manage_posts', 'bcd_thumbnail_filter');
	add_action('restrict_manage_pages', 'bcd_thumbnail_filter');

	function bcd_thumbnail_column_filter($wpquery){
		if(! is_admin()){
			return;
		}
		$filter_value = isset($_GET['bcd_thumbnail_filter']) ? sanitize_key($_GET['bcd_thumbnail_filter']) : '';
		if('1'==$filter_value){
			$wpquery->set('meta_query', array(
				array(
					'key' => '_thumbnail_id',
					'compare' => 'EXISTS'
				)
			)
		);
		} else if('2'==$filter_value){
			$wpquery->set('meta_query', array(
				array(
					'key' => '_thumbnail_id',
					'compare' => 'NOT EXISTS'
				)
			)
		);
		}
	}
	add_action('pre_get_posts', 'bcd_thumbnail_column_filter');
	add_action('pre_get_pages', 'bcd_thumbnail_column_filter');

}
blogs_column_data();

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
require_once( 'includes/admin-notice.php' );
require __DIR__ . '/vendor/autoload.php';
function appsero_init_tracker_blogs_column_data() {
    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }
    $client = new Appsero\Client( 'c4865092-5e8c-41dc-8e29-887ecd306081', 'Blogs Column Data', __FILE__ );
    $client->insights()->init();
}
appsero_init_tracker_blogs_column_data();


