<?php

namespace App\Tests\Twig\Templates;

use App\Tests\Twig\Base;

class DefinitionsTest extends Base
{
    public function testIndexTemplate()
    {
        $options = [
            'selectedSourceLang' => 'en-gb',
            'sourceLangs' => [
                'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
            ]
        ];

        $haystack = $this->renderTemplate('definitions/index.html.twig', $options);
        $this->assertExtendsBaseTemplate($haystack);
        $this->assertDefinitionsLanguagesAreRendered($haystack);
    }

    /**
     * @dataProvider getOptions
     */
    public function testContentTemplate(array $options)
    {
        $haystack = $this->renderTemplate('definitions/index.html.twig', $options);
        $this->assertStringContainsString($options['text'], $haystack);
        if (isset($options['sourceLangPhoneticSpelling'])) {
            $this->assertStringContainsString("/{$options['sourceLangPhoneticSpelling']}/", $haystack);
        }
        if (isset($options['sourceLangAudioFile'])) {
            $filename = explode('/', $options['sourceLangAudioFile'])[count(explode('/', $options['sourceLangAudioFile'])) - 1];
            $this->assertStringContainsString('<button class="play-audio">', $haystack);
            $this->assertStringContainsString('<img src="/images/volume.png" height="15" alt="Listen">', $haystack);
            $this->assertStringContainsString('<audio hidden>', $haystack);
            $this->assertStringContainsString('<source src="' . "/audio-proxy/{$filename}" . '" type="audio/mpeg">', $haystack);
            $this->assertStringContainsString('<span class="audio-error"></span>', $haystack);
        }
        foreach ($options['senses'] as $key => $value) {
            $this->assertStringContainsString($key, $haystack);
            if (isset($value['definitions'])) {
                foreach ($value['definitions'] as $definition) {
                    $this->assertStringContainsString($definition, $haystack);
                }
            }

            if (isset($value['examples'])) {
                foreach ($value['examples'] as $example) {
                    $this->assertStringContainsString($example->text, $haystack);
                }
            }
        }
        $this->assertExtendsBaseTemplate($haystack);
        $this->assertDefinitionsLanguagesAreRendered($haystack);
    }

    public static function getOptions(): array
    {
        $example11 = new \stdClass();
        $example11->text = "the ace of diamonds";
        $example12 = new \stdClass();
        $register11 = new \stdClass();
        $register11->id = "figurative";
        $register11->text = "Figurative";
        $example12->registers = [$register11];
        $example12->text = "life had started dealing him aces again";
        $example31 = new \stdClass();
        $example31->text = "Nadal banged down eight aces in the set";
        $example43 = new \stdClass();
        $example43->text = "I didn't realize that I was ace for a long time";
        $example51 = new \stdClass();
        $example51->text = "he can ace opponents with serves of no more than 62 mph";

        $options = [
            'text' => 'ace',
            'selectedSourceLang' => 'en-gb',
            'sourceLangs' => [
                'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
            ],
        ];

        $optionsWithMixDefinitionsExamplesAndNoPhoneticSpellingOrAudioFile = $options;
        $optionsWithMixDefinitionsExamplesAndNoPhoneticSpellingOrAudioFile['senses'] = [
            'noun' => [
                [
                    'definitions' => [
                        "a playing card with a single spot on it, ranked as the highest card in its suit in most card games"
                    ],
                    'examples' => [$example11, $example12]
                ],
                [
                    'definitions' => ["a person who excels at a particular sport or other activity"]
                    // no examples
                ],
                [
                    'definitions' => ["(in tennis and similar games) a service that an opponent is unable to return and thus wins a point"],
                    'examples' => [$example31]
                ],
                [
                    'definitions' => ["an asexual person"]
                    // no examples
                ]
            ],
            'adjective' => [
                [
                    'definitions' => ["very good"]
                    // no examples
                ],
                [
                    // no definitions
                    'examples' => [$example43]
                ]
            ],
            'verb' => [
                [
                    'definitions' => ["(in tennis and similar games) serve an ace against (an opponent)"],
                    'examples' => [$example51]
                ],
                [
                    'definitions' => ["achieve high marks in (a test or exam)"]
                    // no examples
                ]
            ]
        ];

        $optionsWithNoDefinitionsExamplesAndPhoneticSpellingAndAudioFile = $options;
        $optionsWithNoDefinitionsExamplesAndPhoneticSpellingAndAudioFile['pronunciations'] = [
            'UK' => [
                'phoneticSpelling' => 'eɪs',
                'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/ace__gb_3.mp3',
            ]
        ];
        $optionsWithNoDefinitionsExamplesAndPhoneticSpellingAndAudioFile['senses'] = [];

        return [
            [$optionsWithMixDefinitionsExamplesAndNoPhoneticSpellingOrAudioFile],
            [$optionsWithNoDefinitionsExamplesAndPhoneticSpellingAndAudioFile],
        ];
    }
}
