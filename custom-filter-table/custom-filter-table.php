<?php
/**
 * Plugin Name: Custom Filter Table
 * Plugin URI: https://yourwebsite.com/plugins/custom-filter-table
 * Description: A custom WordPress plugin that creates filterable tables for displaying data with advanced search functionality.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: custom-filter-table
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CFT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CFT_VERSION', '1.0.0');

// Include required files
require_once CFT_PLUGIN_DIR . 'includes/class-custom-filter-table.php';
require_once CFT_PLUGIN_DIR . 'includes/class-custom-filter-table-shortcode.php';
require_once CFT_PLUGIN_DIR . 'admin/class-custom-filter-table-admin.php';

// Initialize the plugin
function custom_filter_table_init() {
    // Load text domain for translations
    load_plugin_textdomain('custom-filter-table', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize the main plugin class
    $plugin = new Custom_Filter_Table();
    $plugin->init();
    
    // Initialize the admin class if in admin area
    if (is_admin()) {
        $admin = new Custom_Filter_Table_Admin();
        $admin->init();
    }
    
    // Register the shortcode
    $shortcode = new Custom_Filter_Table_Shortcode();
    $shortcode->init();
}
add_action('plugins_loaded', 'custom_filter_table_init');

// Activation hook
register_activation_hook(__FILE__, 'custom_filter_table_activate');
function custom_filter_table_activate() {
    // Create custom database tables if needed
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'custom_filter_table';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        municipio varchar(100) NOT NULL,
        data_publicacao date NOT NULL,
        prazo_final date NOT NULL,
        situacao varchar(50) NOT NULL,
        numero_edital varchar(50) NOT NULL,
        projeto varchar(100) NOT NULL,
        objeto text NOT NULL,
        arquivo_url varchar(255),
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add default options
    add_option('custom_filter_table_version', CFT_VERSION);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'custom_filter_table_deactivate');
function custom_filter_table_deactivate() {
    // Clean up if needed
}

// Enqueue scripts and styles
function custom_filter_table_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('custom-filter-table-styles', CFT_PLUGIN_URL . 'assets/css/custom-filter-table.css', array(), CFT_VERSION);
    
    // Enqueue scripts
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('custom-filter-table-script', CFT_PLUGIN_URL . 'assets/js/custom-filter-table.js', array('jquery'), CFT_VERSION, true);
    
    // Localize script with data
    wp_localize_script('custom-filter-table-script', 'custom_filter_table_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('custom-filter-table-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'custom_filter_table_enqueue_scripts');

// Add AJAX handlers
function custom_filter_table_ajax_filter() {
    check_ajax_referer('custom-filter-table-nonce', 'nonce');
    
    // Get filter parameters
    $municipio = isset($_POST['municipio']) ? sanitize_text_field($_POST['municipio']) : '';
    $situacao = isset($_POST['situacao']) ? sanitize_text_field($_POST['situacao']) : '';
    $data_inicio = isset($_POST['data_inicio']) ? sanitize_text_field($_POST['data_inicio']) : '';
    $data_fim = isset($_POST['data_fim']) ? sanitize_text_field($_POST['data_fim']) : '';
    
    // Build query
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_filter_table';
    
    $query = "SELECT * FROM $table_name WHERE 1=1";
    
    if (!empty($municipio)) {
        $query .= $wpdb->prepare(" AND municipio = %s", $municipio);
    }
    
    if (!empty($situacao)) {
        $query .= $wpdb->prepare(" AND situacao = %s", $situacao);
    }
    
    if (!empty($data_inicio)) {
        $query .= $wpdb->prepare(" AND data_publicacao >= %s", $data_inicio);
    }
    
    if (!empty($data_fim)) {
        $query .= $wpdb->prepare(" AND prazo_final <= %s", $data_fim);
    }
    
    // Get results
    $results = $wpdb->get_results($query);
    
    // Build HTML table
    $output = '';
    if ($results) {
        $output .= '<table class="custom-filter-table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>MUNICÍPIO</th>';
        $output .= '<th>DATA DE PUBLICAÇÃO</th>';
        $output .= '<th>PRAZO FINAL</th>';
        $output .= '<th>SITUAÇÃO</th>';
        $output .= '<th>Nº DO EDITAL</th>';
        $output .= '<th>PROJETO</th>';
        $output .= '<th>OBJETO</th>';
        $output .= '<th>ACESSE</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        
        foreach ($results as $row) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($row->municipio) . '</td>';
            $output .= '<td>' . date('d/m/Y', strtotime($row->data_publicacao)) . '</td>';
            $output .= '<td>' . date('d/m/Y', strtotime($row->prazo_final)) . '</td>';
            $output .= '<td>' . esc_html($row->situacao) . '</td>';
            $output .= '<td>' . esc_html($row->numero_edital) . '</td>';
            $output .= '<td>' . esc_html($row->projeto) . '</td>';
            $output .= '<td>' . esc_html($row->objeto) . '</td>';
            $output .= '<td>';
            if (!empty($row->arquivo_url)) {
                $output .= '<a href="' . esc_url($row->arquivo_url) . '" class="download-button" download><img src="' . CFT_PLUGIN_URL . 'assets/images/download-icon.svg" alt="Download"></a>';
            }
            $output .= '</td>';
            $output .= '</tr>';
        }
        
        $output .= '</tbody>';
        $output .= '</table>';
    } else {
        $output = '<p class="no-results">Nenhum resultado encontrado. Por favor, tente outros critérios de busca.</p>';
    }
    
    echo $output;
    wp_die();
}
add_action('wp_ajax_custom_filter_table_ajax_filter', 'custom_filter_table_ajax_filter');
add_action('wp_ajax_nopriv_custom_filter_table_ajax_filter', 'custom_filter_table_ajax_filter');