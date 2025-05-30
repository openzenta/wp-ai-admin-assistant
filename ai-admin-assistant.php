<?php
/**
 * Plugin Name: AI Admin Assistant
 * Description: Integrates Claude.ai into the WordPress Admin Backend
 * Version: 1.0
 * Author: OpenZenta
 * License: GPL v2 or later
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Text Domain: ai-admin-assistant
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

class PROTECZONE_AI_Assistant {
    private $api_key;
    private $api_url = 'https://api.anthropic.com/v1/messages';
    private $model = 'claude-3-opus-20240229';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_ai_admin_assistant_message', array($this, 'handle_ai_message'));
        add_action('admin_init', array($this, 'register_settings'));
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        
        $this->api_key = get_option('ai_admin_assistant_api_key', '');
    }

    public function plugin_activation() {
        add_option('ai_admin_assistant_api_key', '');
    }

    public function register_settings() {
        register_setting('ai_admin_assistant_options', 'ai_admin_assistant_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }

    public function add_admin_menu() {
        add_menu_page(
            'AI Assistant',
            'AI Assistant',
            'manage_options',
            'ai-admin-assistant',
            array($this, 'admin_page'),
            'dashicons-admin-generic'
        );
        
        add_submenu_page(
            'ai-admin-assistant',
            'Settings',
            'Settings',
            'manage_options',
            'ai-admin-assistant-settings',
            array($this, 'settings_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_ai-admin-assistant' !== $hook) {
            return;
        }

        // Register and enqueue local copies of external libraries
        wp_register_script(
            'ai-admin-assistant-marked-js',
            plugins_url('assets/js/marked.min.js', __FILE__),
            array(),
            '4.0.2',
            true
        );

        wp_register_script(
            'ai-admin-assistant-highlight-js',
            plugins_url('assets/js/highlight.min.js', __FILE__),
            array(),
            '11.5.1',
            true
        );

        wp_register_style(
            'ai-admin-assistant-highlight-js-style',
            plugins_url('assets/css/default.min.css', __FILE__),
            array(),
            '11.5.1'
        );

        wp_enqueue_style(
            'ai-admin-assistant-style',
            plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'ai-admin-assistant-script',
            plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
            array('jquery', 'ai-admin-assistant-marked-js', 'ai-admin-assistant-highlight-js'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'ai-admin-assistant-script',
            'aiAdminAssistant',
            array(
                'nonce' => wp_create_nonce('ai_admin_assistant_nonce'),
                'ajaxurl' => esc_url(admin_url('admin-ajax.php'))
            )
        );
    }

    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'ai-admin-assistant'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('AI Assistant for Claude AI', 'ai-admin-assistant'); ?></h1>
            <?php if (empty($this->api_key)) : ?>
                <div class="notice notice-warning">
                    <p><?php 
                        printf(
                            /* translators: %s: Settings page URL */
                            esc_html__('Please configure your API key in the %s first.', 'ai-admin-assistant'),
                            '<a href="' . esc_url(admin_url('admin.php?page=ai-admin-assistant-settings')) . '">' . esc_html__('Settings', 'ai-admin-assistant') . '</a>'
                        );
                    ?></p>
                </div>
            <?php else : ?>
                <div class="ai-chat-container">
                    <div id="ai-chat-messages"></div>
                    <div class="ai-chat-input">
                        <textarea id="ai-user-input" placeholder="<?php echo esc_attr__('Your message to the AI Assistant...', 'ai-admin-assistant'); ?>"></textarea>
                        <button id="ai-send-message" class="button button-primary"><?php echo esc_html__('Send', 'ai-admin-assistant'); ?></button>
                    </div>
                </div>
                <div class="ai-chat-info">
                    <p><strong><?php echo esc_html__('Tip:', 'ai-admin-assistant'); ?></strong> <?php echo esc_html__('You can also use the Enter key to send. Use Shift+Enter for a new line.', 'ai-admin-assistant'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'ai-admin-assistant'));
        }

        // Verify nonce and process form submission
        if (isset($_POST['ai_admin_assistant_settings_nonce'])) {
            // Sanitize the nonce value before verification
            $nonce = sanitize_text_field(wp_unslash($_POST['ai_admin_assistant_settings_nonce']));
            
            if (wp_verify_nonce($nonce, 'ai_admin_assistant_settings')) {
                if (isset($_POST['ai_admin_assistant_api_key'])) {
                    $api_key = sanitize_text_field(wp_unslash($_POST['ai_admin_assistant_api_key']));
                    update_option('ai_admin_assistant_api_key', $api_key);
                    $this->api_key = $api_key;
                    echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved.', 'ai-admin-assistant') . '</p></div>';
                }
            }
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('AI Assistant Settings', 'ai-admin-assistant'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('ai_admin_assistant_settings', 'ai_admin_assistant_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Claude API Key', 'ai-admin-assistant'); ?></th>
                        <td>
                            <input type="text" 
                                   name="ai_admin_assistant_api_key" 
                                   value="<?php echo esc_attr($this->api_key); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php 
                                printf(
                                    /* translators: %s: Anthropic Console URL */
                                    esc_html__('You can find your API key in the %s.', 'ai-admin-assistant'),
                                    '<a href="https://console.anthropic.com/" target="_blank">' . esc_html__('Anthropic Console', 'ai-admin-assistant') . '</a>'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private function call_claude_api($message) {
        $headers = array(
            'anthropic-version' => '2023-06-01',
            'x-api-key' => $this->api_key,
            'content-type' => 'application/json'
        );

        $body = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $message
                )
            ),
            'max_tokens' => 4000,
            'temperature' => 0.7
        );

        $response = wp_remote_post($this->api_url, array(
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            throw new Exception(esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception(
                isset($data['error']['message']) 
                ? esc_html($data['error']['message']) 
                : esc_html__('Unknown API error', 'ai-admin-assistant')
            );
        }

        return isset($data['content'][0]['text']) 
            ? esc_html($data['content'][0]['text']) 
            : esc_html__('No response received from API.', 'ai-admin-assistant');
    }

    public function handle_ai_message() {
        check_ajax_referer('ai_admin_assistant_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'ai-admin-assistant')));
            return;
        }

        if (!isset($_POST['message'])) {
            wp_send_json_error(array('message' => esc_html__('No message provided.', 'ai-admin-assistant')));
            return;
        }

        $message = sanitize_textarea_field(wp_unslash($_POST['message']));
        
        if (empty($this->api_key)) {
            wp_send_json_error(array('message' => esc_html__('Please configure your Claude API key first.', 'ai-admin-assistant')));
            return;
        }

        try {
            $response = $this->call_claude_api($message);
            wp_send_json_success(array('message' => $response));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
                    /* translators: %s: Error message */
                    esc_html__('Error in Claude API request: %s', 'ai-admin-assistant'),
                    $e->getMessage()
                )
            ));
        }
    }
}

// Initialize plugin
new PROTECZONE_AI_Assistant();
