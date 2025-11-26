<?php
session_start();
// Hancurkan session dan redirect ke login
session_unset();
session_destroy();
header('Location: login.php');
exit();
