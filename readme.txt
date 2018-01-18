=== Antispam ===
Contributors: eugenbobrowski
Tags: comments, spam, antispam, anti-spam, anti spam, comment spam, spam comments
Requires at least: 3.2
Tested up to: 4.7
Stable tag: 1.5
License: GPLv2 or later

Anti-spam check the robots by behavior. No captcha. Antispam let robots do so as a human can't do.

== Description ==

Antispam let robots do so as a human can't do.

= Features include: =

* No captcha
* Statistic

= Incompatible with: =

* Disqus
* Jetpack Comments
* AJAX Comment Form
* bbPress


GitHub: https://github.com/EugenBobrowski/antispam

== Installation ==

Upload the Antispam plugin to your blog, Activate it, then use it.

1, 2, 3: You're done!

== Frequently Asked Questions ==

= Can it protect other of comment fields too? =

Yes! You can.
```
add_filter('antispam_fields', 'antispam_add_fields');
function antispam_add_fields ($fields) {
    $fields['email_field'] = array(
        //protect method (replace | add )
        'method' => 'add',
        'request_method' => 'post',
        //parent to copy and hide
        'parent' => '.subscribtion-form-email',
        'author' => 'author',
        'email' => 'email',
    );
 return $fields;
}
```

== Screenshots ==

1. Spam comment is rejected
2. Antispam statistic page

== Changelog ==

= 1.5 =
*Release Date - 29th December, 2016*

* Disable search antispam
* Fix antispam fields filter applying

= 1.4 =
*Release Date - 24th November, 2016*

* Add statistic

= 1.3 =
*Release Date - 30rd May, 2016*

* Change old to two new algorithms of protection.
* Add feature of custom fields protect.
* Move spam counter to comments section in admin bar

= 1.2 =
*Release Date - 25rd May, 2016*

* Add counter of spam detected comments.

= 1.1 =
*Release Date - 11rd May, 2016*

* Use md5 hash of ABSPATH as a name of real comment field.

= 1.0 =
*Release Date - 23rd April, 2016*

* Initial

== Upgrade Notice ==

= 1.3 =
*Old algorithm didn't work good. Please update!
