# encoding

Bulk renaming files into another encoding.

This tool is useful, for example, in Windows - in order to use files written by PHP before 7.1 to PHP 7.1+.

PHP before 7.1 in Windows could create file names in UTF-8, but they looked gibberish (like "РєСѓР»РёРЅР°СЂРёСЏ.txt").

PHP 7.1+ introduced proper conversion, creating human-readable file names; but now PHP would not read those old file names properly (unless you set the default_charset as equal to the local codepage; but that would take file contents in a wrong encoding). So, in order to migrate to newer PHP and save the old files, we need to convert files' names to human-readable UTF-8 (as PHP 7.1+ writes them).

For that,

 1. in the text of this skript, replace the target folder names with whichever you need (currently there are folder names for standard DokuWiki installation);
 2. and the run the script twice:

  * 1st pass - with your old PHP (before 7.1) (or in PHP 7.1+ with default_charset or  internal_encoding equal to the local codepage?).
  * 2nd pass - in new PHP (7.1+), default charset equals to UTF-8, with EXACTLY the same set of dirs/files.

On each run, the tool creates two files with dirs & files listings. In the first run:

  * list1.txt - human-readable file names, with paths.
  * stat1.txt - a list of sizes and timestamps for those files

On the second run it creates:

  * list2.txt - a list of (actual, read in new way) file names, with paths;
  * stat2.txt (should be equal to stat1.txt, to use for basic error prevention).

Then it compares stat1.txt and stat2.txt.

If they are equal, it means that the files remain the same as they were during the first run. You could rewise the information the script gives you, and confirm renaming.

Then the files with non-ASCII names (like "РєСѓР»РёРЅР°СЂРёСЏ.txt", listed in list2.txt) will be renamed with corresponding lines from list1.txt (where it was read by older PHP and so was human-readable).

(Similarly, you can rename files the other way round, or between other encodings: run twice with corresponding encodings).

For DokuWiki:

  * WARNING! Backup before converting you files, just in case! In order to preserve file timestamps, ZIP them instead of just copying.
  * By default, run this tool from DokuWiki root dir (where "data" dir is).
  * By default the list of folders to convert is:
    * data\pages, data\media, data\meta, data\media-meta, data\media-attic, data\attic
  * Depending on the amount of files and the server speed, the script can work pretty long time (especially on renaming stage). Please set max execution time large (600 sec can be a good idea for large installation). If the script stops, you can run it again to continue renaming (then ignore warnings that it can't find some files, as they were already renamed).
  * Check the final statistics. If few files weren't renamed due to permission conflicts or something, you could grep their names from the text the script generated, and maybe you would run their renaming again, with better luck.

For the benefit of all!
