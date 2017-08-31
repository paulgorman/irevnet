Presence's iRev.net Portfolio Gallery
=======

This is the new iRev.net website featuring Presence's gallery of stuff

Install
-------
* git clone git://github.com/paulgorman/irev.net.git
* mkdir -p irev.net/i/artist
* mkdir irev.net/i/category
* mkdir irev.net/i/pages
* mkdir irev.net/m
* chown -R www:www irev.net/m irev.net/i
* mysqladmin create irev
* GRANT ALL PRIVILEGES ON irev.* TO username@'localhost' IDENTIFIED BY 'password';
* FLUSH PRIVILEGES
* mysql irevnet < irev.net/schema.sql
* edit irev.net/db.php: $user and $pass
* edit system httpd.conf and add
```
	<LocationMatch "/(i|m)/.*\.(php|cgi)$">
		Order Deny,Allow
		Deny from All
	</LocationMatch>
```

* check irev.net/php.ini

Notes
-----
alias ci='git add -A;git commit;git push origin master;'

Depends upon ffmpeg, mod_h264_streaming, php7, mariaDB, ImageMagick 

PHP modules: fileinfo, gd, mysqli, session, exif, hash, iconv, phar
