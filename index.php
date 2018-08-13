<?php
declare(strict_types=1);

$arrayOfSymlinksPaths = [];

function showTreeOfDirectoryForBrowser(string $folder, string $space, string $searchName = "")
{
    global $arrayOfSymlinksPaths;
    $files = scandir($folder);
    foreach ($files as $file) {
        if (($file == '.') || ($file == '..')) continue;
        $fullPathOfFile = $folder . '/' . $file;
        if (is_dir($fullPathOfFile) && is_readable($fullPathOfFile) && !is_link($fullPathOfFile)) {
            highlightSearchNameInTheTreeForBrowser($space . $file, $searchName);
            showTreeOfDirectoryForBrowser($fullPathOfFile, $space . '&nbsp;&nbsp;', $searchName);
        } elseif (is_link($fullPathOfFile) && is_readable($fullPathOfFile) && !detectInfiniteRecursion($fullPathOfFile)) {
            $arrayOfSymlinksPaths[] = $fullPathOfFile;
            highlightSearchNameInTheTreeForBrowser($space . $file, $searchName);
            showTreeOfDirectoryForBrowser($fullPathOfFile, $space . '&nbsp;&nbsp;', $searchName);
        } else highlightSearchNameInTheTreeForBrowser($space . $file, $searchName);
    }
}

function showTreeOfDirectoryForConsole(string $folder, string $space, string $searchName = "")
{
    global $arrayOfSymlinksPaths;
    $files = scandir($folder);
    foreach ($files as $file) {
        if (($file == '.') || ($file == '..')) continue;
        $fullPathOfFile = $folder . '/' . $file;
        if (is_dir($fullPathOfFile) && is_readable($fullPathOfFile) && !is_link($fullPathOfFile)) {
            highlightSearchNameInTheTreeForConsole($space . $file, $searchName);
            showTreeOfDirectoryForConsole($fullPathOfFile, $space . '  ', $searchName);
        } elseif (is_link($fullPathOfFile) && is_readable($fullPathOfFile) && !detectInfiniteRecursion($fullPathOfFile)) {
            $arrayOfSymlinksPaths[] = $fullPathOfFile;
            highlightSearchNameInTheTreeForConsole($space . $file, $searchName);
            showTreeOfDirectoryForConsole($fullPathOfFile, $space . '  ', $searchName);
        } else
            highlightSearchNameInTheTreeForConsole($space . $file, $searchName);
    }
}

function highlightSearchNameInTheTreeForBrowser(string $fileName, string $searchName)
{
    if ($searchName != "") {
        echo str_replace($searchName, '<b>' . $searchName . '</b>', $fileName) . "<br />";
    } else {
        echo $fileName . "<br />";
    }
}

function highlightSearchNameInTheTreeForConsole(string $fileName, string $searchName)
{
    if ($searchName != "") {
        echo str_replace($searchName, "\033[32m" . "{$searchName}\033[37m", $fileName) . "\n";
    } else {
        echo $fileName . "\n";
    }
}

function deleteRootDirName(string $symlinkPathForDetect, string $rootDirName): string
{
    $symlinkPathForDetect = str_replace($rootDirName, "", $symlinkPathForDetect);
    return $symlinkPathForDetect;
}

function detectTheSubdirectory($symlinkPathForDetect, $rootDirName): bool
{
    if (strstr($rootDirName, $symlinkPathForDetect) === false) {
        return false;
    } else {
        return true;
    }
}

function detectTheParentdirectory(string $symlinkPathForDetect, string $rootDirName): bool
{
    $rootDirName = str_replace(".//", "/", $rootDirName);
    if (stristr($symlinkPathForDetect, $rootDirName) === false) {
        return false;
    } else {
        return true;
    }
}

function detectInfiniteRecursion(string $symlinkPathForDetect): bool
{
    global $arrayOfSymlinksPaths;
    if (empty($arrayOfSymlinksPaths)) {
        return false;
    }

    foreach ($arrayOfSymlinksPaths as $symlink) {
        $symlinkPathForDetect = deleteRootDirName($symlinkPathForDetect, $symlink);
        if (detectTheSubdirectory($symlinkPathForDetect, $symlink) === false && detectTheParentdirectory($symlinkPathForDetect, $symlink) === false) {
            return false;
        }
    }
    return true;
}

if (php_sapi_name() === "cli") {
    if (isset($argv[1])) {
        showTreeOfDirectoryForConsole("./", "", $argv[1]);
    } else {
        showTreeOfDirectoryForConsole("./", "");
    }
} else {
    if (isset($_GET["searchName"])) {
        showTreeOfDirectoryForBrowser("./", "", $_GET["searchName"]);
    } else {
        showTreeOfDirectoryForBrowser("./", "");
    }

}
