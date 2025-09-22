<?php

namespace App\Tests\Twig\Templates;

use App\Tests\Twig\Base;

class TranslationsTest extends Base
{
    public function testIndexTemplate()
    {
        $options = [
            'selectedSourceLang' => 'en',
            'selectedTargetLang' => 'it',
            'sourceLangs' => [
                'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
                'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
                'tt' => 'Tatar', 'zh' => 'Chinese'
            ],
            'targetLangs' => [
                'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
                'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
                'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
                'yo' => 'Yoruba', 'zh' => 'Chinese'
            ]
        ];

        $haystack = $this->renderTemplate('translations/index.html.twig', $options);
        $this->assertExtendsBaseTemplate($haystack);
        $this->assertTranslationsLanguagesAreRendered($haystack);
    }

    /**
     * @dataProvider getOptions
     */
    public function testContentTemplate(array $options)
    {
        $haystack = $this->renderTemplate('translations/index.html.twig', $options);
        $this->assertStringContainsString($options['text'], $haystack);
        if (isset($options['pronunciations'])) {
            $dialect = array_keys($options['pronunciations'])[0];
            $phoneticSpelling = array_values($options['pronunciations'])[0];
            $this->assertStringContainsString("{$dialect}: /{$phoneticSpelling}/", $haystack);

            if (count($options['pronunciations']) > 1) {
                foreach (array_slice($options['pronunciations'], 1) as $dialect => $phoneticSpelling) {
                    $this->assertStringContainsString("  |  {$dialect}: /{$phoneticSpelling}/", $haystack);
                }
            }
        }
        foreach ($options['senses'] as $key => $value) {
            $this->assertStringContainsString($key, $haystack);
            if (isset($value['notes'])) {
                foreach ($value['notes'] as $note) {
                    $this->assertStringContainsString($note->text, $haystack);
                }
            }
            if (isset($value['translations'])) {
                foreach ($value['translations'] as $translation) {
                    $this->assertStringContainsString($translation->text, $haystack);
                }
            }
            if (isset($value['examples'])) {
                foreach ($value['examples'] as $example) {
                    $this->assertStringContainsString($example->text, $haystack);
                    foreach ($example->translations as $translation) {
                        $this->assertStringContainsString($translation->text, $haystack);
                    }
                }
            }
        }
        $this->assertExtendsBaseTemplate($haystack);
        $this->assertTranslationsLanguagesAreRendered($haystack);
    }

    public function getOptions(): array
    {
        $notes11 = new \stdClass();
        $notes11->text = 'lively';
        $notes11->type = 'indicator';
        $collocations11 = new \stdClass();
        $collocations11->id = 'child';
        $collocations11->text = 'child';
        $collocations11->type = 'object';
        $translations11 = new \stdClass();
        $translations11->collocations = [$collocations11];
        $translations11->language = 'it';
        $translations11->text = 'vivace, sveglio';
        $collocations12 = new \stdClass();
        $collocations12->id = 'old_person';
        $collocations12->text = 'old person';
        $collocations12->type = 'object';
        $translations12 = new \stdClass();
        $translations12->collocations = [$collocations12];
        $translations12->language = 'it';
        $translations12->text = 'arzillo';
        $translations21 = new \stdClass();
        $translations21->language = 'it';
        $translations21->text = 'vigile';
        $translations22 = new \stdClass();
        $translations22->language = 'it';
        $translations22->text = 'attento';
        $notes21 = new \stdClass();
        $notes21->text = 'attentive';
        $notes21->type = 'indicator';
        $examplesTranslationsCollocations21 = new \stdClass();
        $examplesTranslationsCollocations21->id = 'danger';
        $examplesTranslationsCollocations21->text = 'danger';
        $examplesTranslationsCollocations21->type = 'object';
        $examplesTranslationsCollocations22 = new \stdClass();
        $examplesTranslationsCollocations22->id = 'risk';
        $examplesTranslationsCollocations22->text = 'risk';
        $examplesTranslationsCollocations22->type = 'object';
        $examplesTranslationsCollocations23 = new \stdClass();
        $examplesTranslationsCollocations23->id = 'fact';
        $examplesTranslationsCollocations23->text = 'fact';
        $examplesTranslationsCollocations23->type = 'object';
        $examplesTranslationsCollocations24 = new \stdClass();
        $examplesTranslationsCollocations24->id = 'possibility';
        $examplesTranslationsCollocations24->text = 'possibility';
        $examplesTranslationsCollocations24->type = 'object';
        $examplesTranslations21 = new \stdClass();
        $examplesTranslations21->collocations = [
            $examplesTranslationsCollocations21,
            $examplesTranslationsCollocations22,
            $examplesTranslationsCollocations23,
            $examplesTranslationsCollocations24
        ];
        $examplesTranslations21->language = 'it';
        $examplesTranslations21->text = 'essere consapevole di';
        $examples21 = new \stdClass();
        $examples21->text = 'to be alert to';
        $examples21->translations = [$examplesTranslations21];
        $translations31 = new \stdClass();
        $translationsGrammaticalFeatures31 = new \stdClass();
        $translationsGrammaticalFeatures31->id = 'masculine';
        $translationsGrammaticalFeatures31->text = 'Masculine';
        $translationsGrammaticalFeatures31->type = 'Gender';
        $translations31->grammaticalFeatures = [$translationsGrammaticalFeatures31];
        $translations31->language = 'it';
        $translations31->text = 'allarme';
        $examplesTranslationsCollocations31 = new \stdClass();
        $examplesTranslationsCollocations31->id = 'danger';
        $examplesTranslationsCollocations31->text = 'danger';
        $examplesTranslationsCollocations31->type = 'object';
        $examplesTranslations31 = new \stdClass();
        $examplesTranslations31->collocations = [$examplesTranslationsCollocations31];
        $examplesTranslations31->language = 'it';
        $examplesTranslations31->text = 'stare in guardia contro';
        $examples31 = new \stdClass();
        $examples31->text = 'to be on the alert for';
        $examples31->translations = [$examplesTranslations31];
        $examplesTranslations32 = new \stdClass();
        $examplesTranslations32->language = 'it';
        $examplesTranslations32->text = 'allarme antincendio, allarme bomba';
        $examples32 = new \stdClass();
        $examples32->text = 'fire, bomb alert';
        $examples32->translations = [$examplesTranslations32];
        $examplesTranslations33 = new \stdClass();
        $examplesTranslations33->language = 'it';
        $examplesTranslations33->text = 'allarme di sicurezza';
        $examples33 = new \stdClass();
        $examples33->text = 'security alert';
        $examples33->translations = [$examplesTranslations33];
        $examplesTranslations34 = new \stdClass();
        $examplesTranslations34->language = 'it';
        $examplesTranslations34->text = 'essere in stato di massima allerta';
        $examples34 = new \stdClass();
        $examples34->text = 'to be on (red) alert (Military) or on full alert';
        $examples34->translations = [$examplesTranslations34];
        $translations41 = new \stdClass();
        $translations41->language = 'it';
        $translations41->text = 'allertare, mettere in stato d\'allerta';
        $examplesTranslationsCollocations51 = new \stdClass();
        $examplesTranslationsCollocations51->id = 'danger';
        $examplesTranslationsCollocations51->text = 'danger';
        $examplesTranslationsCollocations51->type = 'object';
        $examplesTranslations51 = new \stdClass();
        $examplesTranslations51->collocations = [$examplesTranslationsCollocations51];
        $examplesTranslations51->language = 'it';
        $examplesTranslations51->text = 'mettere qcn in guardia contro';
        $examplesTranslationsCollocations52 = new \stdClass();
        $examplesTranslationsCollocations52->id = 'fact';
        $examplesTranslationsCollocations52->text = 'fact';
        $examplesTranslationsCollocations52->type = 'object';
        $examplesTranslationsCollocations53 = new \stdClass();
        $examplesTranslationsCollocations53->id = 'situation';
        $examplesTranslationsCollocations53->text = 'situation';
        $examplesTranslationsCollocations53->type = 'object';
        $examplesTranslations52 = new \stdClass();
        $examplesTranslations52->collocations = [$examplesTranslationsCollocations52, $examplesTranslationsCollocations53];
        $examplesTranslations52->language = 'it';
        $examplesTranslations52->text = 'richiamare l\'attenzione di qcn su';
        $examples51 = new \stdClass();
        $examples51->text = 'to alert sb to';
        $examples51->translations = [$examplesTranslations51, $examplesTranslations52];

        $options = [
            'text' => 'alert',
            'selectedSourceLang' => 'en',
            'selectedTargetLang' => 'it',
            'sourceLangs' => [
                'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
                'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
                'tt' => 'Tatar', 'zh' => 'Chinese'
            ],
            'targetLangs' => [
                'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
                'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
                'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
                'yo' => 'Yoruba', 'zh' => 'Chinese'
            ]
        ];

        $optionsWithTranslationsNotesAndExamplesAndNoPronunciations = $options;
        $optionsWithTranslationsNotesAndExamplesAndNoPronunciations['senses'] = [
            'adjective' => [
                [
                    'translations' => [$translations11, $translations12],
                    'notes' => [$notes11]
                    // no examples
                ],
                [
                    'translations' => [$translations21, $translations22],
                    'notes' => [$notes21],
                    'examples' => [$examples21]
                ]
            ],
            'noun' => [
                [
                    'translations' => [$translations31],
                    // no notes
                    'examples' => [
                        $examples31,
                        $examples32,
                        $examples33,
                        $examples34
                    ]
                ]
            ],
            'verb' => [
                [
                    'translations' => [$translations41],
                    // no notes
                    // no examples
                ],
                [
                    // no translations
                    // no notes
                    'examples' => [$examples51]
                ]
            ]
        ];

        $optionsWithNoTranslationsNotesAndExamplesAndOnePronunciation = $options;
        $optionsWithNoTranslationsNotesAndExamplesAndOnePronunciation['pronunciations'] = ['UK' => 'əˈləːt'];
        $optionsWithNoTranslationsNotesAndExamplesAndOnePronunciation['senses'] = [];

        $optionsWithNoTranslationsNotesAndExamplesAndTwoPronunciations = $options;
        $optionsWithNoTranslationsNotesAndExamplesAndTwoPronunciations['pronunciations'] = [
            'UK' => 'əˈləːt',
            'US' => 'əˈlərt'
        ];
        $optionsWithNoTranslationsNotesAndExamplesAndTwoPronunciations['senses'] = [];

        return [
            [$optionsWithTranslationsNotesAndExamplesAndNoPronunciations],
            [$optionsWithNoTranslationsNotesAndExamplesAndOnePronunciation],
            [$optionsWithNoTranslationsNotesAndExamplesAndTwoPronunciations],
        ];
    }
}
