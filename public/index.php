<?php
/**
 * Venus Hotel website – front controller.
 * Every page request (public site and /admin) goes through this file.
 */
require __DIR__ . '/app/bootstrap.php';
App::run();
