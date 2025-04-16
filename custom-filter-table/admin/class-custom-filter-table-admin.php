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
    
    /**
     * Render records per page field
     */
    public function render_records_per_page_field() {
        $options = get_option('custom_filter_table_settings');
        $value = isset($options['records_per_page']) ? $options['records_per_page'] : 10;
        
        echo '<input type="number" name="custom_filter_table_settings[records_per_page]" value="' . esc_attr($value) . '" class="small-text" min="1" max="100">';
    }
    
    /**
     * Add meta boxes for custom post type
     */
    public function add_meta_boxes() {
        add_meta_box(
            'custom_filter_table_entry_details',
            __('Entry Details', 'custom-filter-table'),
            array($this, 'render_entry_details_meta_box'),
            'cft_table_entry',
            'normal',
            'high'
        );
    }
    
    /**
     * Render entry details meta box
     */
    public function render_entry_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('custom_filter_table_save_meta', 'custom_filter_table_meta_nonce');
        
        // Get saved values
        $municipio = get_post_meta($post->ID, '_cft_municipio', true);
        $data_publicacao = get_post_meta($post->ID, '_cft_data_publicacao', true);
        $prazo_final = get_post_meta($post->ID, '_cft_prazo_final', true);
        $situacao = get_post_meta($post->ID, '_cft_situacao', true);
        $numero_edital = get_post_meta($post->ID, '_cft_numero_edital', true);
        $projeto = get_post_meta($post->ID, '_cft_projeto', true);
        $objeto = get_post_meta($post->ID, '_cft_objeto', true);
        $arquivo_url = get_post_meta($post->ID, '_cft_arquivo_url', true);
        
        // Format dates
        $data_publicacao_formatted = !empty($data_publicacao) ? date('d/m/Y', strtotime($data_publicacao)) : '';
        $prazo_final_formatted = !empty($prazo_final) ? date('d/m/Y', strtotime($prazo_final)) : '';
        
        ?>
        <div class="custom-filter-table-meta-box">
            <div class="form-field">
                <label for="cft_municipio">
                    <?php _e('Município', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_municipio" name="cft_municipio" value="<?php echo esc_attr($municipio); ?>" />
            </div>
            
            <div class="form-field">
                <label for="cft_data_publicacao">
                    <?php _e('Data de Publicação', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_data_publicacao" name="cft_data_publicacao" value="<?php echo esc_attr($data_publicacao_formatted); ?>" class="datepicker" placeholder="dd/mm/aaaa" />
            </div>
            
            <div class="form-field">
                <label for="cft_prazo_final">
                    <?php _e('Prazo Final', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_prazo_final" name="cft_prazo_final" value="<?php echo esc_attr($prazo_final_formatted); ?>" class="datepicker" placeholder="dd/mm/aaaa" />
            </div>
            
            <div class="form-field">
                <label for="cft_situacao">
                    <?php _e('Situação', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_situacao" name="cft_situacao" value="<?php echo esc_attr($situacao); ?>" />
            </div>
            
            <div class="form-field">
                <label for="cft_numero_edital">
                    <?php _e('Nº do Edital', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_numero_edital" name="cft_numero_edital" value="<?php echo esc_attr($numero_edital); ?>" />
            </div>
            
            <div class="form-field">
                <label for="cft_projeto">
                    <?php _e('Projeto', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_projeto" name="cft_projeto" value="<?php echo esc_attr($projeto); ?>" />
            </div>
            
            <div class="form-field">
                <label for="cft_objeto">
                    <?php _e('Objeto', 'custom-filter-table'); ?>:
                </label>
                <textarea id="cft_objeto" name="cft_objeto" rows="4"><?php echo esc_textarea($objeto); ?></textarea>
            </div>
            
            <div class="form-field">
                <label for="cft_arquivo_url">
                    <?php _e('URL do Arquivo', 'custom-filter-table'); ?>:
                </label>
                <input type="text" id="cft_arquivo_url" name="cft_arquivo_url" value="<?php echo esc_url($arquivo_url); ?>" />
                <button type="button" class="button" id="cft_upload_button">
                    <?php _e('Upload File', 'custom-filter-table'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save post meta data
     */
    public function save_post_meta($post_id, $post) {
        // Check if nonce is set
        if (!isset($_POST['custom_filter_table_meta_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['custom_filter_table_meta_nonce'], 'custom_filter_table_save_meta')) {
            return;
        }
        
        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check the user's permissions
        if ('cft_table_entry' == $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        // Update post meta
        if (isset($_POST['cft_municipio'])) {
            update_post_meta($post_id, '_cft_municipio', sanitize_text_field($_POST['cft_municipio']));
        }
        
        if (isset($_POST['cft_data_publicacao'])) {
            $data_publicacao = sanitize_text_field($_POST['cft_data_publicacao']);
            if (!empty($data_publicacao)) {
                $date = DateTime::createFromFormat('d/m/Y', $data_publicacao);
                if ($date) {
                    $formatted_date = $date->format('Y-m-d');
                    update_post_meta($post_id, '_cft_data_publicacao', $formatted_date);
                }
            }
        }
        
        if (isset($_POST['cft_prazo_final'])) {
            $prazo_final = sanitize_text_field($_POST['cft_prazo_final']);
            if (!empty($prazo_final)) {
                $date = DateTime::createFromFormat('d/m/Y', $prazo_final);
                if ($date) {
                    $formatted_date = $date->format('Y-m-d');
                    update_post_meta($post_id, '_cft_prazo_final', $formatted_date);
                }
            }
        }
        
        if (isset($_POST['cft_situacao'])) {
            update_post_meta($post_id, '_cft_situacao', sanitize_text_field($_POST['cft_situacao']));
        }
        
        if (isset($_POST['cft_numero_edital'])) {
            update_post_meta($post_id, '_cft_numero_edital', sanitize_text_field($_POST['cft_numero_edital']));
        }
        
        if (isset($_POST['cft_projeto'])) {
            update_post_meta($post_id, '_cft_projeto', sanitize_text_field($_POST['cft_projeto']));
        }
        
        if (isset($_POST['cft_objeto'])) {
            update_post_meta($post_id, '_cft_objeto', sanitize_textarea_field($_POST['cft_objeto']));
        }
        
        if (isset($_POST['cft_arquivo_url'])) {
            update_post_meta($post_id, '_cft_arquivo_url', esc_url_raw($_POST['cft_arquivo_url']));
        }
        
        // Also save data to custom table
        $this->sync_post_with_table($post_id);
    }
    
    /**
     * Sync post data with custom table
     */
    private function sync_post_with_table($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_filter_table';
        
        // Get post meta
        $municipio = get_post_meta($post_id, '_cft_municipio', true);
        $data_publicacao = get_post_meta($post_id, '_cft_data_publicacao', true);
        $prazo_final = get_post_meta($post_id, '_cft_prazo_final', true);
        $situacao = get_post_meta($post_id, '_cft_situacao', true);
        $numero_edital = get_post_meta($post_id, '_cft_numero_edital', true);
        $projeto = get_post_meta($post_id, '_cft_projeto', true);
        $objeto = get_post_meta($post_id, '_cft_objeto', true);
        $arquivo_url = get_post_meta($post_id, '_cft_arquivo_url', true);
        
        // Check if entry exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE post_id = %d",
            $post_id
        ));
        
        $data = array(
            'post_id' => $post_id,
            'municipio' => $municipio,
            'data_publicacao' => $data_publicacao,
            'prazo_final' => $prazo_final,
            'situacao' => $situacao,
            'numero_edital' => $numero_edital,
            'projeto' => $projeto,
            'objeto' => $objeto,
            'arquivo_url' => $arquivo_url,
        );
        
        if ($exists) {
            // Update existing record
            $wpdb->update(
                $table_name,
                $data,
                array('post_id' => $post_id)
            );
        } else {
            // Insert new record
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Render main admin page
     */
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="custom-filter-table-admin-container">
                <div class="custom-filter-table-admin-intro">
                    <p><?php _e('Welcome to Custom Filter Table plugin!', 'custom-filter-table'); ?></p>
                    <p><?php _e('This plugin allows you to create filterable tables for your WordPress site.', 'custom-filter-table'); ?></p>
                    <p><?php _e('Use the following shortcode to display the table on any page or post:', 'custom-filter-table'); ?></p>
                    <code>[custom_filter_table]</code>
                </div>
                
                <div class="custom-filter-table-admin-usage">
                    <h2><?php _e('Usage Instructions', 'custom-filter-table'); ?></h2>
                    <ol>
                        <li><?php _e('Add entries to the database using the "Table Entries" menu or import from CSV.', 'custom-filter-table'); ?></li>
                        <li><?php _e('Configure plugin settings using the "Settings" menu.', 'custom-filter-table'); ?></li>
                        <li><?php _e('Add the shortcode [custom_filter_table] to any page or post.', 'custom-filter-table'); ?></li>
                    </ol>
                </div>
                
                <div class="custom-filter-table-admin-shortcode">
                    <h2><?php _e('Shortcode Options', 'custom-filter-table'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Option', 'custom-filter-table'); ?></th>
                            <th><?php _e('Description', 'custom-filter-table'); ?></th>
                            <th><?php _e('Example', 'custom-filter-table'); ?></th>
                        </tr>
                        <tr>
                            <td>title</td>
                            <td><?php _e('Set a custom title for the table', 'custom-filter-table'); ?></td>
                            <td><code>[custom_filter_table title="My Custom Table"]</code></td>
                        </tr>
                        <tr>
                            <td>municipio</td>
                            <td><?php _e('Filter by default município', 'custom-filter-table'); ?></td>
                            <td><code>[custom_filter_table municipio="Sorocaba/SP"]</code></td>
                        </tr>
                        <tr>
                            <td>situacao</td>
                            <td><?php _e('Filter by default situação', 'custom-filter-table'); ?></td>
                            <td><code>[custom_filter_table situacao="Encerrada"]</code></td>
                        </tr>
                        <tr>
                            <td>limit</td>
                            <td><?php _e('Limit number of results', 'custom-filter-table'); ?></td>
                            <td><code>[custom_filter_table limit="10"]</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('custom-filter-table-settings-group');
                do_settings_sections('custom-filter-table-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render import page
     */
    public function render_import_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="custom-filter-table-import-container">
                <div class="custom-filter-table-import-form">
                    <h2><?php _e('Import Data from CSV', 'custom-filter-table'); ?></h2>
                    
                    <p><?php _e('Upload a CSV file to import data into the table.', 'custom-filter-table'); ?></p>
                    <p><?php _e('The CSV file should have the following columns:', 'custom-filter-table'); ?></p>
                    
                    <ol>
                        <li><?php _e('Município', 'custom-filter-table'); ?></li>
                        <li><?php _e('Data de Publicação (DD/MM/YYYY)', 'custom-filter-table'); ?></li>
                        <li><?php _e('Prazo Final (DD/MM/YYYY)', 'custom-filter-table'); ?></li>
                        <li><?php _e('Situação', 'custom-filter-table'); ?></li>
                        <li><?php _e('Número do Edital', 'custom-filter-table'); ?></li>
                        <li><?php _e('Projeto', 'custom-filter-table'); ?></li>
                        <li><?php _e('Objeto', 'custom-filter-table'); ?></li>
                        <li><?php _e('URL do Arquivo (opcional)', 'custom-filter-table'); ?></li>
                    </ol>
                    
                    <div class="form-field">
                        <label for="csv_file"><?php _e('Select CSV File', 'custom-filter-table'); ?></label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" />
                    </div>
                    
                    <div class="form-field">
                        <button type="button" id="cft_import_button" class="button button-primary">
                            <?php _e('Import CSV', 'custom-filter-table'); ?>
                        </button>
                        <span class="spinner import-spinner"></span>
                    </div>
                    
                    <div id="import_results" class="import-results"></div>
                </div>
                
                <div class="custom-filter-table-sample-csv">
                    <h2><?php _e('Sample CSV', 'custom-filter-table'); ?></h2>
                    <p><?php _e('Download a sample CSV file to see the required format:', 'custom-filter-table'); ?></p>
                    <a href="<?php echo CFT_PLUGIN_URL . 'assets/sample/sample.csv'; ?>" class="button">
                        <?php _e('Download Sample CSV', 'custom-filter-table'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on plugin pages
        if (strpos($hook, 'custom-filter-table') === false && get_post_type() !== 'cft_table_entry') {
            return;
        }
        
        // Enqueue jQuery UI datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', CFT_PLUGIN_URL . 'assets/css/jquery-ui.min.css');
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        // Enqueue admin styles
        wp_enqueue_style('custom-filter-table-admin-styles', CFT_PLUGIN_URL . 'assets/css/admin.css', array(), CFT_VERSION);
        
        // Enqueue admin scripts
        wp_enqueue_script('custom-filter-table-admin-script', CFT_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), CFT_VERSION, true);
        
        // Localize script
        wp_localize_script('custom-filter-table-admin-script', 'custom_filter_table_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom-filter-table-admin-nonce'),
            'import_success' => __('CSV imported successfully! Number of records imported: ', 'custom-filter-table'),
            'import_error' => __('Error importing CSV: ', 'custom-filter-table'),
            'confirm_import' => __('Are you sure you want to import this CSV? This will add new records to the database.', 'custom-filter-table')
        ));
    }
    
    /**
     * Handle CSV import via AJAX
     */
    public function handle_csv_import() {
        // Check nonce
        check_ajax_referer('custom-filter-table-admin-nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to import data.', 'custom-filter-table'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || empty($_FILES['csv_file']['tmp_name'])) {
            wp_send_json_error(__('No file was uploaded.', 'custom-filter-table'));
        }
        
        // Get file path
        $file_path = $_FILES['csv_file']['tmp_name'];
        
        // Import CSV
        $plugin = new Custom_Filter_Table();
        $imported = $plugin->import_csv($file_path);
        
        if (is_wp_error($imported)) {
            wp_send_json_error($imported->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => sprintf(__('CSV imported successfully! %d records imported.', 'custom-filter-table'), $imported)
            ));
        }
    }
}