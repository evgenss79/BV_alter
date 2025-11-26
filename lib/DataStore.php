<?php
class DataStore {
    public static function readJson(string $path): array {
        if (!file_exists($path)) {
            return [];
        }
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    public static function writeJson(string $path, $data): bool {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $fp = fopen($path, 'c+');
        if (!$fp) {
            return false;
        }
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
}
