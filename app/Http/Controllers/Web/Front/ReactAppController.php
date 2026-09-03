<?php
/*
 * Chapada Livre — Serve a SPA React para todas as rotas de usuário.
 * O React Router cuida da navegação interna (/perfil, /meus-anuncios, etc.)
 */

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;

class ReactAppController extends Controller
{
    /**
     * Serve o index.html do bundle React com Meta Tags Dinâmicas para SEO e redes sociais.
     */
    public function serve(\Illuminate\Http\Request $request)
    {
        $path    = $request->path();
        $fullUrl = $request->url(); // URL sem query string para canonical limpa

        $indexPath = public_path('react/index.html');

        if (!file_exists($indexPath)) {
            abort(503, 'React app não encontrado. Execute: cd react-app && npm run build');
        }

        $mtime    = filemtime($indexPath);
        $cacheKey = 'react_seo_html_v3_' . md5($fullUrl) . '_' . $mtime;

        // Cacheia o HTML completo por 10 minutos (600 segundos)
        $html = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($request, $path, $fullUrl, $indexPath) {
            $htmlContent = file_get_contents($indexPath);

            // Se o build não tiver placeholders, devolve o HTML direto
            if (strpos($htmlContent, '__OG_TITLE__') === false && strpos($htmlContent, '__META_TITLE__') === false) {
                return $htmlContent;
            }

            // ---------------------------------------------------------------
            // Meta padrão (usado para a home e páginas genéricas)
            // ---------------------------------------------------------------
            $siteUrl  = rtrim(config('app.url', 'https://chapadalivre.com.br'), '/');
            $fallbackImage = $siteUrl . '/react/logo.webp';

            $meta = [
                'title'       => 'Chapada Livre | Classificados da Chapada Diamantina',
                'description' => 'Encontre tudo na Chapada Diamantina: Carros, Imóveis, Serviços e muito mais em Seabra, Lençóis, Palmeiras e região.',
                'og_image'    => $fallbackImage,
                'og_url'      => $siteUrl . '/' . ltrim($path, '/'),
                'canonical'   => $siteUrl . '/' . ltrim($path, '/'),
            ];

            $post = null;

            // ---------------------------------------------------------------
            // Detecta ID numérico no final da URL:
            //   /pasta-de-dente-do-sherek/76  → ID = 76
            //   /anuncio/nome/76              → ID = 76
            // ---------------------------------------------------------------
            if (preg_match('#(?:^|/)(\d+)/?$#', $path, $matches)) {
                $postId = (int) $matches[1];

                try {
                    $post = \Illuminate\Support\Facades\Cache::remember("seo_post_{$postId}", 600, function () use ($postId) {
                        return \App\Models\Post::with(['category', 'city', 'pictures', 'picture'])->find($postId);
                    });
                } catch (\Throwable $e) {
                    $post = null;
                }

                if ($post) {
                    $cityName = $post->city->name ?? 'Chapada Diamantina';
                    $catName  = $post->category->name ?? 'Classificados';
                    $price    = $post->price_formatted ?? null;

                    $titleParts = [$post->title, 'em', $cityName, '— Chapada Livre'];
                    $meta['title'] = implode(' ', $titleParts);

                    $descParts = ['Confira', $post->title, 'em', $cityName, 'na categoria', $catName . '.'];
                    if ($price) {
                        $descParts[] = 'Preço:';
                        $descParts[] = $price . '.';
                    }
                    if (!empty($post->excerpt)) {
                        $descParts[] = $post->excerpt;
                    }
                    $meta['description'] = implode(' ', $descParts);

                    // Imagem do anúncio (prioridade: primeira foto grande)
                    if (!empty($post->picture?->file_url_large)) {
                        $meta['og_image'] = $post->picture->file_url_large;
                    } elseif ($post->pictures && $post->pictures->isNotEmpty()) {
                        $firstPic = $post->pictures->first();
                        if (!empty($firstPic->file_url_large)) {
                            $meta['og_image'] = $firstPic->file_url_large;
                        }
                    }

                    // Canonical limpo (sem query string)
                    $slug = \Illuminate\Support\Str::slug($post->title);
                    $meta['canonical'] = $siteUrl . '/' . $slug . '/' . $postId;
                    $meta['og_url']    = $meta['canonical'];
                }
            }
            // ---------------------------------------------------------------
            // Busca / Categorias / Cidades
            // ---------------------------------------------------------------
            elseif (str_starts_with($path, 'buscar') || str_starts_with($path, 'category') || str_starts_with($path, 'location')) {
                $q       = $request->query('q');
                $catSlug = null;
                $cityName = null;

                if (preg_match('#^category/[^/]+/([^/]+)$#', $path, $m)) {
                    $catSlug = $m[1];
                } elseif (preg_match('#^category/([^/]+)$#', $path, $m)) {
                    $catSlug = $m[1];
                } elseif (preg_match('#^location/([^/]+)#', $path, $m)) {
                    $cityName = str_replace('-', ' ', $m[1]);
                }

                $titleParts = [];
                if ($q) $titleParts[] = "Anúncios de \"$q\"";

                if ($catSlug) {
                    try {
                        $category = \App\Models\Category::where('slug', $catSlug)->first();
                        if ($category) $titleParts[] = $category->name;
                    } catch (\Throwable $e) {}
                }

                if ($cityName) $titleParts[] = 'em ' . ucwords($cityName);

                if (!empty($titleParts)) {
                    $meta['title']       = implode(' ', $titleParts) . ' — Chapada Livre';
                    $meta['description'] = 'Confira os melhores anúncios de ' . implode(' ', $titleParts) . ' na Chapada Diamantina.';
                } elseif (str_starts_with($path, 'category')) {
                    $meta['title'] = 'Categorias de Anúncios — Chapada Diamantina | Chapada Livre';
                }
            }

            // ---------------------------------------------------------------
            // Substitui TODOS os placeholders no HTML
            // ---------------------------------------------------------------
            $htmlContent = str_replace(
                [
                    '__META_TITLE__',
                    '__META_DESCRIPTION__',
                    '__OG_TITLE__',
                    '__OG_DESCRIPTION__',
                    '__OG_IMAGE__',
                    '__CANONICAL_URL__',
                ],
                [
                    e($meta['title']),
                    e($meta['description']),
                    e($meta['title']),
                    e($meta['description']),
                    e($meta['og_image']),
                    e($meta['canonical']),
                ],
                $htmlContent
            );

            // Injeta og:url e og:site_name explícitos (relevante para o WhatsApp)
            $ogUrlTag  = '<meta property="og:url" content="' . e($meta['og_url']) . '" />';
            $ogSiteTag = '<meta property="og:site_name" content="Chapada Livre" />';
            $htmlContent = str_replace(
                '<meta property="og:type" content="website" />',
                '<meta property="og:type" content="website" />' . "\n  " . $ogUrlTag . "\n  " . $ogSiteTag,
                $htmlContent
            );

            // Injeção de dados iniciais do anúncio para o React renderizar em 0ms
            if ($post) {
                try {
                    $resource = new \App\Http\Resources\PostResource($post, ['embed' => 'picture,pictures,city,category,user']);
                    $jsonPost = json_encode($resource->resolve());
                    $initialScript = "<script>window.__INITIAL_POST_DATA__ = {$jsonPost};</script>\n";
                    $htmlContent = str_replace('</head>', "  {$initialScript}</head>", $htmlContent);
                } catch (\Throwable $e) {}
            }

            // Injeção de conteúdo estático para SEO (robôs)
            $seoContent  = $this->generateSeoContent($meta, $post);
            $htmlContent = str_replace('<div id="root">', $seoContent . "\n" . '<div id="root">', $htmlContent);

            return $htmlContent;
        });

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=600, s-maxage=600');
    }

    /**
     * Gera o HTML estático otimizado para robôs de busca (SEO) antes do carregamento do React.
     */
    private function generateSeoContent($meta, $post = null)
    {
        $title = e($meta['title'] ?? 'Chapada Livre | Classificados da Chapada Diamantina');
        $desc  = e($meta['description'] ?? 'Encontre carros, imóveis, empregos e serviços em Seabra e região.');

        if (strpos(strtolower($title), 'chapada livre') === false) {
            $title = 'Chapada Livre | ' . $title;
        }

        $html = "\n\t<!-- INÍCIO DO CONTEÚDO DE INDEXAÇÃO DE SEO -->\n";
        $html .= "\t<section style=\"position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;\">\n";

        if ($post) {
            $postTitle = e($post->title);
            $city      = e($post->city->name ?? 'Seabra e Região');
            $price     = e($post->price_formatted ?? 'A combinar');
            $postDesc  = e($post->description);

            $html .= "\t\t<article>\n";
            $html .= "\t\t\t<h1>{$postTitle}</h1>\n";
            $html .= "\t\t\t<h2>Anúncio em {$city} - Chapada Diamantina</h2>\n";
            $html .= "\t\t\t<p><strong>Valor:</strong> {$price}</p>\n";
            $html .= "\t\t\t<div>{$postDesc}</div>\n";
            $html .= "\t\t</article>\n";

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