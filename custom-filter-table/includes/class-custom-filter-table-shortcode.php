<?php
/**
 * Shortcode handler for Custom Filter Table
 */
class Custom_Filter_Table_Shortcode {
    /**
     * Initialize the class
     */
    public function init() {
        add_shortcode('custom_filter_table', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the shortcode
     */
    public function render_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'title' => '',
            'municipio' => '',
            'situacao' => '',
            'limit' => -1,
        ), $atts, 'custom_filter_table');
        
        // Start output buffering
        ob_start();
        
        // Get main plugin instance
        $plugin = new Custom_Filter_Table();
        
        // Get filter options
        $municipalities = $plugin->get_municipalities();
        $situations = $plugin->get_situations();
        
        // Display filter form
        $this->render_filter_form($municipalities, $situations);
        
        // Display table with initial data
        $this->render_initial_table($atts);
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Render the filter form
     */
    private function render_filter_form($municipalities, $situations) {
        ?>
        <div class="custom-filter-table-container">
            <div class="filter-form-container">
                <div class="filter-form">
                    <div class="filter-row">
                        <div class="filter-column">
                            <div class="filter-label">MUNICÍPIO COM VAGA</div>
                            <div class="filter-input">
                                <select id="filter-municipio" class="filter-select">
                                    <option value="">Selecione o município</option>
                                    <?php foreach ($municipalities as $municipio) : ?>
                                        <option value="<?php echo esc_attr($municipio); ?>"><?php echo esc_html($municipio); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-column">
                            <div class="filter-label">SITUAÇÃO DO EDITAL</div>
                            <div class="filter-input">
                                <select id="filter-situacao" class="filter-select">
                                    <option value="">Selecione a situação</option>
                                    <?php foreach ($situations as $situacao) : ?>
                                        <option value="<?php echo esc_attr($situacao); ?>"><?php echo esc_html($situacao); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-column">
                            <div class="filter-label">DATA DA PUBLICAÇÃO</div>
                            <div class="filter-input">
                                <input type="text" id="filter-data-inicio" class="datepicker" placeholder="dd/mm/aaaa">
                            </div>
                        </div>
                        
                        <div class="filter-column">
                            <div class="filter-label">DATA FINAL</div>
                            <div class="filter-input">
                                <input type="text" id="filter-data-fim" class="datepicker" placeholder="dd/mm/aaaa">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="custom-filter-table-results" class="table-results">
                <!-- Table will be loaded here -->
                <div class="loading" style="display: none;">Carregando...</div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the initial table with data
     */
    private function render_initial_table($atts) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_filter_table';
        
        // Build query
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $params = array();
        
        if (!empty($atts['municipio'])) {
            $query .= " AND municipio = %s";
            $params[] = $atts['municipio'];
        }
        
        if (!empty($atts['situacao'])) {
            $query .= " AND situacao = %s";
            $params[] = $atts['situacao'];
        }
        
        if (!empty($atts['limit']) && $atts['limit'] > 0) {
            $query .= " LIMIT %d";
            $params[] = $atts['limit'];
        }
        
        // Prepare query if parameters exist
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        // Get results
        $results = $wpdb->get_results($query);
        
        // Display table
        if ($results) {
            ?>
            <table class="custom-filter-table">
                <thead>
                    <tr>
                        <th>MUNICÍPIO</th>
                        <th>DATA DE PUBLICAÇÃO</th>
                        <th>PRAZO FINAL</th>
                        <th>SITUAÇÃO</th>
                        <th>Nº DO EDITAL</th>
                        <th>PROJETO</th>
                        <th>OBJETO</th>
                        <th>ACESSE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row->municipio); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row->data_publicacao)); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row->prazo_final)); ?></td>
                            <td><?php echo esc_html($row->situacao); ?></td>
                            <td><?php echo esc_html($row->numero_edital); ?></td>
                            <td><?php echo esc_html($row->projeto); ?></td>
                            <td><?php echo esc_html($row->objeto); ?></td>
                            <td>
                                <?php if (!empty($row->arquivo_url)) : ?>
                                    <a href="<?php echo esc_url($row->arquivo_url); ?>" class="download-button" download>
                                        <img src="<?php echo CFT_PLUGIN_URL; ?>assets/images/download-icon.svg" alt="Download">
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p class="no-results">Nenhum resultado encontrado. Por favor, tente outros critérios de busca.</p>';
        }
    }
}