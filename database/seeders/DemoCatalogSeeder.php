<?php

namespace Database\Seeders;

use App\Enums\PropertyAttributeType;
use App\Enums\PropertyPurpose;
use App\Models\Company;
use Illuminate\Database\Seeder;

/**
 * Provisions the demo company's characteristics (features), property
 * attributes, and price type catalogs.
 */
class DemoCatalogSeeder extends Seeder
{
    /**
     * Feature categories and the características within each one.
     *
     * @var array<string, list<string>>
     */
    public const array FEATURE_CATEGORIES = [
        'Lazer' => ['Piscina', 'Churrasqueira', 'Salão de festas', 'Playground', 'Academia'],
        'Segurança' => ['Portaria 24h', 'Câmeras de segurança', 'Alarme monitorado', 'Cerca elétrica'],
        'Comodidades' => ['Ar-condicionado', 'Mobiliado', 'Aquecimento a gás', 'Armários planejados'],
        'Área externa' => ['Varanda gourmet', 'Quintal', 'Vaga coberta', 'Jardim'],
    ];

    public function run(): void
    {
        $company = Company::where('slug', DemoCompanySeeder::COMPANY_SLUG)->firstOrFail();

        foreach (self::FEATURE_CATEGORIES as $categoryName => $features) {
            $category = $company->featureCategories()->create(['name' => $categoryName]);

            foreach ($features as $feature) {
                $category->features()->create([
                    'company_id' => $company->id,
                    'name' => $feature,
                ]);
            }
        }

        $company->propertyAttributes()->create([
            'name' => 'Quartos', 'type' => PropertyAttributeType::Integer,
            'filterable' => true, 'comparable' => true, 'required' => true,
        ]);
        $company->propertyAttributes()->create([
            'name' => 'Banheiros', 'type' => PropertyAttributeType::Integer,
            'filterable' => true, 'comparable' => true,
        ]);
        $company->propertyAttributes()->create([
            'name' => 'Vagas de garagem', 'type' => PropertyAttributeType::Integer,
            'filterable' => true, 'comparable' => true,
        ]);
        $company->propertyAttributes()->create([
            'name' => 'Suítes', 'type' => PropertyAttributeType::Integer,
            'comparable' => true,
        ]);
        $company->propertyAttributes()->create([
            'name' => 'Ano de construção', 'type' => PropertyAttributeType::Integer,
        ]);
        $company->propertyAttributes()->create([
            'name' => 'Mobiliado', 'type' => PropertyAttributeType::Boolean,
            'filterable' => true,
        ]);

        $andar = $company->propertyAttributes()->create([
            'name' => 'Andar', 'type' => PropertyAttributeType::Select,
            'filterable' => true,
        ]);
        foreach (['Térreo', '1º ao 5º andar', '6º ao 10º andar', 'Acima do 10º andar'] as $order => $value) {
            $andar->options()->create(['value' => $value, 'order' => $order]);
        }

        $posicao = $company->propertyAttributes()->create([
            'name' => 'Posição solar', 'type' => PropertyAttributeType::Select,
        ]);
        foreach (['Norte', 'Sul', 'Leste', 'Oeste'] as $order => $value) {
            $posicao->options()->create(['value' => $value, 'order' => $order]);
        }

        $company->priceTypes()->create(['name' => 'Venda', 'purpose' => PropertyPurpose::Sale, 'comparable' => true]);
        $company->priceTypes()->create(['name' => 'Aluguel', 'purpose' => PropertyPurpose::Rent, 'comparable' => true]);
        $company->priceTypes()->create(['name' => 'Condomínio', 'comparable' => false]);
        $company->priceTypes()->create(['name' => 'IPTU', 'comparable' => false]);
    }
}
