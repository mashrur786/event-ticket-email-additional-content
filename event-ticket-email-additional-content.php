<?php
/*
Plugin Name: Event Ticket Email Additional Content
Plugin URI: https://wordpress.org/plugins/ete-additonal-content/
Description: This plugin allows admin to add additional content to the Email tickets towards to end of event ticket plus tickets.
This plugin requires and work alongside Tribe Event Ticket Plus plugin.
Version: 1
Author: Mashrur Chowdhury
Author URI: http://mashrur.co.uk
Text Domain: ete-additional-content
Domain Path: /languages
*/



// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* Register post type when plugin is activated */
add_action( 'init', 'eteac_register_post_type' );
function eteac_register_post_type(){

	$labels = array(
		'name' => __( 'Email Additional Content', 'tribe' ),
		'singular_name' => __( 'Email Additional Content', 'tribe' ),
		'add_new' => __( 'Add new ', 'tribe' ),
		'add_new_item' => __('Add New ', 'tribe' ),
		'edit_item' => __( 'Edit', 'tribe' ),
		'new_item' => __( 'New', 'tribe' ),
		'view_item' => __( 'View', 'tribe' ),
		'search_items' => __( 'Search', 'tribe' ),
		'not_found' => __( 'Noting Found', 'tribe' ),
		'not_found_in_trash' => __( 'Nothing Found in the Trash', 'tribe' ),
	);
	$args = array(
		'labels' => $labels,
		'public' => false,
		'show_ui' => true,
		'capability_type' => 'post',
		'show_in_nav_menus' => true,
		'menu_icon' => 'dashicons-email-alt',
		'rewrite' => true,
		'menu_position' => 20,
		'supports' => array(
			'title',
			'editor'
		),
	);
	register_post_type( 'eEmailContent', $args );

}

/*
* Setup custom meta boxes for the wp-barometer custom post type page
*/

if ( ! class_exists( '' ) ) {
	class EventTicketEmailContent {

		protected $event_id;


		function __construct() {
			add_action( 'add_meta_boxes', array($this, 'etpeac_metabox_create') );
			add_action( 'save_post',  array($this, 'etpeac_meta_save'), 10, 3);
			add_action( 'tribe_tickets_ticket_email_ticket_top', array($this,'etpeac_store_event_id') );
			//add_action( 'tribe_tickets_ticket_email_bottom', array($this,'etpeac_add_additional_content') );
		}


		/* create custom post type meta box */
		public function etpeac_metabox_create() {
			add_meta_box(
				'etpeac_meta',
				__( 'Select Event for Email Additional Content', 'tribe' ),
				array($this, 'etpeac_metabox_display'),
				'eEmailContent',
				'normal',
				'high'
			);
		}



		/**
		 * Meta box display for the barometer post type.
		 * @param $post object
		 * Provides the form controls necessary to select the color of the barometer as well as:
		 * @return  null
		 */
		public function etpeac_metabox_display($post){



			// Ensure the global $post variable is in scope
			global $post;
			$event_id = get_post_meta($post->ID, 'event_id', true);
			$output = '';
			$isSelected = '';
			// Retrieve the next 5 upcoming events
			$events = tribe_get_events( array(
					'start_date'     => date( 'Y-m-d H:i:s' )
				)
			);


			wp_nonce_field(basename(__FILE__), 'etpeac_fields');
			// Output the field

			$output .= "<select style='' name='event_id'>";
			foreach ( $events as $post ) {
				if($event_id == $post->ID ) $isSelected = "selected";
				$output .= "<option " . $isSelected ." value='" . $post->ID ."'> ". $post->post_title ." </option>";
			}
			$output .= "</select>";

			//$output .= "<input type='number' name='event_id' placeholder='" .  $event_id ."' value='" . $event_id . "' required>";
			echo $output;

		}


		/** Saves the meta box info for the post
		 * - wp_barometer_meta_save
		 * @param $post_id
		 *
		 * @return String
		 */

		public function etpeac_meta_save( $post_id) {


			if( !isset( $_POST['etpeac_fields'] )) return;


			//skip auto save
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}


			// If this isn't a 'wpbarometer' post, don't update it.
			$post_type = get_post_type($post_id);
			if ( "eemailcontent" != $post_type ) return;

			if (isset($_POST['event_id'])){
				update_post_meta($post_id, 'event_id', sanitize_text_field($_POST['event_id']));
			}

		}

		public function etpeac_store_event_id($ticket){
			$this->event_id = $ticket['event_id'];

			$args = array(
				'post_type'     => 'eemailcontent',
				'meta_query' => array(
					array(
						'key' => 'event_id',
						'value' =>  $this->event_id,
						'compare' => '=',
					)
				)
			);


			$query = new WP_Query($args);
			echo '<table class="content" align="center" width="620" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="margin:0 auto; padding:0; margin: top -100px">';
			echo '<tr>';
			echo '<td style="text-align: left; color: #555;">';
			if( $query->have_posts() ){
				while ($query->have_posts()){
					$query->the_post();
					wpautop(the_content());
					break;
				}
			}
			echo '</td>';
			echo '</tr>';
			echo '<table>';
		}


		public function etpeac_add_additional_content(){

			$args = array(
				'post_type'     => 'eemailcontent',
				'meta_query' => array(
					array(
						'key' => 'event_id',
						'value' =>  $this->event_id,
						'compare' => '=',
					)
				)
			);


			$query = new WP_Query($args);
			echo '<table class="content" align="center" width="620" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="margin:20px auto; padding:0; margin: top -100px">';
			echo '<tr>';
			echo '<td style="text-align: left; color: #555;">';
			if( $query->have_posts() ){
				while ($query->have_posts()){
					$query->the_post();
					wpautop(the_content());
					break;
				}
			}
			echo '</td>';
			echo '</tr>';
			echo '<table>';

		}



	}

}

new EventTicketEmailContent();


register_uninstall_hook( __FILE__, 'etpeac_uninstall' );

function etpeac_uninstall() {
	// Uninstallation stuff here
	unregister_post_type( 'eemailcontent' );
}