<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyLanguageHelper
{
    /** @var LinklyLanguageHelper singleton instance */
    protected static $instance;

    protected function __construct()
    {
        $this->checkLanguageFiles();
    }

    public static function instance(): LinklyLanguageHelper
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getLanguageSelectOptions(): string
    {
        $availableLanguages = $this->getAvailableLanguages();
        $selectedLanguage = get_option('linkly_settings_language');
        $options = '<option value="wordpress" ' . ($selectedLanguage == "wordpress" ? 'selected' : '') . '>Wordpress Language</option>';
        foreach ($availableLanguages as $code => $data) {
            $options .= '<option value="' . $code . '" ' . ($selectedLanguage === $code ? 'selected' : '') . '>' . $data["name"] . '</option>';
        }

        return $options;
    }

    public function get($key, array $parameters = []): string
    {
        $strings = $this->getLanguageStrings();
        $keys = explode(".", $key);
        $value = $strings;
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $key;
            }
        }

        for ($i = 0; $i < count($parameters); $i++) {
            $value = str_replace("{{$i}}", $parameters[$i], $value);
        }

        return $value;
    }

    private function getAvailableLanguages(): array
    {
        $languageFiles = glob(__DIR__ . "/../assets/languages/*.json");
        $languages = [];
        try {
            foreach ($languageFiles as $languageFile) {
                $language = json_decode(file_get_contents($languageFile), true, 512, JSON_THROW_ON_ERROR);
                $languages[$language['code']] = ["name" => $language['name'], "File" => $languageFile];
            }
        } catch (JsonException $e) {
        }

        return $languages;
    }

    private function checkLanguageFiles(): void
    {
        $languages = $this->getAvailableLanguages();
        if (get_option('linkly_settings_language') !== "wordpress" && !isset($languages[get_option('linkly_settings_language')])) {
            update_option('linkly_settings_language', "wordpress");
        }


        $languageFiles = glob(__DIR__ . "/../assets/languages/*.json");

        if (count($languageFiles) === 0) {
            $this->downloadDefaultLanguageFiles();
        }
        $foundCodes = [];
        foreach ($languageFiles as $languageFile) {
            try {
                $language = json_decode(file_get_contents($languageFile), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->downloadDefaultLanguageFiles();
            }
            if (!isset($language['code'], $language['name'])) {
                $this->downloadDefaultLanguageFiles();
            }
            if (in_array($language['code'], $foundCodes, true)) {
                $this->downloadDefaultLanguageFiles();
            }
            $foundCodes[] = $language['code'];
        }
    }

    private function downloadDefaultLanguageFiles()
    {
        // TODO download language files
    }

    private function getLanguageStrings()
    {
        $langCode = get_option('linkly_settings_language');
        if ($langCode === "wordpress") {
            $langCode = get_locale();
        }
        $languages = $this->getAvailableLanguages();
        if (isset($languages[$langCode])) {
            try {
                $language = json_decode(file_get_contents($languages[$langCode]['File']), true, 512, JSON_THROW_ON_ERROR);
                if (!isset($language['translations'])) {
                    ;

                    return [];
                }

                return $language['translations'];
            } catch (JsonException $e) {
            }
        } else {
            $langCode = "en_US";
            if (isset($languages[$langCode])) {
                try {
                    $language = json_decode(file_get_contents($languages[$langCode]['File']), true, 512, JSON_THROW_ON_ERROR);
                    if (!isset($language['translations'])) {
                        ;

                        return [];
                    }

                    return $language['translations'];
                } catch (JsonException $e) {
                }
            }
        }
        linkly_dd($languages);

        return [];
    }


}