<?php

use App\Log;

require __DIR__ . '/../vendor/autoload.php';

if (isset($_FILES[UPLOAD_NO_AUTH_PARAMETER_NAME])) {
    $log = Log::upload($_FILES[UPLOAD_NO_AUTH_PARAMETER_NAME]);
    echo 'OK ' . $log->filename() . "\n";
    exit;
}

require __DIR__ . '/../templates/header.php';

if (isset($_FILES['files'])) {
    $api_key = $_REQUEST['api_key'] ?? '';
    if (!api_key_exists($api_key)) {
        ?>
        <div class="alert alert-danger">API KEY INVALIDE !</div>
        <?php
    } else {
        api_key_save($api_key);

        foreach (explode_files($_FILES['files']) as $file) {
            try {
                $log = Log::upload($file); ?>
                <div class="alert alert-success"><?= $file['name'] ?> &mdash; OK</div>
                <?php
            } catch (\Exception $exception) {
                ?>
                <div class="alert alert-danger"><?= $file['name'] ?> &mdash; <?= $exception->getMessage() ?></div>
                <?php
            }
        }
    }
}

?>

    <form method="post" action="upload.php" enctype="multipart/form-data">

        <div class="form-group">
            <label for="api_key">Api Key pour authentification :</label>
            <input type="text" id="api_key" name="api_key" class="form-control"
                   placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXXXXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                   value="<?= api_key_get() ?>"
            >
        </div>

        <div class="form-group mb-4">
            <label>Fichiers :</label>
            <input type="file" name="files[]" class="form-control-file" multiple>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>

    </form>

<?php

require __DIR__ . '/../templates/footer.php';

function api_key_exists($api_key)
{
    foreach (ACCOUNTS as $name => $config) {
        foreach (($config['keys'] ?? []) as $acc => $key) {
            if (trim($api_key) == $key) {
                return true;
            }
        }
    }
    return false;
}

function api_key_save($api_key)
{
    if (empty(session_id())) {
        session_start();
    }
    $_SESSION['api_key'] = $api_key;
}

function api_key_get()
{
    if (empty(session_id())) {
        session_start();
    }
    return $_SESSION['api_key'] ?? '';
}

function explode_files($array)
{
    $files = [];
    foreach (($array['name'] ?? []) as $i => $name) {
        $file = [
            'name'     => $array['name'][$i] ?? '',
            'type'     => $array['type'][$i] ?? '',
            'tmp_name' => $array['tmp_name'][$i] ?? '',
            'error'    => $array['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $array['size'][$i] ?? 0,
        ];
        if ($file['name'] && $file['size'] && $file['type']) {
            $files[] = $file;
        }
    }
    return $files;
}
