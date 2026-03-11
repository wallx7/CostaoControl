<?php include 'services/session.php'; ?>
<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']) {
    header('Location: auth-signin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
