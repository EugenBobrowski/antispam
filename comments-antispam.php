<?php
/**
 * @package Antispam
 * @version 1.0
 */
/*
Plugin Name: Antispam
Plugin URI: http://wordpress.org/plugins/antispam/
Description: Anti-spam check the robots by behavior. No captcha. Antispam let robots do so as a human can't do.
Author: Eugen Bobrowski
Version: 1.5
Author URI: http://bobrowski.ru/
*/

if (!defined('ABSPATH')) exit;

if (is_admin()) {
    include_once 'admin/admin.php';
    include_once 'admin/install.php';
    add_action('plugins_loaded', array('Antispam_Activator', 'db_check'));
    register_activation_hook( __FILE__, array('Antispam_Activator', 'db_check') );
} else {
    class Antispam
    {

        protected static $instance;
        private $secret;
        private $nonce;
        private $localize_object;
        private $fields;

        private function __construct()
        {
            $this->secret = apply_filters('antispam_s', ABSPATH);

            $this->nonce = hash('md5', $this->secret);

            $this->localize_object = 'veritas';

            $this->fields = apply_filters('antispam_fields', array(
                //field name
                'comment' => array(
                    //protect method (replace | add )
                    'method' => 'add',
                    'request_method' => 'post',
                    //parent to copy and hide
                    'parent' => '.comment-form-comment',
                    'author' => 'author',
                    'email' => 'email',
                ),
//                's' => array(
//                    //protect method (replace | add )
//                    'method' => 'add',
//                    'request_method' => 'get',
//                    //parent to copy and hide
//                    'parent' => 'label',
//                ),

            ));

            foreach ($this->fields as $name => $settings) {
                $this->fields[$name]['ha'] = hash('md5', ABSPATH . $name);
            }
//		    add_filter('pre_comment_on_post', array($this, 'verify_spam'));
            add_filter('init', array($this, 'verify_spam'), 1);
            add_action('wp_print_scripts', array($this, 'localize'));
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

        public function localize()
        {
            wp_enqueue_script('jquery');
            wp_localize_script('jquery', $this->localize_object, $this->fields);
        }

        public function javascript()
        {
            wp_enqueue_script('jquery');
            ob_start();
            ?>
            <script>
                (function ($) {
                    $(document).ready(function () {

                        var someFunction = function (e) {
                            for (var key in veritas) {
                                var $field = $('[name="' + key + '"]');
                                if ($field.length < 1) continue;

                                $field.each(function () {

                                    var $this = $(this);

                                    if (veritas[key].method == 'replace') {

                                        $this.focus(function(){
                                            $("label[for='" + $this.attr('id') + "']").attr('for', veritas[key].ha);
                                            $this.attr('id', veritas[key].ha).attr('name', veritas[key].ha);
                                        });

                                    } else if (veritas[key].method =    = 'add') {

                                        var $parent = $this.parents(veritas[key].parent);
                                        var $clone = $parent.clone();


                                        $clone.find("label[for='" + $this.attr('id') + "']").attr('for', veritas[key].ha);
                                        $clone.find('[name="' + key + '"]').attr('id', veritas[key].ha).attr('name', veritas[key].ha);
                                        $parent.after($clone).hide().find('[name="' + key + '"]').removeAttr('required');

                                    }

                                })

                            }
                        };

                        setTimeout(someFunction, 1000);

                    });
                })(jQuery);

            </script>
            <?php
            $js = ob_get_clean();

            $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
            $js = str_replace(': ', ':', $js);
            $js = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $js);
            $x = array(
                'someFunction' => ' a',
//                ' veritas' => ' v',
                'key' => 'k',
                '$parent' => 'p',
                '$clone' => 'c',
            );
            $js = str_replace(array_keys($x), $x, $js);
            echo($js);
            return true;
        }


        public function verify_spam($commentdata)
        {

            foreach ($this->fields as $name => $field) {
                if (
                (('replace' == $field['method'] && isset($_REQUEST[$name]) && !isset($_REQUEST[$field['ha']]))
                ||
                ('add' == $field['method'] && !empty($_REQUEST[$name])))
                && !isset($_COOKIE['antispam_verified'])
                ) {
                    $this->die_die_die($field);
                } elseif (isset($_REQUEST[$field['ha']]) && 'post' == $field['request_method']) {
                    $_POST[$name] = $_POST[$field['ha']];
                } elseif (isset($_REQUEST[$field['ha']]) && 'get' == $field['request_method']) {
                    setcookie('antispam_verified', 'asd', time() + 60, '/');
                    $_GET[$name] = $_GET[$field['ha']];
                    wp_redirect(site_url('?s='.$_GET[$field['ha']])); exit;
                }
            }


            return $commentdata;
        }

        public function log_spam($field = array()) {

            $spamdata = array(
                'spam_date' => current_time( 'mysql' ),
                'spam_IP' => '',
                'spam_email' => '',
                'spam_author' => '',
            );


            $spamdata['spam_IP'] = $_SERVER['REMOTE_ADDR'];
            $spamdata['spam_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $spamdata['spam_IP'] );

            if (isset($field['author']) && isset($_POST[$field['author']])) $spamdata['spam_author'] = sanitize_text_field($_POST[$field['author']]);
            elseif (isset($_POST['author'])) $spamdata['spam_author'] = sanitize_text_field($_POST['author']);

            if (isset($field['email']) && isset($_POST[$field['email']])) $spamdata['spam_email'] = sanitize_text_field($_POST[$field['email']]);
            elseif (isset($_POST['email'])) $spamdata['spam_email'] = sanitize_text_field($_POST['email']);

            global $wpdb;
            $table = $wpdb->prefix . 'comments_antispam_log';

            $wpdb->insert($table, $spamdata);

        }

        public function die_die_die($field)
        {
            $this->log_spam($field);
            $spam_detected = get_option('spams_detected', 0);
            $spam_detected++;
            update_option('spams_detected', $spam_detected);
            wp_die(__('Sorry, comments for bots are closed.'));
        }

    }

    add_action('plugins_loaded', array('Antispam', 'get_instance'));
}

// Example

//add_filter('antispam_fields', 'antispam_disable_search');
//function antispam_disable_search ($fields) {
// if (isset($fields['s'])) unset($fields['s']);
// return $fields;
//}