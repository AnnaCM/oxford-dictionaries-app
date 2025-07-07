<?php

namespace App\Controller;


use App\Exception\NotFoundError;
use App\Service\Definitions as DefinitionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Definitions extends AbstractController
{
    private DefinitionsService $service;

    public function __construct(DefinitionsService $service)
    {
        $this->service = $service;
    }

    /**
     * @Route("/definitions", name="definitions", methods={"GET"})
     */
    public function index(): Response
    {
        $defaultSourceLang = 'en-gb';

        return $this->render(
            'definitions/index.html.twig',
            $this->getParameters($defaultSourceLang)
        );
    }

    /**
     * @Route(
     *     "/definitions/{sourceLang}/{wordId}",
     *     name="definitionContent",
     *     methods={"GET"},
     *     requirements={"sourceLang"="[a-z]{2}(-[a-z]{2})?", "wordId":"[a-z]++"}
     * )
     */
    public function definitions(string $sourceLang, string $wordId): Response
    {
        try {
            $data = $this->service->getDefinitions($sourceLang, $wordId);
        } catch (\Throwable $e) {
            if ($e instanceof NotFoundError) {
                return $this->render('exceptions/error404.html.twig', array_merge(
                    ['text' => $wordId],
                    $this->getParameters($sourceLang)
                ));
            }

            throw $e;
        }

        $parameters = [
            'text' => $wordId,
            'senses' => $data->senses
        ];

        if (
            count($data->pronunciations) &&
            isset(array_values($data->pronunciations)[0]['phoneticSpelling'])
        ) {
            $parameters['sourceLangPhoneticSpelling'] = $data->pronunciations[array_keys($data->pronunciations)[0]]['phoneticSpelling'];
        }

        return $this->render(
            'definitions/content.html.twig',
            array_merge($parameters, $this->getParameters($sourceLang))
        );
    }

    private function getParameters(string $sourceLang): array
    {
        return [
            'selectedSourceLang' => $sourceLang,
            'sourceLangs' => $this->service::ALLOWED_DEFINITIONS_SOURCE_LANGS,
        ];
    }
}
