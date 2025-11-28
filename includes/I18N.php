<?php

class MultilingualTranslation {
    private $translations = [];

    public function __construct($language) {
        $this->loadTranslations($language);
    }

    private function loadTranslations($language) {
        // Logic to load translations from files or database
        switch ($language) {
            case 'en':
                $this->translations = [
                    'hello' => 'Hello',
                    'goodbye' => 'Goodbye'
                ];
                break;
            case 'es':
                $this->translations = [
                    'hello' => 'Hola',
                    'goodbye' => 'Adiós'
                ];
                break;
            // Additional languages can be added here
            default:
                $this->translations = [
                    'hello' => 'Hello',
                    'goodbye' => 'Goodbye'
                ];
                break;
        }
    }

    public function translate($key) {
        return $this->translations[$key] ?? $key;
    }
}

?>