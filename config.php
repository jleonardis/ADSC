<?php

/**
 * Configuration for database connection
 *
 */

 $host = "localhost";
 $username = "homestead";
 $password = "secret";
 $dbname = "amigos_test";
 $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
 $options = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
 );
