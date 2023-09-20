<?php

header("Content-type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Shanghai");

const DS = DIRECTORY_SEPARATOR; //分隔符
define('SITE_ROOT', dirname(__FILE__) . DS);

//composer include
include_once dirname(__DIR__) . "/vendor/autoload.php";

require '../app/bootstrap_web.php';
