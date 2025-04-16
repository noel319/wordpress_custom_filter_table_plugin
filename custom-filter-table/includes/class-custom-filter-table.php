<?php
/**
 * Main plugin class for Custom Filter Table
 */
class Custom_Filter_Table {
    /**
     * Initialize the class
     */
    public function init() {
        // Register custom post type if needed
        $this->register_post_type();
        
        // Register custom taxonomies if needed
        $this->register_taxonomies();
    }
    
    /**
     * Register custom post type for storing table data
     */
    private function register_post_type() {
        $labels = array(
            'name'               => _x('Table Entries', 'post type general name', 'custom-filter-table'),
            'singular_name'      => _x('Table Entry', 'post type singular name', 'custom-filter-table'),
            'menu_name'          => _x('Table Entries', 'admin menu', 'custom-filter-table'),
            'name_admin_bar'     => _x('Table Entry', 'add new on admin bar', 'custom-filter-table'),
            'add_new'            => _x('Add New', 'entry', 'custom-filter-table'),
            'add_new_item'       => __('Add New Entry', 'custom-filter-table'),
            'new_item'           => __('New Entry', 'custom-filter-table'),
            'edit_item'          => __('Edit Entry', 'custom-filter-table'),
            'view_item'          => __('View Entry', 'custom-filter-table'),
            'all_items'          => __('All Entries', 'custom-filter-table'),
            'search_items'       => __('Search Entries', 'custom-filter-table'),
            'parent_item_colon'  => __('Parent Entries:', 'custom-filter-table'),
            'not_found'          => __('No entries found.', 'custom-filter-table'),
            'not_found_in_trash' => __('No entries found in Trash.', 'custom-filter-table')
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Custom table entries for filterable data', 'custom-filter-table'),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'table-entry'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-editor-table',
            'supports'           => array('title')
        );
        
        register_post_type('cft_table_entry', $args);
    }
    
    /**
     * Register custom taxonomies for filtering
     */
    private function register_taxonomies() {
        // Município taxonomy
        $labels = array(
            'name'                       => _x('Municípios', 'taxonomy general name', 'custom-filter-table'),
            'singular_name'              => _x('Município', 'taxonomy singular name', 'custom-filter-table'),
            'search_items'               => __('Search Municípios', 'custom-filter-table'),
            'popular_items'              => __('Popular Municípios', 'custom-filter-table'),
            'all_items'                  => __('All Municípios', 'custom-filter-table'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit Município', 'custom-filter-table'),
            'update_item'                => __('Update Município', 'custom-filter-table'),
            'add_new_item'               => __('Add New Município', 'custom-filter-table'),
            'new_item_name'              => __('New Município Name', 'custom-filter-table'),
            'separate_items_with_commas' => __('Separate municípios with commas', 'custom-filter-table'),
            'add_or_remove_items'        => __('Add or remove municípios', 'custom-filter-table'),
            'choose_from_most_used'      => __('Choose from the most used municípios', 'custom-filter-table'),
            'not_found'                  => __('No municípios found.', 'custom-filter-table'),
            'menu_name'                  => __('Municípios', 'custom-filter-table'),
        );
        
        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => array('slug' => 'municipio'),
        );
        
        register_taxonomy('cft_municipio', 'cft_table_entry', $args);
        
        // Situação taxonomy
        $labels = array(
            'name'                       => _x('Situações', 'taxonomy general name', 'custom-filter-table'),
            'singular_name'              => _x('Situação', 'taxonomy singular name', 'custom-filter-table'),
            'search_items'               => __('Search Situações', 'custom-filter-table'),
            'popular_items'              => __('Popular Situações', 'custom-filter-table'),
            'all_items'                  => __('All Situações', 'custom-filter-table'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit Situação', 'custom-filter-table'),
            'update_item'                => __('Update Situação', 'custom-filter-table'),
            'add_new_item'               => __('Add New Situação', 'custom-filter-table'),
            'new_item_name'              => __('New Situação Name', 'custom-filter-table'),
            'separate_items_with_commas' => __('Separate situações with commas', 'custom-filter-table'),
            'add_or_remove_items'        => __('Add or remove situações', 'custom-filter-table'),
            'choose_from_most_used'      => __('Choose from the most used situações', 'custom-filter-table'),
            'not_found'                  => __('No situações found.', 'custom-filter-table'),
            'menu_name'                  => __('Situações', 'custom-filter-table'),
        );
        
        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => array('slug' => 'situacao'),
        );
        
        register_taxonomy('cft_situacao', 'cft_table_entry', $args);
    }
    
    /**
     * Get all available municipalities for filtering
     */
    public function get_municipalities() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_filter_table';
        
        $results = $wpdb->get_col("SELECT DISTINCT municipio FROM $table_name ORDER BY municipio ASC");
        
        return $results;
    }
    
    /**
     * Get all available situations for filtering
     */
    public function get_situations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_filter_table';
        
        $results = $wpdb->get_col("SELECT DISTINCT situacao FROM $table_name ORDER BY situacao ASC");
        
        return $results;
    }
    
    /**
     * Import data from CSV
     */
    public function import_csv($file) {
        if (!file_exists($file)) {
            return new WP_Error('file_not_found', __('The CSV file could not be found', 'custom-filter-table'));
        }
        
        $csv = array_map('str_getcsv', file($file));
        array_shift($csv); // Remove header row
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_filter_table';
        
        $imported = 0;
        foreach ($csv as $row) {
            $data = array(
                'municipio' => sanitize_text_field($row[0]),
                'data_publicacao' => $this->format_date($row[1]),
                'prazo_final' => $this->format_date($row[2]),
                'situacao' => sanitize_text_field($row[3]),
                'numero_edital' => sanitize_text_field($row[4]),
                'projeto' => sanitize_text_field($row[5]),
                'objeto' => sanitize_textarea_field($row[6]),
                'arquivo_url' => esc_url_raw($row[7]),
            );
            
            $wpdb->insert($table_name, $data);
            
            if ($wpdb->insert_id) {
                $imported++;
            }
        }
        
        return $imported;
    }
    
    /**
     * Format date from d/m/Y to Y-m-d for database storage
     */
    private function format_date($date_string) {
        if (empty($date_string)) {
            return '';
        }
        
        $date = DateTime::createFromFormat('d/m/Y', $date_string);
        
        if ($date) {
            return $date->format('Y-m-d');
        }
        
        return '';
    }
}