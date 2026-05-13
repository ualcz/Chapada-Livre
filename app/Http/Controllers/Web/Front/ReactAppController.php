<?php
/*
 * Chapa Livre — Serve a SPA React para todas as rotas de usuário.
 * O React Router cuida da navegação interna (/perfil, /meus-anuncios, etc.)
 */

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;

class ReactAppController extends Controller
{
    /**
     * Serve o index.html do bundle React para qualquer rota de usuário.
     * O React Router gerencia o roteamento do lado do cliente.
     */
    /**
     * Serve o index.html do bundle React com Meta Tags Dinâmicas para SEO.
     */
    public function serve(\Illuminate\Http\Request $request)
    {
        $indexPath = public_path('react/index.html');

        if (!file_exists($indexPath)) {
            abort(503, 'React app não encontrado. Execute: cd react-app && npm run build');
        }

        $html = file_get_contents($indexPath);

        // SEO Default - Tunado para busca regional
        $meta = [
            'title' => 'Chapa Livre | Classificados da Chapada Diamantina: Compra e Venda em Seabra e Região',
            'description' => 'Encontre tudo na Chapada Diamantina: Carros, Imóveis, Serviços e muito mais em Seabra, Lençóis, Palmeiras e região. O melhor lugar para comprar e vender localmente.',
            'og_image' => url('/og-image.png'),
            'canonical' => $request->fullUrl(),
        ];

        // Lógica para Anúncio Específico - Título e Descrição mais "vendedores"
        $path = $request->path();
        if (preg_match('/\/(\d+)$/', $path, $matches)) {
            $postId = $matches[1];
            $post = \App\Models\Post::with(['category', 'city', 'pictures'])->find($postId);
            
            if ($post) {
                $cityName = $post->city->name ?? 'Chapada Diamantina';
                $catName = $post->category->name ?? 'Classificados';
                
                $meta['title'] = $post->title . ' em ' . $cityName . ' - Chapada Diamantina | Chapa Livre';
                $meta['description'] = 'Confira ' . $post->title . ' em ' . $cityName . ' na categoria ' . $catName . '. Preço: ' . $post->price_formatted . '. ' . $post->excerpt . '. Veja detalhes no Chapa Livre.';
                
                if ($post->picture) {
                    $meta['og_image'] = $post->picture->file_url_large;
                }

                $meta['canonical'] = url($path);
            }
        }
        // Lógica para Busca / Categorias / Cidades
        else if (str_starts_with($path, 'buscar') || str_starts_with($path, 'category') || str_starts_with($path, 'location')) {
            $q = $request->query('q');
            $catSlug = null;
            $cityName = null;

            // Detectar categoria na URL: category/parent/child ou category/slug
            if (preg_match('/^category\/[^\/]+\/([^\/]+)$/', $path, $matches)) {
                $catSlug = $matches[1];
            } else if (preg_match('/^category\/([^\/]+)$/', $path, $matches)) {
                $catSlug = $matches[1];
            } 
            // Detectar cidade na URL: location/nome-da-cidade/id
            else if (preg_match('/^location\/([^\/]+)/', $path, $matches)) {
                $cityName = str_replace('-', ' ', $matches[1]);
            }

            $titleParts = [];
            if ($q) $titleParts[] = "Anúncios de \"$q\"";
            
            if ($catSlug) {
                $category = \App\Models\Category::where('slug', $catSlug)->first();
                if ($category) $titleParts[] = $category->name;
            }

            if ($cityName) {
                $titleParts[] = "em " . ucwords($cityName);
            }

            if (!empty($titleParts)) {
                $meta['title'] = implode(' ', $titleParts) . ' - Chapada Diamantina | Chapa Livre';
                $meta['description'] = 'Confira os melhores anúncios de ' . implode(' ', $titleParts) . ' na Chapada Diamantina. Veja fotos, preços e contatos de vendedores locais.';
            } else if (str_starts_with($path, 'category')) {
                $meta['title'] = 'Categorias de Anúncios - Chapada Diamantina | Chapa Livre';
            }
        }

        $html = str_replace(
            ['__META_TITLE__', '__META_DESCRIPTION__', '__OG_TITLE__', '__OG_DESCRIPTION__', '__OG_IMAGE__', '__CANONICAL_URL__'],
            [e($meta['title']), e($meta['description']), e($meta['title']), e($meta['description']), e($meta['og_image']), e($meta['canonical'])],
            $html
        );

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
