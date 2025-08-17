<?php
session_start();

// Ondoa session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../login/index.html?message=logout_success");
exit();
?>