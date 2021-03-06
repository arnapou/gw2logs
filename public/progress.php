<?php

use App\Api\Achievements;
use App\Api\Raids;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../templates/header.php';

$ACCOUNT     = ACCOUNTS[$_GET['tab'] ?? ''] ?? [];
$KEYS        = $ACCOUNT['keys'] ?? [];
$HAS_SUMMARY = $ACCOUNT['summary'] ?? true;

?>
    <style>
        .achiev {
            font-size: .8em;
            padding: 0 !important;
            text-align: center;
            color: #fff;
        }

        .boss {
            font-size: .8em;
            padding: .1em 0 !important;
            text-align: center;
            border: 1px solid #b7e1cd;
            background: #e5ffed;
            color: #aec6b5;
            width: 3em;
        }

        .achiev {
            white-space: nowrap;
            overflow: hidden;
        }

        .boss.type-E {
            background: #eee;
            color: #bbb;
        }

        .achiev.unlocked {
            background: rgba(46, 117, 153, 0);
            color: #fff;
        }

        .boss.done {
            background: #2d9b3a;
            color: #fff;
        }

        .card {
            margin-bottom: 1.5em;
        }

        .card-body table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        .card-body.raid table td:first-child {
            padding: 0 .5em 0 3em;
        }

        .card-body.achievements table {
            table-layout: fixed;
            width: 100%;
            background: #b33737;
        }

        .card-body.achievements table td {
            border-right: 1px solid #fff;
            border-bottom: 1px solid #fff;
        }

        .card-body.achievements table td:first-child {
            width: 1.8em;
            padding: 0;
            background: #fff;
        }

        .card-header .account {
            font-size: 1rem;
        }

        .card-header small.float-right {
            padding-top: .2em;
        }

        .card-body {
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .card-body:last-child {
            border-bottom: none;
        }
    </style>

<?php
if ($HAS_SUMMARY) {
    $TOTAL = 0;
    $NUM   = 0;
    foreach ($KEYS as $name => $accessToken) {
        $data  = Raids::progress($accessToken);
        $TOTAL += $data['total'];
        $NUM   += $data['num'];
    }
    $PCT = 100 * $NUM / ($TOTAL ?: 1); ?>
    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card">
                <h5 class="card-header text-muted">
                    <span class="float-right text-primary">
                        <?= $NUM ?>
                        <small>/ <?= $TOTAL ?></small>
                    </span>
                    Total : <?= round($PCT) ?>%
                </h5>
            </div>
        </div>
    </div>
    <?php
}
?>

    <div class="row">
        <?php foreach ($KEYS as $name => $accessToken): ?>
            <?php $data = Raids::progress($accessToken); ?>
            <?php $categs = Achievements::getAchievements($accessToken); ?>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <h5 class="card-header">
                        <small class="float-right">
                            <?= $data['num'] ?>
                            <small>/ <?= $data['total'] ?></small>
                        </small>
                        <span class="account"><?= $name ?></span>
                    </h5>
                    <div class="card-body raid">
                        <?php foreach ($data['raids'] as $raid): ?>
                            <div><?= $raid['title'] ?></div>
                            <table>
                                <?php foreach ($raid['table'] as $row): ?>
                                    <tr>
                                        <td><?= $row['title'] ?></td>
                                        <?php foreach ($row['cases'] as $case): ?>
                                            <td class="boss type-<?= $case[0] ?> <?= $case[1] ? 'done' : '' ?>">
                                                <?= $case[0] ?: '&nbsp;' ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-body achievements">
                        <?php foreach ($categs as $index => $categ): ?>
                            <table>
                                <tr>
                                    <td title="<?= t($categ['name']) ?>">W<?= $index + 1 ?></td>
                                    <?php foreach ($categ['achievements'] as $achiev): ?>
                                        <td class="achiev" style="<?= bgTD($achiev['unlocked'] ?? 0) ?>"
                                            title="<?=
                                            t($achiev['name'] ?? '') . "\n" .
                                            (($achiev['description'] ?? false) ? '&#x2b9e; ' . t($achiev['description']) . "\n" : '') .
                                            (($achiev['requirement'] ?? false) ? '&#x2b9e; ' . t($achiev['requirement']) . "\n" : '') .
                                            (($achiev['titleName'] ?? false) ? '&#x2b9e; ' . t('Titre : "' . $achiev['titleName'] . '"') . "\n" : '') .
                                            rwd($achiev['rewardTypes'])
                                            ?>"
                                        >
                                            <?= rwd($achiev['rewardTypes']) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </table>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

<?php

require __DIR__ . '/../templates/footer.php';

function bgTD($pct)
{
    if ($pct > 0 && $pct <= 0.5) {
        $pct = 0.33;
    } elseif ($pct > 0.5 && $pct < 1) {
        $pct = 0.66;
    }
    $pct = (float)$pct;
    return "background: rgba(46, 117, 153, $pct);";
}

function rwd($rewards)
{
    $str = '';
    if (\in_array('title', $rewards)) {
        $str .= '&#x1f396; ';
    }
    if (\in_array('mastery', $rewards)) {
        $str .= '&#x2735; ';
    }
    if (\in_array('item', $rewards)) {
        $str .= '&#x1f4b0; ';
    }
    return str_replace(' ', '&nbsp; &nbsp; ', trim($str));
}

function t($str)
{
    return htmlspecialchars($str);
}
