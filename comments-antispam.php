<?php
/**
 * @package Antispam
 * @version 1.0
 */
/*
Plugin Name: Antispam
Plugin URI: http://wordpress.org/plugins/antispam/
Description: Antispam hack form comment form
Author: Eugen Bobrowski
Version: 1.2
Author URI: http://atf.li/
*/

if (!defined('ABSPATH')) exit;

if (is_admin()) {
    class Antispam_Admin
    {
        protected static $instance;

        private function __construct()
        {
            add_action('admin_bar_menu', array($this, 'show_spam_count'), 99);
            add_action('admin_print_styles', array($this, 'style'));
            return true;
        }

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function show_spam_count($wp_admin_bar)
        {
            $wp_admin_bar->add_node(array(
                "id" => "antispam-plugin-counter",
                "title" => "Antispam: " . get_option('spams_detected', 0),
            ));

            $wp_admin_bar->add_node(array(
                'id' => 'antispam-github',
                'href' => 'https://github.com/EugenBobrowski/antispam/issues',
                "title" => 'Create Issue on GitHub',
                "parent" => "antispam-plugin-counter",
            ));


        }

        public function style()
        {
            ?>
            <style>
                #wp-admin-bar-antispam-plugin-counter > .ab-item:before {
                    content: "\f332";
                    top: 4px;
                }
            </style><?php
        }
    }

    if (apply_filters('antispam_counter', true))
        Antispam_Admin::get_instance();
} else {
    class Antispam
    {

        protected static $instance;
        private $nonce;

        private function __construct()
        {

//		    add_filter('pre_comment_on_post', array($this, 'verify_spam'));

            $this->nonce = hash('md5', ABSPATH);

            add_filter('init', array($this, 'verify_spam'));
            add_filter('comment_form_fields', array($this, 'add_real_comment_field'));
            add_filter('print_footer_scripts', array($this, 'javascript'));
            return true;
        }

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function add_real_comment_field($comment_fields)
        {
            $real_field = str_replace('comment', $this->nonce, $comment_fields['comment']);
            $comment_fields['comment'] = $comment_fields['comment'] . $real_field;
            return $comment_fields;
        }

        public function javascript()
        {
            wp_enqueue_script('jquery');
            ?>
            <script>
                (function ($) {
                    var $comentField;
                    $(document).ready(function () {
                        $comentField = $('#comment');
                        $('#<?php echo $this->nonce; ?>').addClass($comentField.attr('class'));
                        $('#<?php echo $this->nonce; ?>').parent().addClass($comentField.parent().attr('class'));
                        $comentField.removeAttr('aria-required').removeAttr('required').parent().hide();
                    });
                })(jQuery);

            </script>


            <?php
        }

        public function verify_spam($commentdata)
        {

            if (isset($_POST['comment'])) {
                $spam_test_field = trim($_POST['comment']);
                if (!empty($spam_test_field)) {
                    $spam_detected = get_option('spams_detected', 1);
                    $spam_detected++;
                    update_option('spams_detected', $spam_detected);
                    return new WP_Error('comment_spam', __('Sorry, comments for bots are closed.'), 403);
                }
                $comment_content = trim($_POST[$this->nonce]);
                $_POST['comment'] = $comment_content;
            }
            return $commentdata;
        }


    }

    Antispam::get_instance();
}
