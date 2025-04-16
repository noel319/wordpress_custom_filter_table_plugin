<?php
/**
 * Admin functionality for Custom Filter Table
 */
class Custom_Filter_Table_Admin {
    /**
     * Initialize the class
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // Save post meta
        add_action('save_post', array($this, 'save_post_meta'), 10, 2);
        
        // Register admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_custom_filter_table_import_csv', array($this, 'handle_csv_import'));
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Add main menu
        add_menu_page(
            __('Custom Filter Table', 'custom-filter-table'),
            __('Filter Table', 'custom-filter-table'),
            'manage_options',
            'custom-filter-table',
            array($this, 'render_main_page'),
            'dashicons-editor-table',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'custom-filter-table',
            __('Settings', 'custom-filter-table'),
            __('Settings', 'custom-filter-table'),
            'manage_options',
            'custom-filter-table-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'custom-filter-table',
            __('Import CSV', 'custom-filter-table'),
            __('Import CSV', 'custom-filter-table'),
            'manage_options',
            'custom-filter-table-import',
            array($this, 'render_import_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('custom-filter-table-settings-group', 'custom_filter_table_settings');
        
        add_settings_section(
            'custom_filter_table_general_section',
            __('General Settings', 'custom-filter-table'),
            array($this, 'render_general_section_callback'),
            'custom-filter-table-settings'
        );
        
        add_settings_field(
            'table_title',
            __('Default Table Title', 'custom-filter-table'),
            array($this, 'render_table_title_field'),
            'custom-filter-table-settings',
            'custom_filter_table_general_section'
        );
        
        add_settings_field(
            'records_per_page',
            __('Records Per Page', 'custom-filter-table'),
            array($this, 'render_records_per_page_field'),
            'custom-filter-table-settings',
            'custom_filter_table_general_section'
        );
    }
    
    /**
     * Render the general section callback
     */
    public function render_general_section_callback() {
        echo '<p>' . __('Configure general settings for the Custom Filter Table plugin.', 'custom-filter-table') . '</p>';
    }
    
    /**
     * Render table title field
     */
    public function render_table_title_field() {
        $options = get_option('custom_filter_table_settings');
        $value = isset($options['table_title']) ? $options['table_title'] : '';
        
        echo '<input type="text" name="custom_filter_table_settings[table_title]" value="' . esc_attr($value) . '" class="regular-text">';
    }