<?php
/**
 * Bulk convert file names between encodings.
 *
 * For example, PHP versions prior to 7.1 wrote in Windows human-unreadable UTF-8
 * (characters looking like "РєСѓР»РёРЅР°СЂРёСЏ.txt")
 * But since 7.1 PHP writes UTF-8 in human-readable form, even in Windows.
 *
 * So we have a problem: UTF-8 encoded file names in Windows, written by PHP 7.0-,
 * are unrecognizable for people AND FOR NEW PHP TOO.
 * Hence we need to convert those gibberish file names into pretty ones.
 *
 * Run this file twice:
 * - 1st in old PHP (before 7.1), or in PHP with Windows encoding.
 * => It would create a list of file names (full path)     // list1.txt
 * => Also, a list of their sizes + timestamps, to check later against errors  // stat1.txt
 * On completion, you could see the ending message - the amount of processed files.
 *
 * - 2nd pass - in new PHP (7.1+), default (UTF-8) encoding.
 * => It would create the same list of file names, but with the new encoding they
 *    will be seen as gibberish    // list2.txt
 * => Again, a list of their sizes + timestamps, to compare with stat1.txt     // stat2.txt
 *
 * Then the lists would be compared, and wjen you press "OK", the files with non-ASCII
 * in their names will be renamed
 *
 * The same approach can be taken for reverse renaming or for other encodings.
 *
 * Author: Chang Zhao
 * Date: 07.02.2018 - 16.11.2018
 */

setlocale(LC_ALL, "C");                  // neutral locale
ini_set('max_execution_time', 600);      // for many files it can take a long time
define('B', dirname(__FILE__).DIRECTORY_SEPARATOR);	// base dir

// Set here the dirs where we will process the files.
// For DokuWiki, I set the following dirs:
// -= * EDIT IF NECESSARY! * =-

$dirPath = array('data'. DIRECTORY_SEPARATOR .'pages',
                 'data'. DIRECTORY_SEPARATOR .'media',
                 'data'. DIRECTORY_SEPARATOR .'meta',
                 'data'. DIRECTORY_SEPARATOR .'media-meta',
                 'data'. DIRECTORY_SEPARATOR .'media-attic',
                 'data'. DIRECTORY_SEPARATOR .'attic');

// For the first pass:

$outName1 = "list1.txt";    // file to list old file names
$outStat1 = "stat1.txt";    // file to list sizes and timestamps

$ready = 0;                 // We increment this variable to track the progress

if (file_exists($outName1)) {   // if the old file names have been listed
    $outName = "list2.txt";     // then - the next step, we make the second list
    $ready++;                   // progress mark +1
    if (file_exists($outName)) $ready += 4;   // = both lists ready
} else $outName = $outName1;                  // now $outName is 1st or 2nd,
                                              // depending on which pass it is.

if (file_exists($outStat1)) {   // if old file stats listed => list new ones
    $outStat = "stat2.txt";
    $ready += 2;                // progress mark +2 => if it == 3, both old lists are ready
    if (file_exists($outStat)) $ready += 8;    // 15 would mean both pairs of files are ready
} else $outStat = $outStat1;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style type="text/css">
.warn{ color: #A00000; }
.deb { color: #6666CC; }
.lo  { color: #AAA; }
    </style>
</head>
<body>
<?php

$s = '<p>Path: ';
foreach($dirPath as $p) $s .= $p . ', ';
echo rtrim($s, ', ') . '</p>' . PHP_EOL;   // For user convenience, print the paths we search

if ($ready < 15) {      //  0 = no files; 3 = 1st pair exists; 15 = both pairs exist

    // List the files. Save their names & stats into a pair of files

    // if (!empty($_REQUEST['out1'])) $outName = $_REQUEST['out1'];    // could tune via URI
    // if (!empty($_REQUEST['out2'])) $outStat = $_REQUEST['out2'];
    global $arr, $brr;              // arrays for files' names & files' stats
    foreach($dirPath as $dir) {     // fill those arrays
        dirToArray($dir);
    }

    echo '
File listing is done. ' . (($ready)? "2nd" : "1st" ) . ' pass OK.<br>
Processed: ' . count($arr) .', '. count($brr) . '. Writing to files...
<hr>';

    $out1 = fopen($outName, "w");   // and write them to files
    $out2 = fopen($outStat, "w");
    $i = 0;
    foreach ($arr as $str) {
        fwrite($out1, $str . PHP_EOL);
        fwrite($out2, $brr[$i++] . PHP_EOL);
    }
    fclose($out1);
    fclose($out2);
}

// Is now the first pass? => Then we done for now

if ($ready < 3) die("OK. Now run it in another encoding.</body></html>");


// This is the Second pass -> We go further.
// Are we ready for renaming?

// (1) If not yet -

if ($ready < 15 && (!$_REQUEST['convert'] || ($_REQUEST['convert'] !== "go"))) {

    // Compare files' stats, to prevent mistakes
    $stat1 = file($outStat1, FILE_IGNORE_NEW_LINES);
    $stat2 = file($outStat, FILE_IGNORE_NEW_LINES);
    $i = 0;
    $diffs = array();
    foreach($stat1 as $s) {
        if ($s !== $stat2[$i]) $diffs[] = $i++;   // log: files of which ## are different
    }

    // if file stats are all equal, perhaps it's safe to rename
    //  prepare the "Submit" button

    if (!empty($diffs)) {
        echo "<p class='warn'>Differences in file size/timestamp were detected.<br>
File correlations can be wrong. See diff of list1.txt & list2.txt for difference.<br>
The End...</p></body></html>";
        die();
    }
?>
<p>OK, old & new file lists seem to be identical (size & time).</p>
<hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="convert" id="convert" value="go">
    <input type="submit" value="Convert files' names">
</form>
</body>
</html>
<?php
}

// Now if both pairs of files are ready, and "submit" was pressed, we could
// actually compare stats & convert file names

$list1 = file($outName1, FILE_IGNORE_NEW_LINES);
$list2 = file($outName, FILE_IGNORE_NEW_LINES);

// In order to batch rename files in subfolders, we need to traverse dirs, starting from
// the deepest levels, and renaming folders only after having all the files in them renamed.
// So we go in the reverse order of the list, to ensure renaming parent dirs last.

$n=count($list1);
$i=$n;
$j=0;
$k=0;
while (--$i >= 0) {
    echo '<p class="deb">' . $list2[$i] . ' &rarr; ' . $list1[$i] . '</p>
';
    $chunks = explode(DIRECTORY_SEPARATOR, $list2[$i]);    // rename from
    $from = array_pop($chunks);
    $base = B.join(DIRECTORY_SEPARATOR, $chunks).DIRECTORY_SEPARATOR;
    $to = array_pop(explode(DIRECTORY_SEPARATOR, $list1[$i])); // to
    if ($from == $to) {
        echo '<span class="lo">' . htmlspecialchars("$from = $to", ENT_QUOTES) . "Skip...</span>.<br>
";
        continue;
    }
    if (rename($base.$from, $base.$to)){
        $j++;
        echo htmlspecialchars($base.$to, ENT_QUOTES).'<br>
';
    }
    else $k++;
}
echo "<hr>
Total files: $n; renamed: $j; errors: $k.</body></html>";

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
