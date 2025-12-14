<?php
if(!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: /index.php');
    exit();
}
?>