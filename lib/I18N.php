<?php
require_once __DIR__ . '/DataStore.php';

class I18N {
    private static $defaultLang = 'en';
    private static $supported = ['en'];
    private static $lang = 'en';
    private static $ui = [];
    private static $categories = [];
    private static $fragrances = [];
    private static $pages = [];

    public static function init(array $config): void {
        self::$defaultLang = $config['defaultLanguage'] ?? 'en';
        self::$supported = $config['supportedLanguages'] ?? [self::$defaultLang];
        $requested = $_GET['lang'] ?? ($_COOKIE['lang'] ?? self::$defaultLang);
        self::setLanguage($requested);
        self::$ui = self::loadFile('ui');
        self::$categories = self::loadFile('categories');
        self::$fragrances = self::loadFile('fragrances');
        self::$pages = self::loadFile('pages');
    }

    private static function loadFile(string $name): array {
        $lang = self::$lang;
        $path = __DIR__ . '/../data/i18n/' . $name . '_' . $lang . '.json';
        $data = DataStore::readJson($path);
        if (empty($data) && $lang !== self::$defaultLang) {
            $fallbackPath = __DIR__ . '/../data/i18n/' . $name . '_' . self::$defaultLang . '.json';
            $data = DataStore::readJson($fallbackPath);
        }
        return $data;
    }

    public static function setLanguage(string $lang): void {
        if (!in_array($lang, self::$supported, true)) {
            $lang = self::$defaultLang;
        }
        self::$lang = $lang;
        setcookie('lang', $lang, time() + 60 * 60 * 24 * 30, '/');
    }

    public static function getLanguage(): string {
        return self::$lang;
    }

    public static function t(string $key, string $fallback = ''): string {
        $segments = explode('.', $key);
        $value = self::resolve(self::$ui, $segments);
        if ($value === null) {
            $value = self::resolve(self::loadFile('ui'), $segments);
        }
        if ($value === null) {
            $value = $fallback !== '' ? $fallback : self::resolve(self::loadFile('ui'), $segments, self::$defaultLang);
        }
        return $value ?? $fallback;
    }

    public static function tCategory(string $categoryKey, string $field): string {
        return self::resolve(self::$categories, [$categoryKey, $field]) ?? '';
    }

    public static function tFragrance(string $fragranceKey, string $field): string {
        return self::resolve(self::$fragrances, [$fragranceKey, $field]) ?? '';
    }

    public static function tPage(string $key): string {
        return self::resolve(self::$pages, explode('.', $key)) ?? '';
    }

    public static function autoTranslate(string $enText, string $targetLang): string {
        // Stub for future integration with a real translation API such as DeepL or Google Translate.
        // For now, simply return the English text as placeholder.
        return $enText;
    }

    private static function resolve(array $tree, array $segments) {
        $current = $tree;
        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }
        if (is_array($current)) {
            if (array_key_exists(self::$lang, $current)) {
                return $current[self::$lang];
            }
            if (array_key_exists(self::$defaultLang, $current)) {
                return $current[self::$defaultLang];
            }
            return $current; // raw array (e.g., olfactory pyramid lists)
        }
        return is_string($current) ? $current : null;
    }
}
