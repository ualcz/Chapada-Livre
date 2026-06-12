<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;
use App\Models\NativeSponsor;
use App\Models\Category;

class NativeSponsorController extends PanelController
{
    public function setup()
    {
        $this->xPanel->setModel(NativeSponsor::class);
        $this->xPanel->setRoute(urlGen()->adminUri('native-sponsors'));
        $this->xPanel->setEntityNameStrings('Patrocinador Nativo', 'Patrocinadores Nativos');

        // COLUMNS
        if ($this->onIndexPage) {
            $this->xPanel->addColumn([
                'name'  => 'id',
                'label' => "ID",
            ]);
            $this->xPanel->addColumn([
                'name'  => 'title',
                'label' => "Anunciante",
            ]);
            $this->xPanel->addColumn([
                'name'          => 'carousel_image_path',
                'label'         => "Carrossel (Home)",
                'type'          => 'model_function',
                'function_name' => 'getCarouselImageHtml',
            ]);
            $this->xPanel->addColumn([
                'name'          => 'sidebar_image_path',
                'label'         => "Lateral (Sidebar)",
                'type'          => 'model_function',
                'function_name' => 'getSidebarImageHtml',
            ]);
            $this->xPanel->addColumn([
                'name'          => 'card_image_path',
                'label'         => "Card (Listas)",
                'type'          => 'model_function',
                'function_name' => 'getCardImageHtml',
            ]);
            $this->xPanel->addColumn([
                'name'  => 'frequency',
                'label' => "Frequência",
            ]);
            $this->xPanel->addColumn([
                'name'          => 'active',
                'label'         => trans('admin.Active'),
                'type'          => 'model_function',
                'function_name' => 'crudActiveColumn',
            ]);
        }

        // FIELDS
        if ($this->onCreatePage || $this->onEditPage) {
            $this->xPanel->addField([
                'name'  => 'title',
                'label' => 'Nome do Anunciante',
                'type'  => 'text',
                'attributes' => [
                    'placeholder' => 'Ex: Supermercado Local',
                ],
                'wrapper' => ['class' => 'col-md-6'],
            ]);

            $this->xPanel->addField([
                'name'  => 'link',
                'label' => 'URL de Destino',
                'type'  => 'text',
                'attributes' => [
                    'placeholder' => 'https://...',
                ],
                'wrapper' => ['class' => 'col-md-6'],
            ]);

            $this->xPanel->addField([
                'name'   => 'carousel_image_path',
                'label'  => 'Imagem para Carrossel (Horizontal)',
                'type'   => 'image',
                'upload' => true,
                'disk'   => config('filesystems.default', 'public'),
                'hint'   => 'Proporção ideal ~32:7 (ex: 1280×280px ou 1920×420px). Ativa o anúncio de topo e no Carrossel se enviado.',
                'wrapper' => ['class' => 'col-md-4'],
            ]);

            $this->xPanel->addField([
                'name'   => 'sidebar_image_path',
                'label'  => 'Imagem para Detalhes (Sidebar)',
                'type'   => 'image',
                'upload' => true,
                'disk'   => config('filesystems.default', 'public'),
                'hint'   => 'Proporção 3:4 vertical (ex: 600×800px) ou 6:5 quadrada (ex: 600×500px). Ativa na barra lateral se enviado.',
                'wrapper' => ['class' => 'col-md-4'],
            ]);

            $this->xPanel->addField([
                'name'   => 'card_image_path',
                'label'  => 'Imagem para Card (Listas)',
                'type'   => 'image',
                'upload' => true,
                'disk'   => config('filesystems.default', 'public'),
                'hint'   => 'Proporção exata 4:3 (ex: 800×600px ou 1024×768px). Ativa o anúncio intercalado na listagem de buscas se enviado.',
                'wrapper' => ['class' => 'col-md-4'],
            ]);

            $this->xPanel->addField([
                'name'  => 'frequency',
                'label' => 'Frequência de Exibição (1 a 10)',
                'type'  => 'number',
                'default' => 5,
                'attributes' => [
                    'min' => 1,
                    'max' => 10,
                ],
                'hint'    => 'Peso no sorteio. 10 aparece muito mais do que 1.',
                'wrapper' => ['class' => 'col-md-4'],
            ]);
            
            $this->xPanel->addField([
                'name'        => 'category_id',
                'label'       => 'Categoria Específica (Opcional)',
                'type'        => 'select2_from_array',
                'options'     => $this->getActiveCategoriesOptions(),
                'allows_null' => true,
                'wrapper'     => ['class' => 'col-md-4'],
                'hint'        => 'Deixe em branco para exibir em todo o site. Subcategorias herdam anúncios da categoria pai.',
            ]);
            
            $this->xPanel->addField([
                'name'    => 'expires_at',
                'label'   => 'Data de Validade (Expiração)',
                'type'    => 'date',
                'wrapper' => ['class' => 'col-md-4'],
                'hint'    => 'Opcional. Após esta data, o anúncio para de aparecer.',
            ]);

            $defaultActiveValue = $this->onCreatePage ? '1' : '0';
            $this->xPanel->addField([
                'name'    => 'active',
                'label'   => trans('admin.Active'),
                'type'    => 'checkbox_switch',
                'default' => $defaultActiveValue,
            ]);
        }
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud($request);
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud($request);
    }

    /**
     * Retorna as categorias ativas do sistema organizadas hierarquicamente
     * para uso no select2 do formulário de patrocinadores.
     */
    private function getActiveCategoriesOptions(): array
    {
        $options = [];

        // Busca apenas categorias pai ativas (sem parent_id)
        $parents = Category::where('active', 1)
            ->whereNull('parent_id')
            ->orderBy('lft')
            ->get();

        foreach ($parents as $parent) {
            $options[$parent->id] = $parent->name;

            // Subcategorias ativas desta categoria pai
            $children = Category::where('active', 1)
                ->where('parent_id', $parent->id)
                ->orderBy('lft')
                ->get();

            foreach ($children as $child) {
                $options[$child->id] = '→ ' . $child->name;
            }
        }

        return $options;
    }
}
