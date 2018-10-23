<?php

use App\Api\Raids;

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../templates/header.php';

$TOTAL = 0;
$NUM   = 0;
foreach (($ACCOUNTS ?? ACCOUNTS) as $name => $accessToken) {
    $data  = Raids::progress($accessToken);
    $TOTAL += $data['total'];
    $NUM   += $data['num'];
}
$PCT = 100 * $NUM / ($TOTAL ?: 1);

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

        .card-header .float-right {
            padding-top: .2em;
        }
    </style>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card">
                <h5 class="card-header text-muted">
                    <span class="float-right text-primary">
                        <?= $NUM ?> <small>/ <?= $TOTAL ?></small>
                    </span>
                    Total : <?= round($PCT) ?>%
                </h5>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach (($ACCOUNTS ?? ACCOUNTS) as $name => $accessToken): ?>
            <?php $data = Raids::progress($accessToken); ?>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <h5 class="card-header">
                        <small class="float-right">
                            <?= $data['num'] ?> <small>/ <?= $data['total'] ?></small>
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

include __DIR__ . '/../templates/footer.php';