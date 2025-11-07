<?php

namespace App\Controller;


use App\Exception\NotFoundError;
use App\Service\Definitions as DefinitionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Definitions extends AbstractController
{
    public function __construct(private DefinitionsService $service) {}


    #[Route(['/', '/definitions'], name: 'definitions', methods: ["GET"])]
    public function index(): Response
    {
        return $this->render(
            'definitions/index.html.twig',
            $this->getParameters($this->service::DEFAULT_DEFINITIONS_SOURCE_LANG)
        );
    }


    #[Route(
        '/definitions/{sourceLang<[a-z]{2}(-[a-z]{2})?>}/{wordId<[a-zà-ü-]++>}',
        name: 'definitionContent',
        methods: ["GET"]
    )]
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

        return $this->render(
            'definitions/content.html.twig',
            array_merge(
                [
                    'text' => $wordId,
                    'senses' => $data->senses,
                    'pronunciations' => $data->pronunciations
                ],
                $this->getParameters($sourceLang)
            )
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
