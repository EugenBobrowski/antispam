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
Version: 1.0
Author URI: http://atf.li/
*/


class Antispam
{

    protected static $instance;
    private $nonce = 'mfmfllfsdjgh';

    private function __construct()
    {
        if (is_admin()) return false;
//		add_filter('pre_comment_on_post', array($this, 'verify_spam'));
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
                return new WP_Error('comment_spam', __('Sorry, comments for bots are closed.'), 403);
            }
            $comment_content = trim($_POST[$this->nonce]);
            $_POST['comment'] = $comment_content;
        }
        return $commentdata;
    }

}
Antispam::get_instance();
