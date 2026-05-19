<?php
namespace CartOfficer\Scripts;

class AssetHandler
{
    public String $packageName = "cart-officer";
    public static function publishAssets()
    {
        // Path to the vendor directory
        $vendorDir = dirname(__DIR__, 4);
        $assetsDirs = ['public', 'templates'];
        foreach ($assetsDirs as $assetsDir) {
            // Target: project_root/public/packagename
            $targetDir = dirname($vendorDir) . '/'.$assetsDir.'/' . self::$packageName;
            // Source: vendor/vendor-name/package-name/public
           $sourceDir = dirname(__DIR__, 2) . '/'.$assetsDir;
            if (!is_dir($sourceDir)) {
                continue; // No assets to copy
            }
            self::copyDirectory($sourceDir, $targetDir);
        }
    }


    private static function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            if (is_dir($srcFile)) {
                self::copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
        closedir($dir);
    }
}