<?php
/**
 * Convert file names written in UTF-8 in Windows by PHP 7.0-
 * (they look like "РєСѓР»РёРЅР°СЂРёСЏ.txt")
 * to file names in actual human-readable UTF-8, as PHP 7.1+ writes
 *
 * Run this file twice:
 * 1st in old PHP (before 7.1), to create a list of old file names with paths
 * 2nd in new PHP (7.1+), to create the same list of them but with the new encoding
 *
 * Then the lists will be compared, and files with non-ASCII names will be renamed
 * Author: Chang Zhao
 * Date: 07.02.2018
 */
setlocale(LC_ALL, "C");                     // neutral
ini_set('max_execution_time', 600);      // it can be long
define('B', dirname(__FILE__).DIRECTORY_SEPARATOR);

// default paths where to read/convert file names: *EDIT IF NECESSARY!*
$dirPath = array('data'. DIRECTORY_SEPARATOR .'pages',
                 'data'. DIRECTORY_SEPARATOR .'media',
                 'data'. DIRECTORY_SEPARATOR .'meta',
                 'data'. DIRECTORY_SEPARATOR .'media-meta',
                 'data'. DIRECTORY_SEPARATOR .'media-attic',
                 'data'. DIRECTORY_SEPARATOR .'attic');
$outFile1 = "list1.txt";    // file to list old file names
$outStat1 = "stat1.txt";    // file to list old file sizes and timestamps (to check the correlations)
$ready = 0;                 // flag: if old files are already listed, we could convert
if (file_exists($outFile1)) {   // if old file names listed => list new ones
    $outFile = "list2.txt";
    $ready++;                   // flag +1
    if (file_exists($outFile)) $ready += 4;
} else $outFile = $outFile1;
if (file_exists($outStat1)) {   // if old file stats listed => list new ones
    $outStat = "stat2.txt";
    $ready += 2;                // flag +2 => if flag == 3, both old lists are ready
    if (file_exists($outStat)) $ready += 8;
} else $outStat = $outStat1;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style type="text/css">
.warn{ color: #A00000; }
.deb { color: #6666CC; }
    </style>
</head>
<body>
<?php

$s = '<p>Path: ';
foreach($dirPath as $p) $s .= $p . ', ';
echo rtrim($s, ', ') . '</p>' . PHP_EOL;

if ($ready < 15) {      // flag: 0 = no files; 3 = 1st pair exists; 15 = both pairs exist

    // list the files - and save the list (names + stats) into a pair of files

//    if (!empty($_REQUEST['out1'])) $outFile = $_REQUEST['out1'];    // for tuning via URI
//    if (!empty($_REQUEST['out2'])) $outStat = $_REQUEST['out2'];    // for tuning via URI
    global $arr, $brr;              // arrays for files' names & files' stats
    foreach($dirPath as $dir) {     // fill those arrays
        dirToArray($dir);
    }

    echo '
' . count($arr) .', '. count($brr) . '<hr>';

    $out1 = fopen($outFile, "w");   // and write them to files
    $out2 = fopen($outStat, "w");
    $i = 0;
    foreach ($arr as $str) {
        fwrite($out1, $str . PHP_EOL);
        fwrite($out2, $brr[$i++] . PHP_EOL);
    }
    fclose($out1);
    fclose($out2);
}

// If no pairs of files existed already - having created the 1st pair, we done for now

if ($ready < 3) die();

$stat1 = file($outStat1, FILE_IGNORE_NEW_LINES);

// Now if both pairs of files are ready, and "submit" was pressed, we can actually correlate & convert file names

if ($ready == 15 && $_REQUEST['convert'] == "go") {
    $list1 = file($outFile1, FILE_IGNORE_NEW_LINES);
    $list2 = file($outFile, FILE_IGNORE_NEW_LINES);
//    array_multisort(array_map('strlen', $list1), SORT_DESC, $list1, $list2, $stat1);
    for($i=0; $i<count($list1); $i++) {
        if ($list1[$i] == $list2[$i]) continue;
        echo '<p class="deb">' . $list2[$i] . ' &rarr; ' . $list1[$i] . '</p>' . PHP_EOL;
        rename(str_replace('\\', '\\', B.$list2[$i]), str_replace('\\', '\\', B.$list1[$i]));

        if ($stat1[0] == DIRECTORY_SEPARATOR)		// is_dir => after dir rename its paths have changed
            $list2 = str_replace($list2[$i] . DIRECTORY_SEPARATOR, $list1[$i] . DIRECTORY_SEPARATOR, $list2);
    }
    die('<hr>'. $i);      // = total files processed
}

// Otherwise prepare "submit"

// Compare files' stats, to prevent mistaken renaming

$stat2 = file($outStat, FILE_IGNORE_NEW_LINES);
$i = 0;
$diffs = array();
foreach($stat1 as $s) {
    if ($s == $stat2[$i++]) continue;
    $diffs[] = $i - 1;                  // in which lines files are different
}

// if file stats are all equal, perhaps it's safe to rename

if (empty($diffs)) echo "<p>OK, files and new names seem to be corresponding (by filesize & timestamp).</p>";
else die("<p class='warn'>Differences in file size/timestamp were detected. Files' correlations can be wrong.</p>");

/**
 * Lists all files and dirs under the path
 * @param $dir <= path to list
 * global $arr => array of file/dir names
 * global $brr => array of filesizes & timestamps
 */
function dirToArray($dir) {
    global $arr, $brr;
    $cdir = array_diff(scandir($dir, SCANDIR_SORT_NONE), array('..', '.'));
    foreach ($cdir as $file) {
        $fpath = $dir . DIRECTORY_SEPARATOR . $file;
        $isAscii = (mb_detect_encoding($file, "ASCII, UTF-8", TRUE) === "ASCII");
        $isDir = is_dir($fpath);
        if (!$isDir && $isAscii) continue;
        $arr[] = $fpath;
        $brr[] = (($isDir)? DIRECTORY_SEPARATOR : filesize($fpath)) . ' ' . filemtime($fpath);
        echo $fpath . '<br>' . PHP_EOL;
        if (is_dir($fpath)) dirToArray($fpath);
    }
}

?>
<hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="convert" id="convert" value="go">
    <input type="submit" value="Convert files' names">
</form>
</body>
</html>
