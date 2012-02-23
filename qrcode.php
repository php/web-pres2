<?php
header( 'Content-Type: image/png' );
include 'phpqrcode.php';

QRcode::png($_GET['d'], false, "M", 10, 2);
?>
