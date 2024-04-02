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