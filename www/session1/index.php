<?php
session_start();

$_SESSION["test"] = "yes";

var_dump($_SESSION);