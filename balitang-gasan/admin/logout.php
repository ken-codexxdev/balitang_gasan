<?php
require_once __DIR__ . '/../config/db.php';
session_destroy();
echo '<script>
alert("You have been logged out successfully. Goodbye!");
window.location.href = "' . addslashes(SITE_URL . '/index.php') . '";
</script>';
exit;

?>
