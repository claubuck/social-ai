<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ContentTopic;
use Illuminate\Database\Seeder;

class ContentTopicSeeder extends Seeder
{
    /**
     * Temas por defecto para Flujo 1 n8n (generar contenido con IA).
     */
    public function run(): void
    {
        $defaults = [
            'tips de marketing',
            'promociones restaurante',
            'producto destacado',
        ];

        Company::query()->each(function (Company $company) use ($defaults): void {
            foreach ($defaults as $i => $topic) {
                ContentTopic::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'topic' => $topic,
                    ],
                    ['sort_order' => $i]
                );
            }
        });
    }
}
