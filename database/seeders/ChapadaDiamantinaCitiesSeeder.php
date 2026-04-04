<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChapadaDiamantinaCitiesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// 1. Desativar todas as cidades direto via DB para evitar disparar observers
		DB::table('cities')->update(['active' => 0]);
		
		// 2. Lista das 24 cidades da Chapada Diamantina (Território de Identidade)
		$cities = [
			'Abaíra',
			'Andaraí',
			'Barra da Estiva',
			'Boninal',
			'Bonito',
			'Ibicoara',
			'Ibitiara',
			'Iramaia',
			'Iraquara',
			'Itaetê',
			'Jussiape',
			'Lençóis',
			'Marcionílio Souza',
			'Morro do Chapéu',
			'Mucugê',
			'Nova Redenção',
			'Novo Horizonte',
			'Palmeiras',
			'Piatã',
			'Rio de Contas',
			'Seabra',
			'Souto Soares',
			'Utinga',
			'Wagner',
		];
		
		$subadmin1Code = 'BR.05'; // Bahia
		$countryCode = 'BR';
		
		foreach ($cities as $cityName) {
			// Normalização simples para busca flexível
			$cityNameNoAccent = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($cityName, ENT_QUOTES, 'UTF-8'));
			
			// Tenta encontrar a cidade na Bahia usando várias estratégias
			$city = City::withoutGlobalScopes()
				->where('subadmin1_code', $subadmin1Code)
				->where(function ($query) use ($cityName, $cityNameNoAccent) {
					$query->where('name', 'like', '%' . $cityName . '%')
						  ->orWhere('name', 'like', '%' . $cityNameNoAccent . '%')
						  ->orWhere('name', 'like', '%"en":"' . $cityName . '"%')
						  ->orWhere('name', 'like', '%"en":"' . $cityNameNoAccent . '"%')
						  ->orWhere('name', 'like', '%"pt":"' . $cityName . '"%')
						  ->orWhere('name', 'like', '%"pt":"' . $cityNameNoAccent . '"%');
				})
				->first();
			
			if ($city) {
				// Salvamos como JSON (ex: {"pt":"Seabra"}) para ser compatível com as outras cidades e o sistema de tradução
				$jsonName = json_encode(['pt' => $cityName], JSON_UNESCAPED_UNICODE);
				
				DB::table('cities')->where('id', $city->id)->update([
					'name'   => $jsonName,
					'active' => 1
				]);
				$this->command->info("Cidade ativada: {$cityName} (ID: {$city->id})");
			} else {
				$this->command->warn("Cidade não encontrada no banco de dados, criando nova: {$cityName}");
				
				$jsonName = json_encode(['pt' => $cityName], JSON_UNESCAPED_UNICODE);
				
				DB::table('cities')->insert([
					'country_code'   => $countryCode,
					'name'           => $jsonName,
					'subadmin1_code' => $subadmin1Code,
					'active'         => 1,
					'latitude'       => -12.5273, 
					'longitude'      => -41.3888,
					'time_zone'      => 'America/Bahia',
					'created_at'     => now(),
					'updated_at'     => now(),
				]);
			}
		}
		
		$activeCount = City::withoutGlobalScopes()->where('active', 1)->count();
		$this->command->info("Total de cidades ativas: {$activeCount}");

		// 3. ATUALIZAÇÃO CRÍTICA: Corrigir o cache da seção de locais na tabela 'sections'
		$sectionId = 4; // ID da seção 'locations' (getLocations)
		$section = DB::table('sections')->where('id', $sectionId)->first();
		
		if ($section) {
			$data = !empty($section->field_values) ? json_decode($section->field_values, true) : [];
			
			// Pegar apenas as 24 cidades que ativamos acima
			$activeCities = DB::table('cities')
				->where('active', 1)
				->orderBy('name')
				->get();
				
			$citiesForSection = [];
			foreach ($activeCities as $city) {
				// Aqui garantimos que enviamos os dados EXATAMENTE como a view espera
				// sem passar pelo Model que forçaria o array de tradução
				$citiesForSection[] = [
					'id'             => $city->id,
					'country_code'   => $city->country_code,
					'name'           => $city->name, // Explicitamente uma STRING
					'longitude'      => $city->longitude,
					'latitude'       => $city->latitude,
					'subadmin1_code' => $city->subadmin1_code,
					'active'         => 1,
					'slug'           => slugify($city->name)
				];
			}
			
			$data['cities'] = $citiesForSection;
			
			DB::table('sections')->where('id', $sectionId)->update([
				'field_values' => json_encode($data)
			]);
			
			$this->command->info("Cache da seção 'locations' (field_values) atualizado com sucesso com nomes em formato string.");
		}
	}
}
