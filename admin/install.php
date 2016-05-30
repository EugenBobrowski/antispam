<?php

class Antispam_Activator
{
    /**
     * Short Description. (use period)
     *
     * Checking and updating the database.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        $version = get_option('antispam_db_version');

        if (intval($version) > 0) return;

        global $wpdb;
        $table_count = $wpdb->prefix . 'comments_antispam';
        $sql = "
CREATE TABLE {$table_count} (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  `date` date NOT NULL DEFAULT '0000-00-00',
	  `count` bigint(11) NOT NULL DEFAULT 1,
      UNIQUE KEY (id)
	);
	";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('si_db_version', 1);
    }
}