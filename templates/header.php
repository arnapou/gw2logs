<!DOCTYPE html>
<html>
<head>
    <title>Gw2 <?= $TITLE ?? '' ?></title>
    <link rel="stylesheet" href="/assets/bootstrap-4.1.3-dist/css/bootstrap.min.css">
    <script src="/assets/bootstrap-4.1.3-dist/js/bootstrap.min.js"></script>
    <script src="/assets/jquery/jquery-3.3.1.slim.min.js"></script>
    <link rel="icon" type="image/png" href="/assets/favicon.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            padding-bottom: 10em;
        }
    </style>
</head>
<body>
<div class="container">
<?php

include __DIR__ . '/menu.php';
