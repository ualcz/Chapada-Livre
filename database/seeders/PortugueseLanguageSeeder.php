<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortugueseLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Reset all languages to non-default and inactive
        DB::table('languages')->update([
            'default' => 0,
            'active'  => 0,
        ]);

        // 2. Ensure Portuguese (pt) exists and set as default/active
        // Note: Using Brazilian flag for Chapada-Livre
        $pt = Language::withoutGlobalScopes()->where('code', 'pt')->first();

        if ($pt) {
            $pt->active = 1;
            $pt->default = 1;
            $pt->name = 'Português';
            $pt->native = 'Português';
            $pt->flag = 'flag-icon-br';
            $pt->save();
        } else {
            Language::create([
                'code'                  => 'pt',
                'locale'                => 'pt_BR',
                'name'                  => 'Português',
                'native'                => 'Português',
                'flag'                  => 'flag-icon-br',
                'script'                => 'Latn',
                'direction'             => 'ltr',
                'active'                => 1,
                'default'               => 1,
                'russian_pluralization' => '0',
                'date_format'           => 'D [de] MMMM [de] YYYY',
                'datetime_format'       => 'D [de] MMMM [de] YYYY HH:mm',
            ]);
        }

        $this->command->info('O idioma Português (Brasil) foi definido como padrão.');

        // 3. Fix Menu Items missing PT translations (especially the User Dropdown)
        $this->command->info('Corrigindo traduções de itens de menu...');
        try {
            $menuItems = \DB::table('menu_items')->get();
            foreach ($menuItems as $item) {
                $label = json_decode($item->label, true);
                if (!is_array($label)) continue;

                if (!isset($label['pt']) || empty($label['pt'])) {
                    // Use English as fallback for placeholders like {user.name}
                    // or other fields that might be empty in PT
                    if (isset($label['en'])) {
                        $label['pt'] = $label['en'];
                        \DB::table('menu_items')->where('id', $item->id)->update([
                            'label' => json_encode($label)
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->command->warn('Erro ao atualizar itens de menu: ' . $e->getMessage());
        }
        $this->command->info('Corrigindo a tradução das categorias...');
        try {
            $categories = \DB::table('categories')->get();
            foreach ($categories as $category) {
                $name = json_decode($category->name, true);
                if (!is_array($name)) continue;

                if (!isset($name['pt']) || empty($name['pt'])) {
                    // Use English as fallback for placeholders like {user.name}
                    // or other fields that might be empty in PT
                    if (isset($name['en'])) {
                        $name['pt'] = $name['en'];
                        \DB::table('categories')->where('id', $category->id)->update([
                            'name' => json_encode($name)
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->command->warn('Erro ao atualizar categorias: ' . $e->getMessage());
        }
        $this->command->info('Traduções concluídas.');
    }
}
