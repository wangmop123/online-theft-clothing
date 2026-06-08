<?php

$password = 'Admin@2026';

$hash = password_hash($password, PASSWORD_DEFAULT);

echo $hash;

?>