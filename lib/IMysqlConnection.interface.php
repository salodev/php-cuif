<?php

interface IMysqlConnection {
	public function __construct($host, $user, $password, $dbname);
	public function connect();
	public function selectDB($dbName);
	public function query($query, $callback);
	public function killQuery();
}