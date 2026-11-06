<?php

use Valet\Drivers\BasicValetDriver;

class LocalValetDriver extends BasicValetDriver
{
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return true;
    }

    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        // 前台已由 Vue H5（localhost:5173）承载，旧的 index 模块页面不再使用
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($path === '/' || $path === '/index' || $path === '/index.html') {
            header('Location: http://localhost:5173/', true, 302);
            exit;
        }

        // 形如 /ujWLHBNEfM.php/index/login：入口文件后面带路径信息
        if (preg_match('#^/([^/]+\.php)(/.*)?$#', $path, $m)) {
            $phpFile = $sitePath . '/' . $m[1];
            if (is_file($phpFile)) {
                $_SERVER['SCRIPT_FILENAME'] = $phpFile;
                $_SERVER['SCRIPT_NAME'] = '/' . $m[1];
                $_SERVER['DOCUMENT_ROOT'] = $sitePath;
                if (!empty($m[2])) {
                    $_SERVER['PATH_INFO'] = $m[2];
                }
                return $phpFile;
            }
        }

        $frontController = parent::frontControllerPath($sitePath, $siteName, $uri);

        // ThinkPHP 需要 PATH_INFO 才能解析 模块/控制器/操作，Herd 默认不传
        if ($frontController && basename($frontController) === 'index.php') {
            $_SERVER['PATH_INFO'] = '/' . ltrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        }

        return $frontController;
    }
}
