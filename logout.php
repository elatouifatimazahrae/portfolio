<?php
session_start();

session_unset();
session_destroy();

header('Location: /ecomerce/index.php');
exit;
