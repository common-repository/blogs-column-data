<?php
if (!defined( 'ABSPATH')) {
    exit;
}
class bcdSupport {
    private $api_url = 'https://app.bwdplugins.com/way-of-api/get-api.php?show_key=true';
    private $api_key;
    private $audience_id = 'https://app.bwdplugins.com/way-of-api/get-api.php?show_audience=true';
    private $list_id;

    public function __construct() {
        $this->bcd_fetch_api_key();
        add_action( 'admin_notices', [$this,'bcd_admin_updates_plugin_notice'] );
        add_action('admin_post_handle_bcd_email_subscription', [$this, 'handle_bcd_email_subscription']);
    }
    
    private function bcd_fetch_api_key() {
        $response = file_get_contents($this->api_url);
        $data = json_decode($response, true);
        if (isset($data['api_key'])) {
            $this->api_key = $data['api_key'];
        } else {
            echo "Error: API api key not found.";
        }
        $responseID = file_get_contents($this->audience_id);
        $dataID = json_decode($responseID, true);
        if (isset($dataID['audience_id'])) {
            $this->list_id = $dataID['audience_id'];
        } else {
            echo "Error: API audience id not found.";
        }
    }

    public function bcd_admin_updates_plugin_notice() {
        if (!get_option('bcd_email_subscription_notice_shown', false)) {
            $admin_email = get_option('admin_email');
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>Thank you for choosing our plugin! We appreciate your trust. <a href="https://bestwpdeveloper.com" target="_blank">Find us..</a></p>';
            echo '<form class="newsletter-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="email" name="email" value="' . esc_attr($admin_email) . '" style="display:none" required>';
            echo '<input type="hidden" name="action" value="handle_bcd_email_subscription">';
            echo '<button type="submit" class="button button-primary bcd-notice-btn">Hide Notice</button>';
            echo '</form>';
            echo '</div>';
        }
    }

    public function handle_bcd_email_subscription() {
        if (isset($_POST['email']) && is_email($_POST['email'])) {
            $email = sanitize_email($_POST['email']);
            $this->add_to_mailchimp($email);
            update_option('bcd_email_subscription_notice_shown', true);
            wp_safe_redirect(admin_url('#'));
            exit;
        } else {
            wp_safe_redirect(admin_url('#'));
            exit;
        }
    }

    private function add_to_mailchimp($email) {
        $data_center = substr($this->api_key, strpos($this->api_key, '-') + 1);
        
        $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $this->list_id . '/members/';
        
        $data = array(
            'email_address' => $email,
            'status'        => 'subscribed',
        );
        
        $json_data = json_encode($data);
        
        $args = array(
            'body'        => $json_data,
            'headers'     => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $this->api_key),
                'Content-Type'  => 'application/json',
            ),
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('Mailchimp error: ' . $response->get_error_message());
        }
    }

}
new bcdSupport();
