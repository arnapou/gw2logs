<?php

use App\Api\Profession;
use App\Log;
use App\Logger\ProcessLogger;
use App\LogMetadata;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../templates/header.php';

if (isset($_REQUEST['history'])) {
    $classes = [
        'NOTICE' => 'table-warning',
        'ERROR'  => 'table-danger',
    ];
    ?>
    <table class="table table-sm table-hover">
        <?php foreach (loadLines(200) as $cols): ?>
            <tr class="<?= $classes[$cols[1]] ?? '' ?>">
                <td><?= $cols[0] ?></td>
                <td><?= $cols[1] ?></td>
                <td><?= $cols[2] ?></td>
                <td><?= $cols[3] ?></td>
                <td><?= $cols[4] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php

} else {

    define('EMPTY_TEXT', '<em class="text-muted">-</em>');
    define('PROCESSING_TEXT', '<em class="text-muted">processing</em>');

    $LOGS = Log::all(
        $_REQUEST['filtres'] ?? [],
        $_REQUEST['offset'] ?? 0,
        $_REQUEST['length'] ?? LOGS_DEFAULT_PAGE_LENGTH
    );

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
            <label for="filtre1" class="col-lg-1 col-form-label">Filtres</label>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="col-lg-2">
                    <input type="text" name="filtres[]" class="form-control" value="<?= $LOGS->filtre($i) ?? '' ?>">
                </div>
            <?php endfor; ?>
            <div class="col-lg-3">
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
                <small class="float-right text-primary text-right">
                    <a href="?history=1"><?= $LOGS->count() ?> logs</a> &mdash;
                    <?= sprintf('%0.0f MB', $LOGS->size()) ?> busy &mdash;
                    <?= disk() ?> free &mdash;
                    <a href="/upload.php">upload</a>
                </small>
                <input type="hidden" name="offset" class="form-control" value="<?= $LOGS->offset() ?>">
                <input type="hidden" name="length" class="form-control" value="<?= $LOGS->length() ?>">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $LOGS->pageCount(); $p++): ?>
                        <?php if ($p > 10): ?>
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
}

require __DIR__ . '/../templates/footer.php';


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

function loadLines($nb)
{
    if (!is_file(ProcessLogger::getFilename())) {
        return [];
    }
    $parseLine = function ($line) {
        if ($line) {
            $line    = trim(trim($line), '[');
            $line    = str_replace('] ', '    ', $line);
            $line    = preg_replace('!    +!', '    ', $line);
            $columns = strpos($line, "\t") !== false
                ? explode("\t", $line)
                : explode('    ', $line);
            if (count($columns) >= 5) {
                return array_map('trim', $columns);
            }
        }
        return null;
    };
    $lines     = [];
    $fp        = fopen(ProcessLogger::getFilename(), "r");
    while (!feof($fp)) {
        $line    = fgets($fp, 40960);
        $columns = $parseLine($line);
        if ($columns) {
            array_push($lines, $columns);
            if (count($lines) > $nb) {
                array_shift($lines);
            }
        }
    }
    fclose($fp);
    return array_reverse($lines);
}

function disk()
{
    $octets    = disk_free_space(__DIR__ . '/../logs/');
    $megaBytes = $octets / (1024 * 1024);
    return $megaBytes > 1024
        ? number_format($megaBytes / 1024, 1, '.', ',') . ' GB'
        : number_format($megaBytes, 0, '.', ',') . ' MB';
}