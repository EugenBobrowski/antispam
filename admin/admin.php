<?php

class Antispam_Admin
{
    protected static $instance;

    private function __construct()
    {
        add_action('admin_bar_menu', array($this, 'show_spam_count'), 99);
        add_action('admin_print_styles', array($this, 'style'));
        add_action('admin_menu', array($this, 'add_tools_submenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        return true;
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_tools_submenu()
    {
        add_submenu_page(
            'tools.php',
            'Antispam statistic',
            'Antispam',
            'manage_options',
            'antispam-statistic',
            array($this, 'view'));
    }

    public function view()
    {

        ?>
        <div class="wrap">

            <h1>
                <?php echo get_admin_page_title(); ?>
            </h1>
            <h2 style="color: #777"><span style="font-size: 3.3em"><?php echo get_option('spams_detected', 0); ?></span> spam comments rejected</h2>
            <div id="chartContainer" class="antispam-chart" style="height: 300px; width: 100%;"></div>

            <p style="text-align: right"><a class="button button-small" href="https://github.com/EugenBobrowski/antispam/issues">Create Issue on GitHub</a></p>
        </div>


        <?php
    }

    public function enqueue($prefix)
    {
        if (strpos('tools_page_antispam-statistic', $prefix) === false) return;
        wp_enqueue_script('canvasjs', plugin_dir_url(__FILE__) . 'canvasjs.min.js?plugin=' . $prefix);
        wp_enqueue_script('antispam-admin', plugin_dir_url(__FILE__) . 'antispam.js?plugin=' . $prefix, array('jquery', 'canvasjs'));
        wp_localize_script('antispam-admin', 'antispam_data', array('data' => $this->get_data()));
    }

    public function get_data () {

        global $wpdb;

        $table = $wpdb->prefix . 'comments_antispam_log';

        $sql = "SELECT COUNT(`spam_ID`) as s_count, DATE(`spam_date`) AS s_date FROM {$table} GROUP BY s_date ORDER BY s_date ASC ;";

        $res = $wpdb->get_results($sql, ARRAY_A);

        if (empty($res)) return array(array('s_count' => 0, 's_date' => date('Y-m-d')));

        return $res;
    }


    public function show_spam_count($wp_admin_bar)
    {
        $wp_admin_bar->add_node(array(
            "id" => "antispam-plugin",
            "title" => "Antispam ",
            'href' => admin_url('tools.php?page=antispam-statistic'),
            "parent" => "comments",
        ));

        $wp_admin_bar->add_node(array(
            "id" => "antispam-plugin-counter",
            "title" => get_option('spams_detected', 0) . ' rejected',
            "parent" => "antispam-plugin",
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'antispam-github',
            'href' => 'https://github.com/EugenBobrowski/antispam/issues',
            "title" => 'Create Issue on GitHub',
            "parent" => "antispam-plugin",
        ));


    }

    public function style()
    {
        ?>
        <style>
            #wp-admin-bar-antispam-plugin > .ab-item:before {
                content: "\f332" !important;
                top: 4px;
            }
        </style><?php
    }
}

if (apply_filters('antispam_counter', true))
    Antispam_Admin::get_instance();