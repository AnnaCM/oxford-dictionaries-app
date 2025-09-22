<?php

namespace App\Controller;


use App\Exception\NotFoundError;
use App\Service\Translations as TranslationsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Translations extends AbstractController
{
    private TranslationsService $service;

    public function __construct(TranslationsService $service)
    {
        $this->service = $service;
    }

    /**
     * @Route("/translations", name="translations", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render(
            'translations/index.html.twig',
            $this->getParameters(
                $this->service::DEFAULT_TRANSLATIONS_SOURCE_LANG,
                $this->service::DEFAULT_TRANSLATIONS_TARGET_LANG
            )
        );
    }

    /**
     * @Route(
     *     "/translations/{sourceLang}/{targetLang}/{wordId}",
     *     name="translationContent",
     *     methods={"GET"},
     *     requirements={"sourceLang"="[a-z]{2}", "targetLang"="[a-z]{2}", "wordId":"[a-z]++"}
     * )
     */
    public function translations(string $sourceLang, string $targetLang, string $wordId): Response
    {
        try {
            $data = $this->service->getTranslations($sourceLang, $targetLang, $wordId);
        } catch (\Throwable $e) {
            if ($e instanceof NotFoundError) {
                return $this->render('exceptions/error404.html.twig', array_merge(
                    ['text' => $wordId],
                    $this->getParameters($sourceLang, $targetLang)
                ));
            }

            throw $e;
        }

        $parameters = [
            'text' => $wordId,
            'senses' => $data->senses
        ];

        if (count($data->pronunciations)) {
            $parameters['pronunciations'] = [];
            foreach($data->pronunciations as $dialect => $pronunciation) {
                if (in_array('phoneticSpelling', array_keys($pronunciation))) {
                    $parameters['pronunciations'][$dialect] = $pronunciation['phoneticSpelling'];
                }
            }
        }

        return $this->render(
            'translations/content.html.twig',
            array_merge($parameters, $this->getParameters($sourceLang, $targetLang))
        );
    }

    private function getParameters(string $sourceLang, string $targetLang): array
    {
        return [
            'selectedSourceLang' => $sourceLang,
            'selectedTargetLang' => $targetLang,
            'sourceLangs' => $this->service::ALLOWED_TRANSLATIONS_SOURCE_LANGS,
            'targetLangs' => $this->service::ALLOWED_TRANSLATIONS_TARGET_LANGS
        ];
    }
}
