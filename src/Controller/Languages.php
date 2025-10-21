<?php

namespace App\Controller;

use App\Service\Definitions as DefinitionsService;
use App\Service\Translations as TranslationsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class Languages extends AbstractController
{
    public function __construct(
        private DefinitionsService $definitionsService,
        private TranslationsService $translationsService
    ) {}


    #[Route('/get-languages/{mode}', name: 'get_languages', methods: ["GET"])]
    public function getLanguages(string $mode): JsonResponse
    {
        if ($mode === 'translations') {
            return $this->json([
                'selectedSourceLang' => $this->translationsService::DEFAULT_TRANSLATIONS_SOURCE_LANG,
                'selectedTargetLang' => $this->translationsService::DEFAULT_TRANSLATIONS_TARGET_LANG,
                'sourceLangs' => $this->translationsService::ALLOWED_TRANSLATIONS_SOURCE_LANGS,
                'targetLangs' => $this->translationsService::ALLOWED_TRANSLATIONS_TARGET_LANGS
            ]);
        }

        // Definitions mode
        return $this->json([
            'selectedSourceLang' => $this->definitionsService::DEFAULT_DEFINITIONS_SOURCE_LANG,
            'sourceLangs' => $this->definitionsService::ALLOWED_DEFINITIONS_SOURCE_LANGS
        ]);
    }
}
