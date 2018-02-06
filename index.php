<?php
/**
 * Read old files with names in UTF-8
 * and rewrite them in new encoding
 * Date: 06.02.2018
 */
setlocale(LC_ALL, "C");

// defaults
$dirPath = './data/pages';
$enc = 'Windows-1251';
$hideAscii = TRUE;
$hideConverted = TRUE;

if (!empty($_REQUEST['from'])) $dirPath = $_REQUEST['from'];
if (!empty($_REQUEST['hidea'])) $hideAscii = $_REQUEST['hidea'];
if (!empty($_REQUEST['hidec'])) $hideConverted = $_REQUEST['hidec'];
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
<p>!!! Before continuing, backup your 'data' folder into archive !!! (ZIP, for example).</p>
<p>Buttons: "⇒" - Rename; "x" - Pass. For bulk operations select several strings in the Result column.</p><hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" class="header">
    <div>
        <label for="from">Path:</label>
        <input type="text" name="from" id="from" value="<?php echo $dirPath; ?>">
    </div><div>
        <label for="hidea">Hide ASCII</label>
        <input type="checkbox" name="hidea" id="hidea" value="1" <?php echo ($hideAscii)? "checked" : ""; ?>>
        <label for="hidec">Hide Converted</label>
        <input type="checkbox" name="hidec" id="hidec" value="1" <?php echo ($hideConverted)? "checked" : ""; ?>>
    </div>
    <div>
        <label for="enc">Current encoding:</label>
        <input type="text" name="enc" id="enc" value="<?php echo $enc; ?>">
    </div>
    <input type="submit" value="<?php echo (empty($_REQUEST['enc']))? 'Show files' : 'Process'; ?>" id="write">
</form>
<?php if (empty($_REQUEST['enc'])) {
    echo '</body></html>';
    die();
}
if (($rootPages = opendir($dirPath)) === FALSE) die("Can't read dir");
?><hr>
<div class="table">
    <div id="files"><?php
        $i = 0;
        $files = array();
        $dirs = array();
        $files2 = array();
        $detect = array();
        while(($file = readdir($rootPages)) !== FALSE) {
            if ($file == ".") continue;
            $isAscii = (mb_detect_encoding($file, "ASCII, " . $enc, TRUE) === "ASCII");
            $is_dir = is_dir($dirPath . "/" . $file);
            if ($hideAscii && $isAscii && !$is_dir) continue;
            $files[] = $file;
            $detect[] = $isAscii;
            $dirs[] = $is_dir;
            echo '<span data-num="' . $i++ . '"' . (($is_dir)? ' class="folder"' : '') . '>' . $file . '</span>
';
        } ?>

    </div>
    <div id="encs">
        <?php for($j=0;$j<$i;$j++){
            echo '<span data-num="' . $j. '">' . (($detect[$j])? '-' : $enc) . '</span>
';
        }
?>
    </div>
    <div id="btns-conv">
        <?php for($j=0;$j<$i;$j++){
            if ($detect[$j])
                echo '<span data-num="' . $j. '" class="blank">&nbsp;</span>
';
            else
                echo '<span data-num="' . $j. '" role="button" class="conv" title="convert">⇒</span>
';
        }
        ?>
    </div>
    <div id="files2">
        <?php for($j=0;$j<$i;$j++){
            if ($hideAscii && $detect[$j]) {
                echo '<span data-num="' . $j. '">-</span>
';
                continue;
            };
            echo '<span data-num="' . $j. '">';
            if ($files[$j] != ".." && !$detect[$j]) {
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
    <div id="btns-del">
        <?php for($j=0;$j<$i;$j++){
            echo '<span data-num="' . $j. '" role="button" class="del" title="pass">x</span>
';
        }
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
