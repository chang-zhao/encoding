<?php
/**
 * Read old files with names in UTF-8
 * and rewrite them in new encoding
 * Date: 06.02.2018
 */
// defaults
$dirPath = './data/pages';
$dirPath2 = './data/pages2';
$enc = 'Windows-1251';
$hideAscii = TRUE;
$hideConverted = TRUE;

if (!empty($_REQUEST['from'])) $dirPath = $_REQUEST['from'];
if (!empty($_REQUEST['to'])) $dirPath2 = $_REQUEST['to'];
if (!empty($_REQUEST['enc'])) $enc = $_REQUEST['enc'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="<?php echo preg_replace("/\.php$/", ".css", $_SERVER['PHP_SELF']); ?>">
</head>
<body>
<h3>After migrating to PHP 7.1+ on Windows, you might want to convert file names to new UTF-8 encoding.</h3>
<p>!!! Before continuing, backup your 'data' folder into archive !!! (ZIP, for example).</p><hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" class="header">
    <div>
        <label for="from">Path:</label>
        <input type="text" name="from" id="from" value="<?php echo $dirPath; ?>">
    </div><div>
        <label for="to">Copy to path:</label>
        <input type="text" name="to" id="to" value="<?php echo $dirPath2; ?>">
    </div>
    <div>
        <label for="enc">Current encoding:</label>
        <input type="text" name="enc" id="enc" value="<?php echo $enc; ?>">
    </div>
    <input type="submit" value="<?php echo (empty($_REQUEST['enc']))? 'Show files' : 'Process'; ?>" id="write">
</form>
<?php if (empty($_REQUEST['enc'])) die();

if (($rootPages = opendir($dirPath)) === FALSE) die("Can't read dir"); ?>
<div class="table">
    <div id="files">
        <?php
        $i = 0;
        $files = array();
        $files2 = array();
        $detect = array();
        $dirs = array();
        while(($file = readdir($rootPages)) !== FALSE) {
            if ($file == ".") continue;
            $is_dir = is_dir($dirPath . "/" . $file);
            echo '<span data-num="' . $i++. '">' . $file . (($is_dir)? " /" : "") . '</span>
';
            $files[] = $file;
            $isAscii = mb_detect_encoding($file, "ASCII, " . $enc, TRUE);
            $detect[] = ($isAscii == "ASCII")? "ASCII" : $enc;
            $dirs[] = $is_dir;
        } ?>
    </div>
    <div id="encs">
        <?php for($j=0;$j<$i;$j++){
            echo '<span data-num="' . $j. '">' . $detect[$j] . '</span>
';
        }
?>
    </div>
    <div id="files2">
        <?php for($j=0;$j<$i;$j++){
            echo '<span data-num="' . $j. '">';
            if ($files[$j] != ".." && $detect[$j] !== "ASCII") {
                $files2[$j] = mb_convert_encoding($files[$j], $enc);
            } else {
                $files2[$j] = $files[$j];
            }
            echo $files2[$j] . '</span>
';
        }

//        echo (copy($dirPath . "/" . $file, $dirPath2 . "/" . $file2)) ? ' OK' : ' NOK';

        ?>
    </div>
</div>

<?php
//        echo " == " . $dirPath2 . "/" . mb_convert_encoding($file, 'UTF-16LE');
//        echo " == " . $dirPath2 . "/" . mb_convert_encoding($file, 'Windows-1251', 'UTF-16LE');
closedir($rootPages);
?>
</body>
</html>
