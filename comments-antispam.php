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
        private $localize_object;
        private $fields;

        private function __construct()
        {

//		    add_filter('pre_comment_on_post', array($this, 'verify_spam'));

            $this->nonce = hash('md5', ABSPATH);

            $this->localize_object = 'veritas';

            $this->fields = apply_filters('antispam_fields', array(
                //field name
                'comment' => array(
                    //protect method (replace | appending )
                    'method' => 'replace',
                    //parent to copy and hide
                    'parent' => '.comment-form-comment',
                ),
            ));

            foreach ($this->fields as $name => $settings) {
                $this->fields[$name]['ha'] = hash('md5', ABSPATH . $name);
            }

            add_filter('init', array($this, 'verify_spam'));
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
                    var $comentField;
                    $(document).ready(function () {

                        var someFunction = function (e) {
                            console.log(veritas);
                            for (var key in veritas) {
                                var $field = $('[name="' + key + '"]');
                                if ($field.length < 1 ) continue;

                                $field.each(function () {
                                    var $this = $(this);

                                    var $parent = $this.parents(veritas[key].parent);

                                    if (veritas[key].method = 'replace') {
                                        $this.attr('id', veritas[key].ha).attr('name', veritas[key].ha);
                                    } else if (veritas[key].method = 'append') {

                                        $("label[for='" + $this.attr('id') + "']").attr('for', veritas[key].ha);
                                        $this.attr('id', veritas[key].ha).attr('name', veritas[key].ha);
                                    }



                                })




                            }


                            $comentField = $('#comment');

                            $('#<?php echo $this->nonce; ?>').addClass($comentField.attr('class'));
                            $('#<?php echo $this->nonce; ?>').parent().addClass($comentField.parent().attr('class'));
                            /*$comentField.removeAttr('aria-required').removeAttr('required').parent().hide();*/
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
            $obfuscate = array(
                'someFunction' => 'a',
            );
            $js = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $js);
            echo($js);

        }



        public function verify_spam($commentdata)
        {
            foreach ($this->fields as $name => $field) {
                if ('replace' == $field['method']) {
                    if (!isset($_POST['comment']) && isset($_POST[$field['ha']])) {
                        $_POST['comment'] = $_POST[$field['ha']];
                    } else {
                        $spam_detected = get_option('spams_detected', 0);
                        $spam_detected++;
                        update_option('spams_detected', $spam_detected);
                        wp_die(__('Sorry, comments for bots are closed.'));
                    }
                } elseif ('append' == $field['method'] && isset($_POST['comment'])) {
                    $spam_test_field = trim($_POST['comment']);
                    if (!empty($spam_test_field)) {
                        $spam_detected = get_option('spams_detected', 0);
                        $spam_detected++;
                        update_option('spams_detected', $spam_detected);
                        wp_die(__('Sorry, comments for bots are closed.'));
                    }
                    $comment_content = trim($_POST[$this->nonce]);
                    $_POST['comment'] = $comment_content;
                }
            }


            return $commentdata;
        }


    }

    Antispam::get_instance();
}
