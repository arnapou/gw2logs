<?php

function isactive($cond)
{
    return $cond ? 'active' : '';
}

?>

<ul class="nav nav-tabs" style="margin-top: 1em; margin-bottom: 1em">
    <li class="nav-item">
        <a class="nav-link <?= isactive($_SERVER['PHP_SELF'] === '/index.php') ?>" href="/">Logs</a>
    </li>
    <?php foreach (ACCOUNTS as $name => $config): ?>
        <li class="nav-item">
            <a class="nav-link <?= isactive(($_GET['tab'] ?? '') === $name) ?>" href="/progress.php?tab=<?= rawurlencode($name) ?>"><?= $name ?></a>
        </li>
    <?php endforeach; ?>
</ul>

