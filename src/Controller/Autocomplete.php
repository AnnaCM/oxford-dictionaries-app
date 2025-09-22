<?php

namespace App\Controller;


use App\Service\CacheStore as CacheStoreService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Autocomplete
{
    /**
     * @Route("/autocomplete", name="autocomplete", methods={"GET"})
     */
    public function suggest(Request $request, CacheStoreService $redis): JsonResponse
    {
        $query = strtolower($request->query->get('q'));
        $maxResults = 5;

        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }

        // Scan Redis for matches
        $dictionary = $redis->zRange('dictionary_words', 0, -1, ['REV']);

        $suggestions = array_filter($dictionary, fn($word) => str_starts_with($word, $query));

        return new JsonResponse(array_slice($suggestions, 0, $maxResults));
    }
}
