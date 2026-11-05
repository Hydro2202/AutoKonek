<?php

/*
| Web routes, grouped by who can use them. Each file below is loaded inside
| the "web" middleware group, and route names never change between files.
*/

require __DIR__.'/web/site.php';      // public pages, directory, legal pages
require __DIR__.'/web/auth.php';      // login, register, logout
require __DIR__.'/web/account.php';   // signed-in users: profile, customer reviews and reports
require __DIR__.'/web/owner.php';     // business owners
require __DIR__.'/web/admin.php';      // administrators
