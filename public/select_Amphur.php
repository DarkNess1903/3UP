<?php
session_start();
include '../connectDB.php';

if (isset($_POST['id'])) {
    $id_province = mysqli_real_escape_string($conn, $_POST['id']);
    $query = "SELECT amphurID, amphurName FROM amphur WHERE provinceID = '$id_province'";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value=\"{$row['amphurID']}\">{$row['amphurName']}</option>";
    }
}

mysqli_close($conn);
?>
