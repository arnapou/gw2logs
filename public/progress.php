<?php

use App\Api\Raids;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../templates/header.php';

$ACCOUNT     = ACCOUNTS[$_GET['tab'] ?? ''] ?? [];
$KEYS        = $ACCOUNT['keys'] ?? [];
$HAS_SUMMARY = $ACCOUNT['summary'] ?? true;

?>
    <style>
        .boss {
            font-size: .8em;
            width: 3em;
            padding: .1em 0;
            text-align: center;
            border: 1px solid #b7e1cd;
            background: #e5ffed;
            color: #aec6b5;
        }

        .boss.type-E {
            background: #eee;
            color: #bbb;
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

        .card-body table td:first-child {
            padding: 0 .5em 0 3em;
        }

        .card-header .account {
            font-size: 1rem;
        }

        .card-header small.float-right {
            padding-top: .2em;
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
    $PCT = 100 * $NUM / ($TOTAL ?: 1);

    ?>
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

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <h5 class="card-header">
                        <small class="float-right">
                            <?= $data['num'] ?>
                            <small>/ <?= $data['total'] ?></small>
                        </small>
                        <span class="account"><?= $name ?></span>
                    </h5>
                    <div class="card-body">
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
                </div>

            </div>

        <?php endforeach; ?>

    </div>

<?php

require __DIR__ . '/../templates/footer.php';