<?php

/**
 * Plugin Name: elicautilities
 * Plugin URI: https://elica-webservices.it/
 * Description: A small plugin to provide custom functionalities to elicathemeunderscores theme
 * Version: 1.0
 * Author: Elisabetta Carrara
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: elicautilities
 */

/**
 * Register Portfolio CPT, custom filelds, taxonomies &shortcode for carousel display
 */

add_action('init', 'register_portfolio_cpt');

function register_portfolio_cpt()
{

    $labels = array(
        'name'                => __('Portfolio Items'),
        'singular_name'       => __('Portfolio Item'),
        'menu_name'           => __('Portfolio'),
        'parent_item_colon'   => __('Parent Portfolio Item:'),
        'all_items'           => __('All Portfolio Items'),
        'view_item'           => __('View Portfolio Item'),
        'add_new_item'        => __('Add New Portfolio Item'),
        'add_new'             => __('Add New'),
        'edit_item'           => __('Edit Portfolio Item'),
        'update_item'         => __('Update Portfolio Item'),
        'search_items'        => __('Search Portfolio Items'),
        'not_found'           => __('No portfolio items found'),
        'not_found_in_trash'  => __('No portfolio items found in Trash'),
    );

    $args = array(
        'label'               => __('portfolio'),
        'description'         => __('Custom post type for portfolio'),
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'portfolio'),
        'menu_icon'           => 'dashicons-portfolio',
        'supports'            => array('title', 'thumbnail'),
        'show_in_rest'        => true,

    );
    register_post_type('portfolio', $args);
}

// Register custom fields (optional, but provides some benefits)
add_action('init', 'register_portfolio_custom_fields');

function register_portfolio_custom_fields()
{
    $args = array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
    );
    register_meta('post', 'portfolio_website_link', $args);
    register_meta('post', 'portfolio_customer', $args);
}

// Register the metabox
add_action('add_meta_boxes', 'register_portfolio_meta_box');

function register_portfolio_meta_box()
{
    add_meta_box(
        'portfolio_meta_box',
        __('Project Information'),
        'display_portfolio_meta_box',
        'portfolio',
        'normal',
        'high'
    );
}

function display_portfolio_meta_box($post)
{
?>
    <label for="portfolio_website_link">Client Website Link:</label>
    <input type="url" id="portfolio_website_link" name="portfolio_website_link" value="<?php echo esc_url(get_post_meta($post->ID, 'portfolio_website_link', true)); ?>">

    <h2><?php esc_html_e('Project Customer'); ?></h2>
    <label for="portfolio_customer">Customer Name:</label>
    <input type="text" id="portfolio_customer" name="portfolio_customer" value="<?php echo esc_attr(get_post_meta($post->ID, 'portfolio_customer', true)); ?>">

<?php
}

// Save the custom field data with sanitisation and validation
add_action('save_post', 'save_portfolio_meta_data', 10, 2); // Priority 10, second parameter is post object

function save_portfolio_meta_data($post_id, $post)
{

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and validate client website link (check if key exists first)
    $website_link = '';
    if (isset($_POST['portfolio_website_link'])) {
        $website_link = sanitize_url($_POST['portfolio_website_link']);
        if (!filter_var($website_link, FILTER_VALIDATE_URL)) {
            $website_link = ''; // Set to empty string if not a valid URL
            add_action('admin_notices', function () {
                echo '<div class="error"><p>Invalid website link provided.</p></div>';
            });
        }
    }
    update_post_meta($post_id, 'portfolio_website_link', $website_link);

    // Sanitize customer name (check if key exists first)
    $customer_name = '';
    if (isset($_POST['portfolio_customer'])) {
        $customer_name = sanitize_text_field($_POST['portfolio_customer']);
    }
    update_post_meta($post_id, 'portfolio_customer', $customer_name);
}


add_action('init', 'register_portfolio_taxonomies');

function register_services_taxonomies()
{

    $labels = array(
        'name'                       => __('Portfolio Categories'),
        'singular_name'              => __('Portfolio Category'),
        'menu_name'                   => __('Categories'),
        'all_items'                   => __('All Categories'),
        'edit_item'                   => __('Edit Category'),
        'update_item'                 => __('Update Category'),
        'add_new_item'                => __('Add New Category'),
        'new_item_name'               => __('New Category Name'),
        'parent_item'                 => __('Parent Category'),
        'parent_item_colon'           => __('Parent Category:'),
        'search_items'                => __('Search Categories'),
        'popular_items'               => __('Popular Categories'),
        'separate_items_with_commas'  => __('Separate categories with commas'),
        'add_or_remove_items'         => __('Add or remove categories'),
        'choose_from_most_used'       => __('Choose from most used categories'),
        'not_found'                   => __('No categories found'),
        'back_to_items'               => __('&laquo; Back to Categories'),
    );

    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array('slug' => 'portfolio-category'),
        'has_archive'           => true,
    );

    register_taxonomy('portfolio-category', array('portfolio'), $args);
}

// Register Custom Post Type - Services
add_action('init', 'register_services_cpt');

function register_services_cpt()
{
    $labels = array(
        'name'                => __('Services'),
        'singular_name'       => __('Service'),
        'menu_name'           => __('Services'),
        'parent_item_colon'   => __('Parent Service:'),
        'all_items'           => __('All Services'),
        'view_item'           => __('View Service'),
        'add_new_item'        => __('Add New Service'),
        'add_new'             => __('Add New'),
        'edit_item'           => __('Edit Service'),
        'update_item'         => __('Update Service'),
        'search_items'        => __('Search Services'),
        'not_found'           => __('No Services Found'),
        'not_found_in_trash'  => __('No Services Found in Trash'),
    );

    $args = array(
        'label'               => __('services'),
        'description'         => __('Custom post type for services'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor', 'thumbnail'),
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'services'),
        'menu_icon'           => 'dashicons-admin-tools',
        'show_in_rest'        => true, // Enable REST API for Services
    );

    register_post_type('services', $args);
}

add_action('init', 'register_services_taxonomies');

function register_portfolio_taxonomies()
{

    $labels = array(
        'name'                       => __('SErvices Categories'),
        'singular_name'              => __('Services Category'),
        'menu_name'                   => __('Categories'),
        'all_items'                   => __('All Categories'),
        'edit_item'                   => __('Edit Category'),
        'update_item'                 => __('Update Category'),
        'add_new_item'                => __('Add New Category'),
        'new_item_name'               => __('New Category Name'),
        'parent_item'                 => __('Parent Category'),
        'parent_item_colon'           => __('Parent Category:'),
        'search_items'                => __('Search Categories'),
        'popular_items'               => __('Popular Categories'),
        'separate_items_with_commas'  => __('Separate categories with commas'),
        'add_or_remove_items'         => __('Add or remove categories'),
        'choose_from_most_used'       => __('Choose from most used categories'),
        'not_found'                   => __('No categories found'),
        'back_to_items'               => __('&laquo; Back to Categories'),
    );

    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array('slug' => 'portfolio-category'),
        'has_archive'           => true,
    );

    register_taxonomy('services-category', array('services'), $args);
}

// Register Custom Metaboxes for Services
add_action('add_meta_boxes', 'register_services_metaboxes');

function register_services_metaboxes()
{
    add_meta_box(
        'service_details', // Unique ID for the metabox
        __('Service Details'), // Title displayed above the metabox
        'render_service_details_metabox', // Callback function to display metabox content
        'services', // Post type where the metabox should appear (services)
        'normal', // Context where the metabox should appear (normal)
        'high' // Priority (high, default, low)
    );
}

// Render Service Details Metabox Content
function render_service_details_metabox($post)
{
    // Retrieve existing values for fields
    $service_icon = get_post_meta($post->ID, 'service_icon', true);
    $service_price = get_post_meta($post->ID, 'service_price', true);
    $service_button_text = get_post_meta($post->ID, 'service_button_text', true);
    $service_button_link = get_post_meta($post->ID, 'service_button_link', true);
?>
    <p>
        <label for="service_icon"><?php esc_html_e('Icon (Dashicon Code):'); ?></label>
        <input type="text" name="service_icon" id="service_icon" value="<?php echo esc_attr($service_icon); ?>" />
    </p>
    <p>
        <label for="service_price"><?php esc_html_e('Price:'); ?></label>
        <input type="number" name="service_price" id="service_price" value="<?php echo esc_attr($service_price); ?>" />
        <select name="currency">
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
            <!-- Add more currency options as needed -->
        </select>
    </p>
    <p>
        <label for="service_button_text"><?php esc_html_e('Button Text:'); ?></label>
        <input type="text" name="service_button_text" id="service_button_text" value="<?php echo esc_attr($service_button_text); ?>" />
    </p>
    <p>
        <label for="service_button_link"><?php esc_html_e('Button Link:'); ?></label>
        <input type="url" name="service_button_link" id="service_button_link" value="<?php echo esc_url($service_button_link); ?>" />
    </p>
    <?php
}

// Save Service Details
add_action('save_post', 'save_service_details');

function save_service_details($post_id)
{
    // Check if nonce is set
    if (!isset($_POST['service_details_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['service_details_nonce'], basename(__FILE__))) {
        return;
    }

    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save custom fields
    if (isset($_POST['service_icon'])) {
        update_post_meta($post_id, 'service_icon', sanitize_text_field($_POST['service_icon']));
    }
    if (isset($_POST['service_price'])) {
        update_post_meta($post_id, 'service_price', sanitize_text_field($_POST['service_price']));
    }
    if (isset($_POST['service_button_text'])) {
        update_post_meta($post_id, 'service_button_text', sanitize_text_field($_POST['service_button_text']));
    }
    if (isset($_POST['service_button_link'])) {
        update_post_meta($post_id, 'service_button_link', esc_url_raw($_POST['service_button_link']));
    }
}

// Register shortcode for displaying portfolio items based on taxonomy
add_shortcode('portfolio_grid', 'portfolio_grid_shortcode');

function portfolio_grid_shortcode($atts)
{
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'category' => '', // Default to empty category (all categories)
    ), $atts);

    // Set up query arguments
    $args = array(
        'post_type' => 'portfolio',
        'posts_per_page' => -1, // Display all portfolio items
        'tax_query' => array(
            array(
                'taxonomy' => 'portfolio-category',
                'field' => 'slug',
                'terms' => $atts['category'],
            ),
        ),
    );

    // Query portfolio items
    $query = new WP_Query($args);

    // Start output buffer
    ob_start();

    // Check if there are portfolio items
    if ($query->have_posts()) {
    ?>
        <div class="portfolio-columns">
            <div class="portfolio-cards">
                <?php
                // Loop through portfolio items
                while ($query->have_posts()) {
                    $query->the_post();
                ?>
                    <?php get_template_part('portfolio-card', 'template-part'); ?>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
        // Restore original post data
        wp_reset_postdata();
    } else {
        // If no portfolio items found, display a message
        echo '<p>No portfolio items found.</p>';
    }

    // End output buffer and return content
    return ob_get_clean();
}

add_shortcode('service_grid', 'service_grid_shortcode');

function service_grid_shortcode($atts)
{
    // Extract shortcode attributes with default category set to empty string
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts);

    // Set up query arguments for services post type and filter by service-category taxonomy
    $args = array(
        'post_type' => 'service', // Replace 'portfolio' with 'service'
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'service-category', // Replace 'portfolio-category' with 'service-category'
                'field' => 'slug',
                'terms' => $atts['category'],
            ),
        ),
    );

    // Query service items
    $query = new WP_Query($args);

    // Start output buffer
    ob_start();

    // Check if there are service items
    if ($query->have_posts()) {
    ?>
        <div class="service-columns">
            <div class="service-cards">
                <?php
                // Loop through service items
                while ($query->have_posts()) {
                    $query->the_post();
                ?>
                    <?php get_template_part('service-card', 'template-part'); ?>
                <?php
                }
                ?>
            </div>
        </div>
<?php
        // Restore original post data
        wp_reset_postdata();
    } else {
        // If no service items found, display a message
        echo '<p>No service items found.</p>';
    }

    // End output buffer and return content
    return ob_get_clean();
}

// Register Agent role with Editor-like capabilities
add_role(
    'agent',
    __('Agent'),
    array(
        'edit_posts' => true, // Edit posts
        'edit_others_posts' => true, // Edit others' posts
        'publish_posts' => true, // Publish posts
        'manage_categories' => true, // Manage categories
        'upload_files' => true, // Upload files
        // Add other capabilities as needed for Agent role
    )
);

// Register Customer role with Author-like capabilities
add_role(
    'customer',
    __('Customer'),
    array(
        'write_posts' => true, // Write posts
        'edit_own_posts' => true, // Edit own posts
        'upload_files' => true, // Upload files (optional for Author-like role)
        // Add other capabilities as needed for Customer role
    )
);

// Restrict publish_posts capability for Agent and Customer roles
add_action('admin_init', 'restrict_publish_caps');
function restrict_publish_caps()
{
    $agent_role = get_role('agent');
    $customer_role = get_role('customer');

    $customer_role->remove_cap('publish_posts');
}
// Grant publish_posts for standard Posts, Tickets, and Replies (for Agents)
// Grant publish_posts for Tickets (for Customers)
add_action('admin_init', 'grant_publish_caps');
function grant_publish_caps()
{
    $agent_role = get_role('agent');
    $customer_role = get_role('customer');

    $agent_role->add_cap('publish_posts'); // Standard Posts (built-in)
    $agent_role->add_cap('publish_posts', 'ticket'); // Ticket CPT
    $agent_role->add_cap('publish_posts', 'reply');   // Reply CPT
    $customer_role->add_cap('publish_posts', 'ticket'); // Ticket CPT (for creating their own tickets)
    $customer_role->add_cap('publish_posts', 'reply');   // Reply CPT
}

// Function to remove capability from customer role
// Function to remove capability from customer role (modify this)
function remove_customer_priority_capability()
{
    $customer_role = get_role('customer'); // Get customer role object
    $customer_role->remove_cap('manage_ticket_priority'); // Remove custom capability
}

function create_ticket_cpt()
{

    $labels = array(
        'name'                => _x('Tickets', 'Post Type General Name', 'elicautilities'),
        'singular_name'       => _x('Ticket', 'Post Type Singular Name', 'elicautilities'),
        'menu_name'           => __('Tickets', 'elicautilities'),
        'parent_item_colon'   => __('Parent Ticket:', 'elicautilities'),
        'all_items'           => __('All Tickets', 'elicautilities'),
        'view_item'           => __('View Ticket', 'elicautilities'),
        'add_new_item'        => __('Add New Ticket', 'elicautilities'),
        'add_new'             => __('Add New', 'elicautilities'),
        'edit_item'           => __('Edit Ticket', 'elicautilities'),
        'update_item'         => __('Update Ticket', 'elicautilities'),
        'search_items'        => __('Search Tickets', 'elicautilities'),
        'not_found'           => __('No tickets found', 'elicautilities'),
        'not_found_in_trash'  => __('No tickets found in Trash', 'elicautilities'),
    );

    $args = array(
        'label'               => __('ticket', 'elicautilities'),
        'description'         => __('Tickets for events', 'elicautilities'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor'),
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'ticket'),
        'menu_icon'           => 'dashicons-tag',
    );

    register_post_type('ticket', $args);
}

add_action('init', 'create_ticket_cpt');

// Function to add the meta box
function add_ticket_metabox()
{
    add_meta_box(
        'ticket_metabox', // Unique ID
        __('Ticket Details', 'elicautilities'), // Title
        'ticket_metabox_callback', // Callback function
        'ticket', // Screen (your CPT slug)
        'normal', // Context
        'high' // Priority
    );
}

// Callback function to display the meta box content
function ticket_metabox_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'ticket_nonce'); // Security nonce

    // Get existing meta values
    $subject = get_post_meta($post->ID, 'subject', true);
    $type = get_post_meta($post->ID, 'type', true);
    $priority = get_post_meta($post->ID, 'priority', true); // Get existing priority

    // Subject field (text area)
    echo '<label for="subject">Subject:</label>';
    echo '<textarea id="subject" name="subject" rows="5" cols="50">' . esc_attr($subject) . '</textarea>';
    echo '<br><br>';

    // Type selector
    echo '<label for="type">Type:</label>';
    echo '<select id="type" name="type">';
    echo '<option value="commercial"' . selected($type, 'commercial', false) . '>Commercial</option>';
    echo '<option value="technical"' . selected($type, 'technical', false) . '>Technical</option>';
    echo '<option value="presales"' . selected($type, 'presales', false) . '>Presales</option>';
    echo '<option value="gdpr_requests"' . selected($type, 'gdpr_requests', false) . '>GDPR Requests</option>';
    echo '</select>';
    echo '<br><br>';

    // Priority selector
    $priority_terms = get_terms(array('taxonomy' => 'ticket_priority')); // Get all priority terms

    echo '<label for="priority">Priority:</label>';
    echo '<select id="priority" name="priority">';
    foreach ($priority_terms as $term) {
        echo '<option value="' . $term->slug . '"' . selected($priority, $term->slug, false) . '>' . $term->name . '</option>';
    }
    echo '</select>';
    echo '<br><br>';
}


// Save meta box data
add_action('save_post', 'save_ticket_metabox');

// Function to save meta box data
function save_ticket_metabox($post_id)
{
    // Verify nonce and autosave
    if (!isset($_POST['ticket_nonce']) || !wp_verify_nonce($_POST['ticket_nonce'], basename(__FILE__))) {
        return;
    }
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    // Check user capability (edit_terms for priority)
    if (!current_user_can('edit_terms')) {
        return; // Don't save priority if user cannot edit terms
    }

    // Sanitize and save subject field
    $subject = sanitize_textarea_field($_POST['subject']);
    update_post_meta($post_id, 'subject', $subject);

    // Sanitize and save type
    $type = sanitize_text_field($_POST['type']);
    update_post_meta($post_id, 'type', $type);

    // Sanitize and save priority (only if user can edit terms)
    if (current_user_can('edit_terms')) {
        $priority = sanitize_text_field($_POST['priority']);
        update_post_meta($post_id, 'priority', $priority);
    }
}

// Function to register custom taxonomies
function register_ticket_categories()
{

    $labels = array(
        'name'                       => _x('Categories', 'Taxonomy General Name', 'elicautilities'),
        'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'elicautilities'),
        'menu_name'                  => __('Categories', 'elicautilities'),
        'all_items'                  => __('All Categories', 'elicautilities'),
        'edit_item'                  => __('Edit Category', 'elicautilities'),
        'update_item'                 => __('Update Category', 'elicautilities'),
        'add_new_item'                => __('Add New Category', 'elicautilities'),
        'new_item_name'               => __('New Category Name', 'elicautilities'),
        'parent_item'                 => __('Parent Category', 'elicautilities'),
        'parent_item_colon'           => __('Parent Category:', 'elicautilities'),
        'search_items'                => __('Search Categories', 'elicautilities'),
        'popular_items'               => __('Popular Categories', 'elicautilities'),
        'separate_items_with_commas'  => __('Separate categories with commas', 'elicautilities'),
        'add_or_remove_items'         => __('Add or remove categories', 'elicautilities'),
        'choose_from_most_used'       => __('Choose from most used categories', 'elicautilities'),
        'not_found'                   => __('No categories found', 'elicautilities'),
        'hierarchical'                => true, // Set to true for hierarchical categories
        'label'                      => __('Category', 'elicautilities'), //Displayed name for singular category on taxonomies screen
        'rewrite'                     => array('slug' => 'ticket-category'), // Category slug in permalinks
    );

    $args = array(
        'labels'                     => $labels,
        'show_ui'                     => true,
        'show_in_menu'                 => true,
        'hierarchical'                => true, // Set to true for hierarchical categories
        'rewrite'                     => array('slug' => 'ticket-category'),
    );

    register_taxonomy('ticket_category', 'ticket', $args); // Register category taxonomy for 'ticket' CPT

}

// Hook to register taxonomies on init
add_action('init', 'register_ticket_categories');

// Function to register custom taxonomies (add this inside your existing function)
function register_ticket_tags()
{

    $labels = array(
        'name'                       => _x('Tags', 'Taxonomy General Name', 'elicautilities'),
        'singular_name'              => _x('Tag', 'Taxonomy Singular Name', 'elicautilities'),
        'menu_name'                  => __('Tags', 'elicautilities'),
        'all_items'                  => __('All Tags', 'elicautilities'),
        'edit_item'                  => __('Edit Tag', 'elicautilities'),
        'update_item'                 => __('Update Tag', 'elicautilities'),
        'add_new_item'                => __('Add New Tag', 'elicautilities'),
        'new_item_name'               => __('New Tag Name', 'elicautilities'),
        'search_items'                => __('Search Tags', 'elicautilities'),
        'popular_items'               => __('Popular Tags', 'elicautilities'),
        'separate_items_with_commas'  => __('Separate tags with commas', 'elicautilities'),
        'add_or_remove_items'         => __('Add or remove tags', 'elicautilities'),
        'choose_from_most_used'       => __('Choose from most used tags', 'elicautilities'),
        'not_found'                   => __('No tags found', 'elicautilities'),
        'hierarchical'                => false, // Set to false for tags (flat structure)
    );

    $args = array(
        'labels'                     => $labels,
        'show_ui'                     => true,
        'show_in_menu'                 => true,
        'hierarchical'                => false, // Set to false for tags (flat structure)
    );

    register_taxonomy('ticket_tag', 'ticket', $args); // Register tag taxonomy for 'ticket' CPT
}
// Hook to register taxonomies on init
add_action('init', 'register_ticket_tags');


// Function to register custom taxonomies (add this inside your existing function)
function register_ticket_priority()
{

    $priority_labels = array(
        'name'                       => _x('Priorities', 'Taxonomy General Name', 'elicautilities'),
        'singular_name'              => _x('Priority', 'Taxonomy Singular Name', 'elicautilities'),
        'menu_name'                  => __('Priorities', 'elicautilities'),
        'all_items'                  => __('All Priorities', 'elicautilities'),
        'edit_item'                  => __('Edit Priority', 'elicautilities'),
        'update_item'                 => __('Update Priority', 'elicautilities'),
        'add_new_item'                => __('Add New Priority', 'elicautilities'),
        'new_item_name'               => __('New Priority Name', 'elicautilities'),
        'search_items'                => __('Search Priorities', 'elicautilities'),
        'popular_items'               => __('Popular Priorities', 'elicautilities'),
        'separate_items_with_commas'  => __('Separate priorities with commas', 'elicautilities'),
        'add_or_remove_items'         => __('Add or remove priorities', 'elicautilities'),
        'choose_from_most_used'       => __('Choose from most used priorities', 'elicautilities'),
        'not_found'                   => __('No priorities found', 'elicautilities'),
    );

    $priority_args = array(
        'labels'                     => $priority_labels,
        'show_ui'                     => true,
        'show_in_menu'                 => true,
        'hierarchical'                => false, // Set to false for priorities (flat structure)
        'rewrite'                     => false, // Don't create rewrite rules (priorities won't have own URLs)
        'capabilities' => array(
            'manage_terms' => 'manage_ticket_priority', // Custom capability for priority terms
            'edit_terms'   => 'edit_ticket_priority',
            'delete_terms' => 'delete_ticket_priority',
        ),
    );

    register_taxonomy('ticket_priority', 'ticket', $priority_args); // Register priority taxonomy with custom capabilities
}
// Hook to register taxonomies on init
add_action('init', 'register_ticket_priority');

// Function to register the Reply CPT
function register_reply_cpt()
{

    $labels = array(
        'name'                => _x('Replies', 'Post Type General Name', 'elicautilities'),
        'singular_name'       => _x('Reply', 'Post Type Singular Name', 'elicautilities'),
        'menu_name'            => __('Replies', 'elicautilities'),
        'name_admin_bar'        => __('Reply', 'elicautilities'),
        'archives'            => __('Reply Archives', 'elicautilities'),
        'parent_item_colon'   => __('Parent Reply:', 'elicautilities'),
        'all_items'            => __('All Replies', 'elicautilities'),
        'add_new_item'          => __('Add New Reply', 'elicautilities'),
        'add_new'              => __('Add New Reply', 'elicautilities'),
        'edit_item'            => __('Edit Reply', 'elicautilities'),
        'update_item'           => __('Update Reply', 'elicautilities'),
        'view_item'            => __('View Reply', 'elicautilities'),
        'search_items'          => __('Search Replies', 'elicautilities'),
        'not_found'            => __('No replies found', 'elicautilities'),
        'not_found_in_trash'  => __('No replies found in Trash', 'elicautilities'),
        'featured_image'        => __('Featured Image', 'elicautilities'),
        'set_featured_image'    => __('Set featured image', 'elicautilities'),
        'remove_featured_image' => __('Remove featured image', 'elicautilities'),
        'use_featured_image'    => __('Use as featured image', 'elicautilities'),
        'menu_icon'             => 'dashicons-megaphone', // Change this if desired
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false, // Set to false to hide from main menu (optional)
        'show_ui'             => true,
        'show_in_menu'         => true,
        'capability_type'     => 'post',
        'has_archive'          => false, // Set to false to disable archive page (optional)
        'hierarchical'          => false, // Set to false for replies (not hierarchical)
        'supports'            => array('editor'), // Supports the Gutenberg editor
    );

    register_post_type('reply', $args);
}

// Hook to register CPT on init
add_action('init', 'register_reply_cpt');

// Function to add the meta box
function add_reply_metabox()
{
    add_meta_box(
        'reply_metabox', // Unique ID
        __('Reply Details', 'elicautilities'), // Title
        'reply_metabox_callback', // Callback function
        'reply', // Screen (your CPT slug)
        'normal', // Context
        'high' // Priority
    );
}

// Hook to add meta box on init
add_action('admin_init', 'add_reply_metabox');
// Callback function to display the meta box content
function reply_metabox_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'reply_nonce'); // Security nonce

    // Get existing meta value
    $subject = get_post_meta($post->ID, 'subject', true); // Get existing subject

    // Subject field (text input)
    echo '<label for="subject">Subject:</label>';
    echo '<input type="text" id="subject" name="subject" value="' . esc_attr($subject) . '">';
    echo '<br><br>';
}
// Function to save meta box data
function save_reply_metabox($post_id)
{
    // Verify nonce and autosave
    if (!isset($_POST['reply_nonce']) || !wp_verify_nonce($_POST['reply_nonce'], basename(__FILE__))) {
        return;
    }
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    // Sanitize and save subject field
    $subject = sanitize_text_field($_POST['subject']);
    update_post_meta($post_id, 'subject', $subject);
}

// Hook to save meta box data on post save
add_action('save_post_reply', 'save_reply_metabox');
