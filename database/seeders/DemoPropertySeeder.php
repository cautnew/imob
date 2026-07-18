<?php

namespace Database\Seeders;

use App\Enums\PriceFrequency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Company;
use App\Models\Feature;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Provisions the demo company's imóveis: a realistic, varied portfolio
 * spanning every purpose, type, and status, wired up to the feature,
 * attribute, and price type catalogs seeded by DemoCatalogSeeder.
 */
class DemoPropertySeeder extends Seeder
{
    /**
     * @var list<array{
     *     title: string, description: string, type: PropertyType, purpose: PropertyPurpose, status: PropertyStatus,
     *     zip_code: string, street: string, number: string, neighborhood: string, city: string, state: string,
     *     total_area: float, built_area: ?float,
     *     attributes: array<string, mixed>, features: list<string>,
     *     sale_price: ?float, rent_price: ?float, condo_price: ?float, iptu_price: ?float,
     * }>
     */
    private const array PROPERTIES = [
        [
            'title' => 'Apartamento 3 quartos no Itaim Bibi', 'description' => 'Amplo apartamento com varanda gourmet e infraestrutura de lazer completa, a poucos passos da Av. Faria Lima.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::SaleAndRent, 'status' => PropertyStatus::Rented,
            'zip_code' => '04532-060', 'street' => 'Rua Joaquim Floriano', 'number' => '1200', 'neighborhood' => 'Itaim Bibi', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 110, 'built_area' => 95,
            'attributes' => ['Quartos' => 3, 'Banheiros' => 2, 'Vagas de garagem' => 2, 'Suítes' => 1, 'Ano de construção' => 2015, 'Mobiliado' => true, 'Andar' => '6º ao 10º andar', 'Posição solar' => 'Norte'],
            'features' => ['Piscina', 'Portaria 24h', 'Academia', 'Varanda gourmet'],
            'sale_price' => 980000, 'rent_price' => 6800, 'condo_price' => 1350, 'iptu_price' => 260,
        ],
        [
            'title' => 'Cobertura duplex com vista panorâmica', 'description' => 'Cobertura duplex reformada, terraço com piscina privativa e vista aberta para a cidade.',
            'type' => PropertyType::Penthouse, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Reserved,
            'zip_code' => '04077-000', 'street' => 'Av. Ibirapuera', 'number' => '2400', 'neighborhood' => 'Moema', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 220, 'built_area' => 180,
            'attributes' => ['Quartos' => 4, 'Banheiros' => 4, 'Vagas de garagem' => 3, 'Suítes' => 3, 'Ano de construção' => 2018, 'Mobiliado' => true, 'Andar' => 'Acima do 10º andar', 'Posição solar' => 'Norte'],
            'features' => ['Piscina', 'Churrasqueira', 'Salão de festas', 'Academia'],
            'sale_price' => 2850000, 'rent_price' => null, 'condo_price' => 2400, 'iptu_price' => 780,
        ],
        [
            'title' => 'Casa térrea em condomínio fechado', 'description' => 'Casa térrea espaçosa em condomínio de alto padrão, com quintal amplo e área gourmet.',
            'type' => PropertyType::House, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '06709-015', 'street' => 'Estrada da Granja Viana', 'number' => '350', 'neighborhood' => 'Granja Viana', 'city' => 'Cotia', 'state' => 'SP',
            'total_area' => 300, 'built_area' => 220,
            'attributes' => ['Quartos' => 4, 'Banheiros' => 3, 'Vagas de garagem' => 4, 'Suítes' => 2, 'Ano de construção' => 2010, 'Mobiliado' => false, 'Posição solar' => 'Leste'],
            'features' => ['Portaria 24h', 'Quintal', 'Jardim', 'Churrasqueira'],
            'sale_price' => null, 'rent_price' => 7200, 'condo_price' => 950, 'iptu_price' => 320,
        ],
        [
            'title' => 'Studio compacto próximo ao metrô', 'description' => 'Studio moderno e funcional, ideal para investidor, a 5 minutos a pé do metrô Vila Mariana.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '04101-000', 'street' => 'Rua Domingos de Morais', 'number' => '980', 'neighborhood' => 'Vila Mariana', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 35, 'built_area' => 32,
            'attributes' => ['Quartos' => 1, 'Banheiros' => 1, 'Vagas de garagem' => 1, 'Suítes' => 0, 'Ano de construção' => 2020, 'Mobiliado' => true, 'Andar' => 'Térreo', 'Posição solar' => 'Sul'],
            'features' => ['Academia', 'Portaria 24h'],
            'sale_price' => null, 'rent_price' => 2800, 'condo_price' => 560, 'iptu_price' => 95,
        ],
        [
            'title' => 'Apartamento 2 quartos reformado', 'description' => 'Apartamento totalmente reformado, com acabamentos de alto padrão em rua tranquila de Perdizes.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Available,
            'zip_code' => '05009-000', 'street' => 'Rua Cardoso de Almeida', 'number' => '650', 'neighborhood' => 'Perdizes', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 70, 'built_area' => 65,
            'attributes' => ['Quartos' => 2, 'Banheiros' => 2, 'Vagas de garagem' => 1, 'Suítes' => 1, 'Ano de construção' => 2005, 'Mobiliado' => false, 'Andar' => '1º ao 5º andar', 'Posição solar' => 'Oeste'],
            'features' => ['Salão de festas', 'Portaria 24h'],
            'sale_price' => 640000, 'rent_price' => null, 'condo_price' => 720, 'iptu_price' => 190,
        ],
        [
            'title' => 'Sala comercial no centro empresarial', 'description' => 'Sala comercial pronta para uso, em edifício corporativo na região da Faria Lima.',
            'type' => PropertyType::CommercialRoom, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '04538-132', 'street' => 'Av. Brigadeiro Faria Lima', 'number' => '3477', 'neighborhood' => 'Itaim Bibi', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 45, 'built_area' => 45,
            'attributes' => ['Banheiros' => 1, 'Vagas de garagem' => 1, 'Ano de construção' => 2012, 'Andar' => '6º ao 10º andar'],
            'features' => ['Portaria 24h', 'Câmeras de segurança'],
            'sale_price' => null, 'rent_price' => 4500, 'condo_price' => 1100, 'iptu_price' => 210,
        ],
        [
            'title' => 'Terreno em condomínio residencial', 'description' => 'Terreno plano pronto para construir, em condomínio fechado com infraestrutura completa.',
            'type' => PropertyType::Land, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Available,
            'zip_code' => '06709-100', 'street' => 'Alameda dos Ipês', 'number' => '85', 'neighborhood' => 'Granja Viana', 'city' => 'Cotia', 'state' => 'SP',
            'total_area' => 450, 'built_area' => null,
            'attributes' => [],
            'features' => ['Portaria 24h'],
            'sale_price' => 480000, 'rent_price' => null, 'condo_price' => 350, 'iptu_price' => 90,
        ],
        [
            'title' => 'Galpão logístico Zona Leste', 'description' => 'Galpão logístico com pé-direito alto, doca de carga e fácil acesso a rodovias.',
            'type' => PropertyType::Warehouse, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '08260-000', 'street' => 'Av. Jacu-Pêssego', 'number' => '4500', 'neighborhood' => 'Itaquera', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 1200, 'built_area' => 1000,
            'attributes' => ['Vagas de garagem' => 6, 'Ano de construção' => 2016, 'Mobiliado' => false],
            'features' => ['Câmeras de segurança', 'Alarme monitorado'],
            'sale_price' => null, 'rent_price' => 18000, 'condo_price' => null, 'iptu_price' => 1500,
        ],
        [
            'title' => 'Chácara com área de lazer completa', 'description' => 'Chácara com casa principal, piscina, área de churrasco e amplo espaço verde.',
            'type' => PropertyType::Farm, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Available,
            'zip_code' => '18150-000', 'street' => 'Estrada Municipal do Ribeirão', 'number' => 's/n', 'neighborhood' => 'Zona Rural', 'city' => 'Ibiúna', 'state' => 'SP',
            'total_area' => 5000, 'built_area' => 280,
            'attributes' => ['Quartos' => 5, 'Banheiros' => 4, 'Vagas de garagem' => 6, 'Suítes' => 2, 'Ano de construção' => 2008, 'Mobiliado' => true],
            'features' => ['Piscina', 'Churrasqueira', 'Jardim', 'Quintal'],
            'sale_price' => 1650000, 'rent_price' => null, 'condo_price' => null, 'iptu_price' => 420,
        ],
        [
            'title' => 'Loja de rua movimentada', 'description' => 'Loja térrea de esquina, com grande fluxo de pedestres, ideal para comércio varejista.',
            'type' => PropertyType::Store, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '03310-000', 'street' => 'Rua Tuiuti', 'number' => '1500', 'neighborhood' => 'Tatuapé', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 90, 'built_area' => 90,
            'attributes' => ['Ano de construção' => 2014, 'Mobiliado' => false],
            'features' => ['Câmeras de segurança'],
            'sale_price' => null, 'rent_price' => 5200, 'condo_price' => null, 'iptu_price' => 340,
        ],
        [
            'title' => 'Apartamento garden com quintal privativo', 'description' => 'Apartamento garden com quintal privativo e área verde, em condomínio com lazer completo.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::SaleAndRent, 'status' => PropertyStatus::Available,
            'zip_code' => '04571-000', 'street' => 'Rua Verbo Divino', 'number' => '1400', 'neighborhood' => 'Brooklin', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 95, 'built_area' => 85,
            'attributes' => ['Quartos' => 3, 'Banheiros' => 2, 'Vagas de garagem' => 2, 'Suítes' => 1, 'Ano de construção' => 2019, 'Mobiliado' => false, 'Andar' => 'Térreo', 'Posição solar' => 'Sul'],
            'features' => ['Piscina', 'Playground', 'Portaria 24h', 'Jardim'],
            'sale_price' => 890000, 'rent_price' => 5900, 'condo_price' => 1050, 'iptu_price' => 240,
        ],
        [
            'title' => 'Casa geminada em bairro familiar', 'description' => 'Casa geminada em bairro tranquilo e familiar, próxima a escolas e comércio local.',
            'type' => PropertyType::House, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Available,
            'zip_code' => '13025-000', 'street' => 'Rua Coronel Quirino', 'number' => '740', 'neighborhood' => 'Cambuí', 'city' => 'Campinas', 'state' => 'SP',
            'total_area' => 180, 'built_area' => 140,
            'attributes' => ['Quartos' => 3, 'Banheiros' => 2, 'Vagas de garagem' => 2, 'Suítes' => 1, 'Ano de construção' => 2012, 'Mobiliado' => false, 'Posição solar' => 'Leste'],
            'features' => ['Quintal', 'Churrasqueira'],
            'sale_price' => 720000, 'rent_price' => null, 'condo_price' => null, 'iptu_price' => 280,
        ],
        [
            'title' => 'Apartamento de 1 quarto para investidor', 'description' => 'Apartamento compacto e mobiliado, ótimo para locação de curta ou longa duração.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::Rent, 'status' => PropertyStatus::Rented,
            'zip_code' => '05433-000', 'street' => 'Rua Harmonia', 'number' => '200', 'neighborhood' => 'Vila Madalena', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 42, 'built_area' => 38,
            'attributes' => ['Quartos' => 1, 'Banheiros' => 1, 'Vagas de garagem' => 1, 'Suítes' => 0, 'Ano de construção' => 2021, 'Mobiliado' => true, 'Andar' => '1º ao 5º andar', 'Posição solar' => 'Oeste'],
            'features' => ['Academia', 'Portaria 24h', 'Ar-condicionado'],
            'sale_price' => null, 'rent_price' => 3200, 'condo_price' => 480, 'iptu_price' => 100,
        ],
        [
            'title' => 'Sobrado com escritório', 'description' => 'Sobrado amplo com cômodo extra que pode ser usado como escritório ou home office.',
            'type' => PropertyType::House, 'purpose' => PropertyPurpose::SaleAndRent, 'status' => PropertyStatus::Rented,
            'zip_code' => '02403-000', 'street' => 'Rua Voluntários da Pátria', 'number' => '3200', 'neighborhood' => 'Santana', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 210, 'built_area' => 175,
            'attributes' => ['Quartos' => 4, 'Banheiros' => 3, 'Vagas de garagem' => 3, 'Suítes' => 1, 'Ano de construção' => 2009, 'Mobiliado' => false, 'Posição solar' => 'Norte'],
            'features' => ['Quintal', 'Câmeras de segurança'],
            'sale_price' => 980000, 'rent_price' => 6200, 'condo_price' => null, 'iptu_price' => 310,
        ],
        [
            'title' => 'Cobertura de frente para o mar', 'description' => 'Cobertura com vista frontal para o mar, terraço amplo e acabamento de altíssimo padrão.',
            'type' => PropertyType::Penthouse, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Available,
            'zip_code' => '11410-000', 'street' => 'Av. Miguel Estéfno', 'number' => '900', 'neighborhood' => 'Pitangueiras', 'city' => 'Guarujá', 'state' => 'SP',
            'total_area' => 250, 'built_area' => 200,
            'attributes' => ['Quartos' => 4, 'Banheiros' => 4, 'Vagas de garagem' => 3, 'Suítes' => 4, 'Ano de construção' => 2017, 'Mobiliado' => true, 'Andar' => 'Acima do 10º andar', 'Posição solar' => 'Norte'],
            'features' => ['Piscina', 'Academia', 'Salão de festas', 'Varanda gourmet'],
            'sale_price' => 3200000, 'rent_price' => null, 'condo_price' => 2600, 'iptu_price' => 850,
        ],
        [
            'title' => 'Apartamento compacto quitado', 'description' => 'Apartamento de fácil manutenção, pronto para morar, em condomínio simples e bem localizado.',
            'type' => PropertyType::Apartment, 'purpose' => PropertyPurpose::Sale, 'status' => PropertyStatus::Sold,
            'zip_code' => '02305-000', 'street' => 'Rua Alberto Torres', 'number' => '450', 'neighborhood' => 'Tucuruvi', 'city' => 'São Paulo', 'state' => 'SP',
            'total_area' => 55, 'built_area' => 50,
            'attributes' => ['Quartos' => 2, 'Banheiros' => 1, 'Vagas de garagem' => 1, 'Suítes' => 0, 'Ano de construção' => 2003, 'Mobiliado' => false, 'Andar' => '1º ao 5º andar', 'Posição solar' => 'Sul'],
            'features' => ['Portaria 24h'],
            'sale_price' => 380000, 'rent_price' => null, 'condo_price' => 420, 'iptu_price' => 110,
        ],
    ];

    public function run(): void
    {
        $company = Company::where('slug', DemoCompanySeeder::COMPANY_SLUG)->firstOrFail();

        $features = Feature::query()->where('company_id', $company->id)->get()->keyBy('name');
        $attributes = PropertyAttribute::query()->where('company_id', $company->id)->with('options')->get()->keyBy('name');
        $priceTypes = PriceType::query()->where('company_id', $company->id)->get()->keyBy('name');
        $owners = $company->owners()->orderBy('id')->get();

        foreach (self::PROPERTIES as $index => $spec) {
            $property = $company->properties()->create([
                'title' => $spec['title'],
                'description' => $spec['description'],
                'purpose' => $spec['purpose'],
                'type' => $spec['type'],
                'status' => $spec['status'],
                'is_public' => true,
                'zip_code' => $spec['zip_code'],
                'street' => $spec['street'],
                'number' => $spec['number'],
                'neighborhood' => $spec['neighborhood'],
                'city' => $spec['city'],
                'state' => $spec['state'],
                'total_area' => $spec['total_area'],
                'built_area' => $spec['built_area'],
            ]);

            $property->features()->attach(
                collect($spec['features'])->map(fn (string $name) => $features[$name]->id)
            );

            $this->attachAttributeValues($property, $attributes, $spec['attributes']);
            $this->attachPrices($property, $priceTypes, $spec);

            $owner = $owners[$index % $owners->count()];
            $property->owners()->attach($owner->id);
        }
    }

    /**
     * @param  Collection<string, PropertyAttribute>  $attributes
     * @param  array<string, mixed>  $values
     */
    private function attachAttributeValues(Property $property, Collection $attributes, array $values): void
    {
        foreach ($values as $attributeName => $value) {
            $attribute = $attributes[$attributeName];

            if ($attribute->type->hasOptions()) {
                $option = $attribute->options->firstWhere('value', $value);

                $property->attributeValues()->create([
                    'property_attribute_id' => $attribute->id,
                    'property_attribute_option_id' => $option->id,
                ]);

                continue;
            }

            $property->attributeValues()->create([
                'property_attribute_id' => $attribute->id,
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
            ]);
        }
    }

    /**
     * @param  Collection<string, PriceType>  $priceTypes
     * @param  array{sale_price: ?float, rent_price: ?float, condo_price: ?float, iptu_price: ?float}  $spec
     */
    private function attachPrices(Property $property, Collection $priceTypes, array $spec): void
    {
        $prices = [
            'Venda' => ['amount' => $spec['sale_price'], 'frequency' => PriceFrequency::OneTime],
            'Aluguel' => ['amount' => $spec['rent_price'], 'frequency' => PriceFrequency::Monthly],
            'Condomínio' => ['amount' => $spec['condo_price'], 'frequency' => PriceFrequency::Monthly],
            'IPTU' => ['amount' => $spec['iptu_price'], 'frequency' => PriceFrequency::Annual],
        ];

        foreach ($prices as $priceTypeName => $price) {
            if ($price['amount'] === null) {
                continue;
            }

            $property->prices()->create([
                'price_type_id' => $priceTypes[$priceTypeName]->id,
                'amount' => $price['amount'],
                'frequency' => $price['frequency'],
            ]);
        }
    }
}
