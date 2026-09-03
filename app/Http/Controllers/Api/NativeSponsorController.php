<?php

namespace App\Http\Controllers\Api;

use App\Models\NativeSponsor;
use Illuminate\Http\Request;

class NativeSponsorController extends BaseController
{
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $format = $request->query('format');
        
        $query = NativeSponsor::where('active', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now()->toDateString());
            });

        // Filtrar por formato se fornecido (verificando se a imagem daquele formato existe)
        if ($format) {
            if ($format === 'carousel' || $format === 'horizontal') {
                $query->whereNotNull('carousel_image_path');
            } elseif ($format === 'sidebar' || $format === 'square' || $format === 'vertical') {
                $query->whereNotNull('sidebar_image_path');
            } elseif ($format === 'list' || $format === 'card') {
                $query->whereNotNull('card_image_path');
            }
        }

        // Se uma categoria foi passada, resolve a hierarquia:
        // busca anúncios globais + desta categoria + da categoria pai (se houver)
        if ($categoryId) {
            // Busca a categoria para verificar se tem pai
            $category = \App\Models\Category::withoutGlobalScopes()->find($categoryId);
            
            $matchingIds = [(int) $categoryId];
            if ($category && $category->parent_id) {
                $matchingIds[] = (int) $category->parent_id;
            }
            
            $query->where(function ($q) use ($matchingIds) {
                $q->whereNull('category_id')
                  ->orWhereIn('category_id', $matchingIds);
            });
        } else {
            // Caso contrário, busca apenas anúncios globais
            $query->whereNull('category_id');
        }

        $cacheKey = 'api_native_sponsors_' . md5($categoryId . '_' . $format . '_' . $request->query('all'));
        $response = cache()->remember($cacheKey, 300, function () use ($query, $request, $format) {
            $sponsors = $query->get();

            if ($sponsors->isEmpty()) {
                return $this->respondSuccess(['result' => ['data' => $request->query('all') ? [] : null]]);
            }

            if ($request->query('all')) {
                $formatted = $sponsors->map(function ($sponsor) use ($format) {
                    // Seleciona a imagem com base no formato solicitado, ou a primeira que estiver disponível
                    $imageUrl = null;
                    if ($format === 'carousel' || $format === 'horizontal') {
                        $imageUrl = $sponsor->carousel_image_url;
                    } elseif ($format === 'sidebar' || $format === 'square' || $format === 'vertical') {
                        $imageUrl = $sponsor->sidebar_image_url;
                    } elseif ($format === 'list' || $format === 'card') {
                        $imageUrl = $sponsor->card_image_url;
                    }
                    
                    if (empty($imageUrl)) {
                        $imageUrl = $sponsor->carousel_image_url 
                            ?? $sponsor->sidebar_image_url 
                            ?? $sponsor->card_image_url;
                    }

                    return [
                        'id' => $sponsor->id,
                        'title' => $sponsor->title,
                        'link' => $sponsor->link,
                        'image' => $imageUrl,
                        'carousel_image_url' => $sponsor->carousel_image_url,
                        'sidebar_image_url' => $sponsor->sidebar_image_url,
                        'card_image_url' => $sponsor->card_image_url,
                    ];
                });
                
                return $this->respondSuccess([
                    'result' => [
                        'data' => $formatted
                    ]
                ]);
            }

            // Lógica de sorteio ponderado baseado na Frequência (peso 1-10)
            $weightedSponsors = [];
            foreach ($sponsors as $sponsor) {
                $weight = max(1, (int)$sponsor->frequency);
                for ($i = 0; $i < $weight; $i++) {
                    $weightedSponsors[] = $sponsor;
                }
            }
            
            $selectedSponsor = $weightedSponsors[array_rand($weightedSponsors)];

            // Seleciona a imagem com base no formato solicitado, ou a primeira que estiver disponível
            $imageUrl = null;
            if ($format === 'carousel' || $format === 'horizontal') {
                $imageUrl = $selectedSponsor->carousel_image_url;
            } elseif ($format === 'sidebar' || $format === 'square' || $format === 'vertical') {
                $imageUrl = $selectedSponsor->sidebar_image_url;
            } elseif ($format === 'list' || $format === 'card') {
                $imageUrl = $selectedSponsor->card_image_url;
            }
            
            if (empty($imageUrl)) {
                $imageUrl = $selectedSponsor->carousel_image_url 
                    ?? $selectedSponsor->sidebar_image_url 
                    ?? $selectedSponsor->card_image_url;
            }

            return $this->respondSuccess([
                'result' => [
                    'data' => [
                        'id' => $selectedSponsor->id,
                        'title' => $selectedSponsor->title,
                        'link' => $selectedSponsor->link,
                        'image' => $imageUrl,
                        'carousel_image_url' => $selectedSponsor->carousel_image_url,
                        'sidebar_image_url' => $selectedSponsor->sidebar_image_url,
                        'card_image_url' => $selectedSponsor->card_image_url,
                    ]
                ]
            ]);
        });

        return $response->header('Cache-Control', 'public, max-age=180, stale-while-revalidate=600');
    }
    
    protected function respondSuccess($data)
    {
        return response()->json(array_merge(['success' => true, 'message' => null], $data));
    }
}
