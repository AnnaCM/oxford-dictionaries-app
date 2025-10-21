<?php

namespace App\Controller;


use App\Service\CacheStore as CacheStoreService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class Autocomplete
{
    #[Route('/autocomplete', name: 'autocomplete', methods: ["GET"])]
    public function suggest(Request $request, CacheStoreService $redis): JsonResponse
    {
        $query = mb_strtolower($request->query->get('q'), 'UTF-8');;
        $sourceLang = strtolower($request->query->get('l'));
        $maxResults = 5;

        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }

        // Scan Redis for matches
        $dictionary = $redis->zRange("{$sourceLang}_dictionary_words", 0, -1, ['REV']);

        $suggestions = array_filter($dictionary, fn($word) => str_starts_with($word, $query));

        return new JsonResponse(array_slice($suggestions, 0, $maxResults));
    }
}
