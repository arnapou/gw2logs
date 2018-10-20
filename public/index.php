<?php

use App\Api\Profession;
use App\Log;
use App\LogMetadata;

require __DIR__ . '/../vendor/autoload.php';

define('EMPTY_TEXT', '<em class="text-muted">-</em>');
define('PROCESSING_TEXT', '<em class="text-muted">processing</em>');

function lnk($url, $icon, $title = '')
{
    return $url
        ? '<a href="' . $url . '"><img src="/assets/icon_' . $icon . '.png"/>' . ($title ?: $icon) . '</a>'
        : EMPTY_TEXT;
}

function prof($player)
{
    return $player['profession_icon']
        ? '<img src="' . $player['profession_icon'] . '"/>'
        : '';
}

function player(LogMetadata $metadata)
{
    foreach ($metadata->getPlayers() as $player) {
        if (\in_array($player['display_name'], array_keys(ACCOUNTS))) {
            return $player + Profession::fromPlayer($player);
        }
    }
    return [];
}

function wday(Log $log)
{
    $wday = date('w', strtotime($log->datetime()));
    return ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'][$wday] ?? '';
}

$LOGS = Log::all(
    $_REQUEST['filtres'] ?? [],
    $_REQUEST['offset'] ?? 0,
    $_REQUEST['length'] ?? LOGS_DEFAULT_PAGE_LENGTH
);

include __DIR__ . '/../templates/header.php';
?>
    <style>
        tr img {
            max-height: 1.4em;
            margin-right: .2em;
        }

        tr.fail {
            color: red;
        }

        tr td.xs {
            width: 1px;
            white-space: nowrap;
        }
    </style>

    <form action="?" method="get" class="filtres">

        <div class="row">
            <label for="filtre1" class="col-sm-1 col-form-label">Filtres</label>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="col-sm-2">
                    <input type="text" name="filtres[]" class="form-control" value="<?= $LOGS->filtre($i) ?? '' ?>">
                </div>
            <?php endfor; ?>
            <div class="col-sm-3">
                <button type="submit" class="btn btn-primary">OK</button>
                <a href="/" class="btn btn-danger">Cancel</a>
            </div>
        </div>

        <table class="table table-striped table-hover table-sm" style="margin-top: 1em">
            <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Date</th>
                <th>Boss</th>
                <th>dps.report</th>
                <th>gw2raidar</th>
                <th>Compte</th>
                <th>Perso</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($LOGS as $log) : ?>
                <?php
                $metadata = $log->metadata();
                $player   = player($log->metadata());
                ?>
                <tr class="<?= $metadata->getStatus() ?>">
                    <td class="xs"><a href="/dl.php?log=<?= $log->filename() ?>"><img src="/assets/zip.png"/></a></td>
                    <td class="xs"><?= wday($log) ?></td>
                    <td><?= $log->datetime() ?></td>
                    <td><?= $metadata->getBoss() ?: ($metadata->hasTag(LogMetadata::TAG_PROCESSING) ? PROCESSING_TEXT : EMPTY_TEXT) ?></td>
                    <td><?= lnk($metadata->getUrlDpsReport(), 'dpsreport') ?></td>
                    <td><?= lnk($metadata->getUrlRaidar(), 'gw2raidar') ?></td>
                    <td><?= $player['display_name'] ?? EMPTY_TEXT ?></td>
                    <td><?= prof($player) . $player['character_name'] ?? EMPTY_TEXT ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($LOGS->pageCount() > 1): ?>
            <nav>
                <small class="float-right text-primary"><?= $LOGS->count() ?> logs &mdash; <?= sprintf('%0.0f MB', $LOGS->size()) ?></small>
                <input type="hidden" name="offset" class="form-control" value="<?= $LOGS->offset() ?>">
                <input type="hidden" name="length" class="form-control" value="<?= $LOGS->length() ?>">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $LOGS->pageCount(); $p++): ?>
                        <?php if ($p > 20): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <li class="page-item disabled"><span class="page-link"><?= $LOGS->pageCount() ?></span></li>
                            <?php break; ?>
                        <?php else: ?>
                            <li class="page-item <?= $LOGS->pageNum() == $p ? 'active' : '' ?>">
                                <button type="submit" class="page-link" data-offset="<?= $LOGS->length() * ($p - 1) ?>"><?= $p ?></button>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </form>

    <script>
        $(function () {
            $('.pagination button').on('click', function () {
                var offset = $(this).data('offset');
                $('input[name=offset]').val(offset || 0);
            });
        });
    </script>

<?php

include __DIR__ . '/../templates/footer.php';