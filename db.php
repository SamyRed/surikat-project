<?php
$db = mysqli_connect ("***", "***", "***", "***");
if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    die();
}
?>
