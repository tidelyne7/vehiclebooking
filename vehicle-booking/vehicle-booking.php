<?php
ob_start();
/**
 * Plugin Name: Vehicle Booking
 * Description: plugin to book a vehicle
 * Version:     1.0
 * Text Domain: vb
*/

/********************
Adding vb plugin to admin menu 
*********************/
function vb_add_menu(){
  // This page will be under "Settings"
  add_menu_page(
    'Vehicle Booking',
    __('Vehicle Booking', 'vb'),
    'manage_options',
    'vb-setting-admin',
    'vb_create_admin_page'
  );


}
add_action( 'admin_menu', 'vb_add_menu');
/**
* Enqueue Admin style and script
*
*/
function vb_admin_style_script() {
  wp_register_script( 
    'ajaxHandle', 
    plugin_dir_url(__FILE__) . 'assets/js/vb-admin-custom.js', 
    array('jquery'), 
    false, 
    true 
  );
  wp_enqueue_script( 'ajaxHandle' );
  wp_localize_script( 
    'ajax_object', 
    'ajax_object', 
    array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
  );
    wp_enqueue_style('vb-admin-css', plugins_url('assets/css/vb_admin_custom.css',__FILE__) );
    // wp_enqueue_script('pbd-admin-jquery', plugin_dir_url(__FILE__) . 'assets/js/vb-admin-custom.js',array('jquery') );
}
add_action( 'admin_enqueue_scripts', 'vb_admin_style_script' );

function vb_front_style_script() {

    wp_enqueue_script( 'ajax-script', plugin_dir_url(__FILE__) . 'assets/js/vb_front_js.js', array('jquery') );

    wp_localize_script( 'ajax-script', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_style('vb-front-css', plugins_url('assets/css/vb_front_cutom.css',__FILE__) );
    wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'jquery_ui','https://code.jquery.com/ui/1.12.1/jquery-ui.js', array( 'jquery' ),'',true );
    wp_enqueue_style('style_ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}
add_action( 'wp_enqueue_scripts', 'vb_front_style_script' );
/**call back function on admin side**/
function vb_create_admin_page(){

}
/*****register custom post type vehicle******/

function vb_vehicle_custom_post_type() {
    $labels = array(
        'name' => _x('Vehicle', 'post type general name'),
        'singular_name' => _x('Vehicle Item', 'post type singular name'),
        'add_new' => _x('Add New', 'vehicle item'),
        'add_new_item' => __('Add New Vehicle Item'),
        'edit_item' => __('Edit Vehicle Item'),
        'new_item' => __('New Vehicle Item'),
        'view_item' => __('View Vehicle Item'),
        'search_items' => __('Search Vehicle Items'),
        'not_found' =>  __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 8,
        'supports' => array('title','editor','thumbnail'),
        'register_meta_box_cb' => 'vb_starting_price_per_day',
    ); 
    register_post_type( 'vehicle_cpt' , $args );
}
add_action('init', 'vb_vehicle_custom_post_type');
function vb_create_vehicle_taxonomies() {
    $labels = array(
        'name'              => _x( 'Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Categories' ),
        'all_items'         => __( 'All Categories' ),
        'parent_item'       => __( 'Parent Category' ),
        'parent_item_colon' => __( 'Parent Category:' ),
        'edit_item'         => __( 'Edit Category' ),
        'update_item'       => __( 'Update Category' ),
        'add_new_item'      => __( 'Add New Category' ),
        'new_item_name'     => __( 'New Category Name' ),
        'menu_name'         => __( 'Categories' ),
    );

    $args = array(
        'hierarchical'      => true, // Set this to 'false' for non-hierarchical taxonomy (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'categories' ),
    );

    register_taxonomy( 'vb_category', array( 'vehicle_cpt' ), $args );
}
add_action( 'init', 'vb_create_vehicle_taxonomies', 0 );
/**
 * Adds a metabox to the right side of the screen under the â€œPublishâ€ box
 */
function vb_starting_price_per_day() {
  add_meta_box(
    'vb_meta_price',
     __( 'Starting Price Per Day', 'vb' ),
    'vb_meta_price',
    'vehicle_cpt'
    
  );
}
// add_meta_box( 'add_meta_boxes', 'vb_starting_price_per_day' );
// add_meta_box( $id, $title, $callback, $page, $context, $priority, $callback_args );
// add_meta_box( 'vb_meta_price', 'Starting Price Per Day', 'vb_meta_price', 'vehicle_cpt', 'normal', 'high' );
add_action( 'add_meta_boxes', 'vb_starting_price_per_day' );
/**
 * Output the HTML for the metabox.
 */
function vb_meta_price() {
  global $post;
  // Nonce field to validate form request came from current site
  wp_nonce_field( 'vehicle_price_field', 'vehicle_price_field' );
  // Get the location data if it's already been entered
  $starting_price_per_day = get_post_meta( $post->ID, '_starting_price_per_day', true );
  // Output the field
  echo '<input type="text" name="starting_price_per_day" value="' . esc_textarea( $starting_price_per_day )  . '" class="vb_starting_price_class">';
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function vb_save_starting_price_per_day_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['vehicle_price_field'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['vehicle_price_field'], 'vehicle_price_field' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['vehicle_cpt'] ) && 'page' == $_POST['vehicle_cpt'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['starting_price_per_day'] ) ) {
        return;
    }

    // Sanitize user input.
    $starting_price_per_day_data = sanitize_text_field( $_POST['starting_price_per_day'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_starting_price_per_day', $starting_price_per_day_data );
}

add_action( 'save_post', 'vb_save_starting_price_per_day_meta_box_data' );

/*******Custompost type for booking*******/
function vb_booking_custom_post_type() {
    $labels = array(
        'name' => _x('Booking', 'post type general name'),
        'singular_name' => _x('Booking Item', 'post type singular name'),
        'add_new' => _x('Add New', 'Booking item'),
        'add_new_item' => __('Add New Booking Item'),
        'edit_item' => __('Edit Booking Item'),
        'new_item' => __('New Booking Item'),
        'view_item' => __('View Booking Item'),
        'search_items' => __('Search Booking Items'),
        'not_found' =>  __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 8,
        'supports' => array('title','editor','thumbnail'),
        'register_meta_box_cb' => 'vb_booking',
    ); 
    register_post_type( 'booking_cpt' , $args );
}
add_action('init', 'vb_booking_custom_post_type');
/**
 * Adds a metabox to the right side of the screen under the â€œPublishâ€ box
 */
function vb_booking() {
  add_meta_box(
    'vb_booking_meta_box',
     __( 'Booking Form Details', 'vb' ),
    'vb_booking_meta_box',
    'booking_cpt'
    
  );
}
// add_meta_box( 'add_meta_boxes', 'vb_starting_price_per_day' );
// add_meta_box( $id, $title, $callback, $page, $context, $priority, $callback_args );
// add_meta_box( 'vb_meta_price', 'Starting Price Per Day', 'vb_meta_price', 'vehicle_cpt', 'normal', 'high' );
add_action( 'add_meta_boxes', 'vb_booking' );
/**
 * Output the HTML for the metabox.
 */
function vb_booking_meta_box() {
  global $post;
  // Nonce field to validate form request came from current site
  wp_nonce_field( 'booking_vehicle_price_field', 'booking_vehicle_price_field' );
  // Get the location data if it's already been entered
  $first_name = get_post_meta( $post->ID, '_first_name', true );
  $last_name = get_post_meta( $post->ID, '_last_name', true );
  $email = get_post_meta( $post->ID, '_email', true );
  $phone = get_post_meta( $post->ID, '_phone', true );
  $vehicle_type = get_post_meta( $post->ID, '_vehicle_type', true );
  $vehicle = get_post_meta( $post->ID, '_vehicle', true );
  $starting_price = get_post_meta( $post->ID, '_starting_price', true );
  $starting_date = get_post_meta( $post->ID, '_starting_date', true );
  $status = get_post_meta( $post->ID, '_status', true );
  // $status = isset( $values['status'] ) ? esc_attr( $values['status'][0] ) : ”;

  // Output the field
  echo '<p><label>First Name</label><input type="text" name="first_name" value="' . esc_textarea( $first_name )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Last Name</label><input type="text" name="last_name" value="' . esc_textarea( $last_name )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Email</label><input type="text" name="email" value="' . esc_textarea( $email )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Phone</label><input type="text" name="phone" value="' . esc_textarea( $phone )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Vehicle Type</label><input type="text" name="vehicle_type" value="' . esc_textarea( $vehicle_type )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Vehicle</label><input type="text" name="Vehicle" value="' . esc_textarea( $vehicle )  . '" class="vb_sbooking_inputs"></p>
    <p><label>Starting price</label><input type="number" name="starting_price" value="' . esc_textarea( $starting_price )  . '" class="vb_sbooking_inputs"></p>
      <p><label>Starting Date</label><input type="text" name="starting_date" value="' . esc_textarea( $starting_date )  . '" class="vb_sbooking_inputs"></p>
      <p><label>Status</label><select name="status" class="vb_sbooking_select">
      <option value="Pending'.selected( $status, "Pending" ).'">Pending</option>
      <option value="Approved'.selected( $status, "Approved" ).'">Approved</option>
      <option value="Reject'.selected( $status, "Reject" ).'">Reject</option>
      <option value="On the way'.selected( $status, "On the way" ).'">On the way</option>
      <option value="Complete'.selected( $status, "Complete" ).'">Complete</option>
      </select></p>';
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function vb_booking_form_meta_box( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['booking_vehicle_price_field'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['booking_vehicle_price_field'], 'booking_vehicle_price_field' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['booking_cpt'] ) && 'page' == $_POST['booking_cpt'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['first_name'] ) ) {
        return;
    }

    // Sanitize user input.
    $first_name = sanitize_text_field( $_POST['first_name'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_first_name', $first_name );
    if ( ! isset( $_POST['last_name'] ) ) {
        return;
    }

    // Sanitize user input.
    $last_name = sanitize_text_field( $_POST['last_name'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_last_name', $last_name );
    if ( ! isset( $_POST['email'] ) ) {
        return;
    }

    // Sanitize user input.
    $email = sanitize_text_field( $_POST['email'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_email', $email );
    if ( ! isset( $_POST['phone'] ) ) {
        return;
    }

    // Sanitize user input.
    $phone = sanitize_text_field( $_POST['phone'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_phone', $phone );
    if ( ! isset( $_POST['vehicle_type'] ) ) {
        return;
    }

    // Sanitize user input.
    $vehicle_type = sanitize_text_field( $_POST['vehicle_type'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_vehicle_type', $vehicle_type );
    if ( ! isset( $_POST['Vehicle'] ) ) {
        return;
    }

    // Sanitize user input.
    $vehicle = sanitize_text_field( $_POST['Vehicle'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_vehicle', $vehicle );
    if ( ! isset( $_POST['starting_price'] ) ) {
        return;
    }

    // Sanitize user input.
    $starting_price = sanitize_text_field( $_POST['starting_price'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_starting_price', $starting_price );
    if ( ! isset( $_POST['starting_date'] ) ) {
        return;
    }

    // Sanitize user input.
    $starting_date = sanitize_text_field( $_POST['starting_date'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_starting_date', $starting_date );
    if ( ! isset( $_POST['status'] ) ) {
        return;
    }

    // Sanitize user input.
    $status = sanitize_text_field( $_POST['status'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_status', $status );

}

add_action( 'save_post', 'vb_booking_form_meta_box' );
/*******Shortoce for Booking Form*********/
function vb_booking_form(){
  $booking_form_html = '<section class="vb_booking_frm_wrp">
    <div class="vb_booking_frm">
      <div class="container">
        <form action="" method="post" class="needs-validation booking_frm_custom" novalidate>
          <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" class="form-control" id="first_name" placeholder="Enter First Name" name="first_name" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" class="form-control" id="last_name" placeholder="Enter Last Name" name="last_name" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" placeholder="Enter Email" name="email" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="number" class="form-control" id="phone" placeholder="Enter phone number" name="phone" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="vehicle_type">Vehicle Type:</label>
            <select class="form-control vb_vehicle_type_select" id="vehicle_type" name="vehicle_type">
              <option disabled selected>Select Vehicle Type</option>'
              .$terms = get_terms('vb_category');
              foreach ( $terms as $term ) 
                  $booking_form_html.= '<option value="'.$term->name.'">'.$term->name.'</option>';
              
             $booking_form_html.='</select>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="vehicle">Vehicle</label>
            <select class="form-control vb_vehicle_select" id="vehicle" name="Vehicle" >
              <option disabled selected>Select Vehicle</option>
            </select>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="starting_price_per_day">Starting Price Per Day:</label>
            <input type="number" class="form-control" id="starting_price_per_day"  name="starting_price_per_day" readonly>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="message">Message:</label>
            <textarea type="number" rows="4" class="form-control" id="message" placeholder="Enter Message number" name="message" required></textarea>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          <div class="form-group">
            <label for="starting_date">Starting date:</label>
          <input type="text" id="datepicker" name="starting_date">
          <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
          </div>
          
          <button name="booking_frm_submit" type="submit" class="btn btn-primary booking_frm_btn">Submit</button>
        </form>
      </div>
    </div>
    
  </section>';

  return $booking_form_html;
}
add_shortcode( 'vb_booking_form', 'vb_booking_form' );
/*******On submit click of form************/
function vb_booking_data_insert(){
  if (isset($_POST['booking_frm_submit'])) {
    
    $first_name =  $_POST['first_name'];
    $last_name =  $_POST['last_name'];
    $email =  $_POST['email'];
    $phone =  $_POST['phone'];
    $vehicle_type =  $_POST['vehicle_type'];
    $Vehicle =  $_POST['Vehicle'];
    $starting_price_per_day =  $_POST['starting_price_per_day'];
    $message =  $_POST['message'];
    $starting_date =  $_POST['starting_date'];
    $booking_post_title = $first_name." _ ".$vehicle_type;
    $post_id = wp_insert_post(array(
    'post_author'=>'1',
    'post_title'=>$booking_post_title, 
    'post_type'=>'booking_cpt', 
    'post_content'=>$message,
    'post_status' =>"publish"
    ));
    if ($post_id) {
     // insert post meta
     update_post_meta($post_id, '_first_name',$first_name);
     update_post_meta($post_id, '_last_name',  $last_name);
     update_post_meta($post_id, '_email',$email);
     update_post_meta($post_id, '_phone', $phone);
     update_post_meta($post_id, '_vehicle_type', $vehicle_type);
     update_post_meta($post_id, '_vehicle',$Vehicle);
     update_post_meta($post_id, '_starting_price',$starting_price_per_day);
     update_post_meta($post_id, '_starting_date',$starting_date);
     update_post_meta($post_id, '_status','Pending');
    /***********mail to admin*******/
    $admin_user = get_users('role=Administrator');
    //print_r($blogusers);
    foreach ($admin_user as $user) {
      $admin_user_email =  $user->user_email;
    } 
    $to_admin = $admin_user_email;
    $subject_admin = "Booking Request";
    $message_admin = "A new Booking Request has been sybmittes";

    wp_mail( $to_admin, $subject_admin, $message_admin );
    /*****mail to custoner ***********/
    $to_customer = $email;
    $subject_customer = "Booking Request";
    $message_customer = "Your Request for the Booking has been submitted you will get the response as soon as possible .Thank you !!";

    wp_mail( $to_customer, $subject_customer, $message_customer );
    }

  }
}
add_action( 'init', 'vb_booking_data_insert' );
/**
 * Plugin ajax on selction vehicle type
 */
add_action( "wp_ajax_nopriv_vb_vehicle_select_ajax", "vb_vehicle_select_ajax" );
add_action( "wp_ajax_vb_vehicle_select_ajax", "vb_vehicle_select_ajax" );
function vb_vehicle_select_ajax(){
  if (isset($_POST['vehicle_selected'])) {
    $vehicle_selected = $_POST['vehicle_selected'];
     $vehicle_selected_post = get_posts(array(
      'showposts' => -1, //add -1 if you want to show all posts
      'post_type' => 'vehicle_cpt',
      'tax_query' => array(
                  array(
                        'taxonomy' => 'vb_category',
                        'field' => 'slug',
                        'terms' => $vehicle_selected //pass your term name here
                          )
                        ))
                       );

        foreach ($vehicle_selected_post as $selected_post) {
        echo '<option value="'.$selected_post->post_title.'" class="vehicle_selected_name">' . $selected_post->post_title . '</option>';} 

    
  }

die();
}
/**
 * Plugin ajax on selction vehicle name
 */
add_action( "wp_ajax_nopriv_vb_vehicle__name_select_ajax", "vb_vehicle__name_select_ajax" );
add_action( "wp_ajax_vb_vehicle__name_select_ajax", "vb_vehicle__name_select_ajax" );
function vb_vehicle__name_select_ajax(){
  if (isset($_POST['vehicle_name'])) {
    $vehicle_name = $_POST['vehicle_name'];
    $post = get_page_by_title( $vehicle_name, OBJECT, 'vehicle_cpt' );
    $post_id =  $post->ID;
    echo get_post_meta( $post_id, "_starting_price_per_day", $single = true );

    
  }

die();
}
