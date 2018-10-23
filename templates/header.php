<?php
$CURRENT_URL = isset(MENU[$_SERVER['PHP_SELF']]) ? $_SERVER['PHP_SELF'] : '';
?><!DOCTYPE html>
<html>
<head>
    <title>Gw2 <?= MENU[$CURRENT_URL] ?? '' ?></title>
    <link rel="stylesheet" href="/assets/bootstrap-4.1.3-dist/css/bootstrap.min.css">
    <script src="/assets/bootstrap-4.1.3-dist/js/bootstrap.min.js"></script>
    <script src="/assets/jquery/jquery-3.3.1.slim.min.js"></script>
    <link rel="icon" type="image/png" href="/assets/favicon.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">

    <ul class="nav nav-tabs" style="margin-top: 1em; margin-bottom: 1em">
        <?php foreach (MENU as $url => $name): ?>
            <li class="nav-item">
                <a class="nav-link <?= $url === $CURRENT_URL ? 'active' : '' ?>" href="<?= $url ?>"><?= $name ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

