<?php

use App\Api\Raids;

require __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/../templates/header.php';

$TOTAL = 0;
$NUM   = 0;

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
            padding: 0 .5em 0 2em;
        }
    </style>

    <div class="row">
        <?php foreach (($ACCOUNTS ?? ACCOUNTS) as $name => $accessToken): ?>

            <?php
            $data  = Raids::progress($accessToken);
            $TOTAL += $data['total'];
            $NUM   += $data['num'];
            ?>

            <div class="col-sm-4">
                <div class="card">
                    <h5 class="card-header">
                        <small class="float-right"><?= $data['num'] ?> / <?= $data['total'] ?></small>
                        <?= $name ?>
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

        <div class="col-sm-4">
            <div class="card text-white bg-secondary">
                <h5 class="card-header">
                    <span class="float-right"><?= $NUM ?> / <?= $TOTAL ?></span>
                    Total : <?= number_format(100*$NUM/($TOTAL ?: 1), 0, '.', '') ?>%
                </h5>
            </div>

        </div>
    </div>

<?php

include __DIR__ . '/../templates/footer.php';