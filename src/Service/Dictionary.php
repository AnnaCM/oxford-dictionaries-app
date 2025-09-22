<?php

namespace App\Service;

use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Dictionary
{
    private HttpClientInterface $client;
    private string $serverHost;
    private string $appId;
    private string $appKey;

    const ALLOWED_DEFINITIONS_SOURCE_LANGS = [
        'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
        'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
        'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
    ];
    const DEFAULT_DEFINITIONS_SOURCE_LANG = 'en-gb';

    const ALLOWED_TRANSLATIONS_SOURCE_LANGS = [
        'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
        'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
        'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
        'tt' => 'Tatar', 'zh' => 'Chinese'
    ];
    const DEFAULT_TRANSLATIONS_SOURCE_LANG = 'en';

    const ALLOWED_TRANSLATIONS_TARGET_LANGS = [
        'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
        'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
        'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
        'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
        'yo' => 'Yoruba', 'zh' => 'Chinese'
    ];
    const DEFAULT_TRANSLATIONS_TARGET_LANG = 'es';

    public function __construct(
        HttpClientInterface $client,
        string $serverHost,
        string $appId,
        string $appKey
    ) {
        $this->client = $client;
        $this->serverHost = $serverHost;
        $this->appId = $appId;
        $this->appKey = $appKey;

        if (empty(trim($this->appId)) || empty(trim($this->appKey))) {
            throw new InvalidArgumentException("Authentication parameters missing. Check your .env file and make sure your App ID and App key are not blank!");
        }
    }

    protected function get(string $url, array $params = []): object
    {
        $params = array_merge($params, ['headers' => ['app_id' => $this->appId, 'app_key' => $this->appKey]]);

        $response = $this->client->request('GET', $this->serverHost . $url, $params);

        if ($response->getStatusCode() == 404) {
            throw new NotFoundError();
        } elseif ($response->getStatusCode() == 400) {
            throw new ValidationError();
        }

        return json_decode($response->getContent());
    }
}
