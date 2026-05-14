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
            'title' => 'Chapada Livre | Classificados da Chapada Diamantina: Compra e Venda em Seabra e Região',
            'description' => 'Encontre tudo na Chapada Diamantina: Carros, Imóveis, Serviços e muito mais em Seabra, Lençóis, Palmeiras e região. O melhor lugar para comprar e vender localmente.',
            'og_image' => url('/og-image.png'),
            'canonical' => $request->fullUrl(),
        ];

        // Lógica para Anúncio Específico - Título e Descrição mais "vendedores"
        $path = $request->path();
        if (preg_match('/\/(\d+)$/', $path, $matches)) {
            $postId = $matches[1];
            
            // Usamos Cache para não sobrecarregar o banco em múltiplos acessos
            $post = \Illuminate\Support\Facades\Cache::remember("seo_post_{$postId}", 600, function() use ($postId) {
                return \App\Models\Post::with(['category', 'city', 'pictures'])->find($postId);
            });
            
            if ($post) {
                $cityName = $post->city->name ?? 'Chapada Diamantina';
                $catName = $post->category->name ?? 'Classificados';
                
                $meta['title'] = $post->title . ' em ' . $cityName . ' - Chapada Diamantina | Chapada Livre';
                $meta['description'] = 'Confira ' . $post->title . ' em ' . $cityName . ' na categoria ' . $catName . '. Preço: ' . $post->price_formatted . '. ' . $post->excerpt . '. Veja detalhes no Chapada Livre.';
                
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
                $meta['title'] = implode(' ', $titleParts) . ' - Chapada Diamantina | Chapada Livre';
                $meta['description'] = 'Confira os melhores anúncios de ' . implode(' ', $titleParts) . ' na Chapada Diamantina. Veja fotos, preços e contatos de vendedores locais.';
            } else if (str_starts_with($path, 'category')) {
                $meta['title'] = 'Categorias de Anúncios - Chapada Diamantina | Chapada Livre';
            }
        }

        $html = str_replace(
            ['__META_TITLE__', '__META_DESCRIPTION__', '__OG_TITLE__', '__OG_DESCRIPTION__', '__OG_IMAGE__', '__CANONICAL_URL__'],
            [e($meta['title']), e($meta['description']), e($meta['title']), e($meta['description']), e($meta['og_image']), e($meta['canonical'])],
            $html
        );

        // Injeção de Conteúdo Estático para SEO (Robôs)
        $seoContent = $this->generateSeoContent($meta, $post ?? null);
        $html = str_replace('<div id="root">', $seoContent . "\n" . '<div id="root">', $html);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

      /**
     * Gera o HTML estático otimizado para robôs de busca (SEO) antes do carregamento do React.
     * Injetar este retorno obrigatoriamente logo após a abertura da tag <body>.
     */
    private function generateSeoContent($meta, $post = null)
    {
        $title = e($meta['title'] ?? 'Chapada Livre | Classificados da Chapada Diamantina');
        $desc = e($meta['description'] ?? 'Encontre carros, imóveis, empregos e serviços em Seabra e região.');
        
        // Garante correspondência exata caso o título vindo do banco omita o termo principal
        if (strpos(strtolower($title), 'chapada livre') === false) {
            $title = 'Chapada Livre | ' . $title;
        }

        $html = "\n\t<!-- INÍCIO DO CONTEÚDO DE INDEXAÇÃO DE SEO -->\n";
        // Ocultação visual limpa recomendada pelo Google (sem display:none)
        $html .= "\t<section style=\"position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;\">\n";
        
        if ($post) {
            $postTitle = e($post->title);
            $city = e($post->city->name ?? 'Seabra e Região');
            $price = e($post->price_formatted ?? 'A combinar');
            $postDesc = e($post->description);

            $html .= "\t\t<article>\n";
            $html .= "\t\t\t<h1>{$postTitle}</h1>\n";
            $html .= "\t\t\t<h2>Anúncio em {$city} - Chapada Diamantina</h2>\n";
            $html .= "\t\t\t<p><strong>Valor:</strong> {$price}</p>\n";
            $html .= "\t\t\t<div>{$postDesc}</div>\n";
            $html .= "\t\t</article>\n";

            // Rich Snippets JSON-LD para indexar preços e categorias no Google
            $html .= "\t\t<script type=\"application/ld+json\">\n";
            $html .= "\t\t{\n";
            $html .= "\t\t\t\"@context\": \"https://schema.org\",\n";
            $html .= "\t\t\t\"@type\": \"Thing\",\n";
            $html .= "\t\t\t\"name\": \"{$postTitle}\",\n";
            $html .= "\t\t\t\"description\": \"{$postDesc}\",\n";
            $html .= "\t\t\t\"areaServed\": \"{$city}\"\n";
            $html .= "\t\t}\n";
            $html .= "\t\t</script>\n";
        } else {
            // Estrutura limpa para a Página Inicial focada em Chapada Livre
            $html .= "\t\t<h1>{$title}</h1>\n";
            $html .= "\t\t<p>{$desc}</p>\n";
            
            $html .= "\t\t<nav>\n";
            $html .= "\t\t\t<h2>Categorias de Classificados - Chapada Livre</h2>\n";
            $html .= "\t\t\t<ul>\n";
            $html .= "\t\t\t\t<li><a href=\"/veiculos\">Carros e Veículos Usados em Seabra</a></li>\n";
            $html .= "\t\t\t\t<li><a href=\"/imoveis\">Casas, Terrenos e Imóveis em Lençóis e Palmeiras</a></li>\n";
            $html .= "\t\t\t\t<li><a href=\"/servicos\">Empregos, Serviços e Vagas em Itaberaba e região</a></li>\n";
            $html .= "\t\t\t\t<li><a href=\"/eletronicos\">Eletrônicos, Celulares e Móveis</a></li>\n";
            $html .= "\t\t\t</ul>\n";
            $html .= "\t\t</nav>\n";
        }
        
        $html .= "\t</section>\n\t<!-- FIM DO CONTEÚDO DE INDEXAÇÃO DE SEO -->\n";
        
        return $html;
    }
}