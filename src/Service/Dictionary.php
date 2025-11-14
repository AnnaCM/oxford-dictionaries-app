<?php

namespace App\Service;

use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Dictionary
{
    public const ALLOWED_DEFINITIONS_SOURCE_LANGS = [
        'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
        'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
        'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese',
    ];
    public const DEFAULT_DEFINITIONS_SOURCE_LANG = 'en-gb';

    public const ALLOWED_TRANSLATIONS_SOURCE_LANGS = [
        'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
        'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
        'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
        'tt' => 'Tatar', 'zh' => 'Chinese',
    ];
    public const DEFAULT_TRANSLATIONS_SOURCE_LANG = 'en';

    public const ALLOWED_TRANSLATIONS_TARGET_LANGS = [
        'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
        'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
        'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
        'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
        'yo' => 'Yoruba', 'zh' => 'Chinese',
    ];
    public const DEFAULT_TRANSLATIONS_TARGET_LANG = 'es';

    public function __construct(
        private HttpClientInterface $client,
        private string $serverHost,
        private string $appId,
        private string $appKey,
    ) {
        if (empty(trim($this->appId)) || empty(trim($this->appKey))) {
            throw new \InvalidArgumentException('Authentication parameters missing. Check your .env file and make sure your App ID and App key are not blank!');
        }
    }

    protected function get(string $url, array $params = []): object
    {
        $params = array_merge($params, ['headers' => ['app_id' => $this->appId, 'app_key' => $this->appKey]]);

        $response = $this->client->request('GET', $this->serverHost.$url, $params);

        if (404 == $response->getStatusCode()) {
            throw new NotFoundError();
        } elseif (400 == $response->getStatusCode()) {
            throw new ValidationError();
        }

        return json_decode($response->getContent());
    }
}
