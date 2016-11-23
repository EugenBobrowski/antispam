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
    public static function db_check()
    {
        $version = get_option('antispam_db_version');

        if (intval($version) > 1) return;

        global $wpdb;
        $table = $wpdb->prefix . 'comments_antispam_log';
        $sql = "
CREATE TABLE {$table} (
	  `spam_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	  `spam_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `spam_IP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
	  `spam_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
	  `spam_author` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
	  `spam_manual` int(11) NOT NULL DEFAULT 0,
      UNIQUE KEY (spam_ID)
	);
	";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('si_db_version', 2);
    }
}