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
Version: 1.3
Author URI: http://atf.li/
*/

if (!defined('ABSPATH')) exit;

if (is_admin()) {
    include_once 'admin/admin.php';
    include_once 'admin/install.php';
    register_activation_hook( __FILE__, array('Antispam_Activator', 'activate') );
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
                    //protect method (replace | add )
                    'method' => 'add',
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

        }


        public function verify_spam($commentdata)
        {
            foreach ($this->fields as $name => $field) {
                if (
                ('replace' == $field['method'] && isset($_POST['comment']) && !isset($_POST[$field['ha']]))
                ||
                ('add' == $field['method'] && !empty($_POST['comment']))
                ) {
                    $this->die_die_die();

                } elseif (isset($_POST[$field['ha']])) {
                    $_POST['comment'] = $_POST[$field['ha']];
                }
            }


            return $commentdata;
        }

        public function die_die_die()
        {
            $spam_detected = get_option('spams_detected', 0);
            $spam_detected++;
            update_option('spams_detected', $spam_detected);
            wp_die(__('Sorry, comments for bots are closed.'));
        }


    }

    Antispam::get_instance();
}
