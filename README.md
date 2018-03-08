# encoding

Bulk renaming files to another encoding.

This tool is useful in Windows for migrating from PHP before 7.1 to PHP 7.1+.

PHP before 7.1 in Windows created "gibberish" file names in UTF-8 (like "РєСѓР»РёРЅР°СЂРёСЏ.txt").

Then PHP 7.1+ won't read these files (unless you set the default_charset as equal to the local codepage; but that perhaps would read files contents in a wrong encoding?). So, in order to migrate to newer PHP, we need to convert files' names to human-readable UTF-8 (as PHP 7.1+ writes them). For that, this tool should be run twice:

  * 1st time either in your old PHP (before 7.1), or in PHP 7.1+ with default_charset equal to the local codepage.
  * 2nd time in new PHP (7.1+), default charset equals to UTF-8, with EXACTLY the same set of dirs/files.

(Instead of default_charset, internal_encoding should work too).

On each run the tool creates two file listings. On the first run:

  * list1.txt - a list of (human-readable) file names, with paths
  * stat1.txt - a list of sizes and timestamps for those files

On the second run it creates:

  * list2.txt - a list of (actual, read in new way) file names, with paths
  * stat2.txt (should be equal to stat1.txt, it's for basic error prevention)

Then it compares stat1.txt and stat2.txt, string by string.

If they are equal, it means that the files still remain the same as during the first run.

Then it renames files with non-ASCII names (like "РєСѓР»РёРЅР°СЂРёСЏ.txt", listed in list2.txt)
with corresponding lines from list1.txt (where it was read by older PHP and so was human-readable).

(Similarly you can rename files the other way round: run 1st in a new setting, 2nd time in an old one.)

For DokuWiki:

  * WARNING! Backup before converting files!
  * By default, run this tool from DokuWiki root dir (where "data" dir is).
  * If necessary, first of all edit the list of folders to convert. By default they are:
    * data\pages, data\media, data\meta, data\media-meta, data\media-attic, data\attic
  * If the script stopped, you can run it again to continue renaming (warnings are normal)
  * Feel free to improve this file with optimizations, error handling etc.
