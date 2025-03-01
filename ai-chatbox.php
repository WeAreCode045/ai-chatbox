<?php
/**
 * Plugin Name: AI Chatbox for Document Content
 * Description: Adds an AI-powered chatbox to the bb-media-info-section for users to interact with document content.
 * Version: 1.0
 * Author: Your Name
 */

// Enqueue scripts and styles


    wp_enqueue_style(
        'ai-chatbox-css',
        plugin_dir_url(__FILE__) . 'css/chatbox.css'
    );

    wp_localize_script('ai-chatbox-js', 'aiChatboxVars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'openai_api_key' => 'your-openai-api-key' // Replace with your OpenAI API key or handle this securely
    ));

add_action('wp_enqueue_scripts', 'ai_chatbox_enqueue_assets');



function ai_chatbox_shortcode() {
    ob_start(); // Start output buffering
    ?>
    <div id="ai-chatbox" class="ai-chatbox">
        <div class="chatbox-container">
            <div id="chat-output"></div>
            <textarea id="chat-input" placeholder="Ask anything about this document..."></textarea>
            <button id="send-chat">Send</button>
        </div>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered output
}
add_shortcode('ai_chatbox', 'ai_chatbox_shortcode');

// Handle AJAX requests
function ai_chatbox_handle_ajax() {
    $message = sanitize_text_field($_POST['message']);
    $api_key = sanitize_text_field($_POST['api_key']);

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-4',
            'messages' => array(
                array('role' => 'system', 'content' => 'You are an AI assistant.'),
                array('role' => 'user', 'content' => $message),
            ),
        )),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        wp_send_json_success(array('response' => $body['choices'][0]['message']['content']));
    }
}

bp_nouveau_activity_hook( 'after', 'entry_comments' );
add_action('bb_media_info_section_render', function () {
    echo do_shortcode('[ai_chatbox]');
});
add_action('wp_ajax_get_ai_response', 'ai_chatbox_handle_ajax');
add_action('wp_ajax_nopriv_get_ai_response', 'ai_chatbox_handle_ajax');

function ai_chatbox_enqueue_assets() {
    if (is_singular() && has_shortcode(get_post()->post_content, 'ai_chatbox')) {
        wp_enqueue_script(
            'ai-chatbox-js',
            plugin_dir_url(__FILE__) . 'js/chatbox.js',
            array('jquery'),
            '1.0',
            true
        );

        wp_enqueue_style(
            'ai-chatbox-css',
            plugin_dir_url(__FILE__) . 'css/chatbox.css'
        );

        wp_localize_script('ai-chatbox-js', 'aiChatboxVars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'openai_api_key' => 'your-openai-api-key' // Replace with your OpenAI API key
        ));
    }
}
add_action('wp_enqueue_scripts', 'ai_chatbox_enqueue_assets');
