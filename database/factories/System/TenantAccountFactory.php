<?php

namespace Database\Factories\System;

use App\Models\Polymorphics\Address;
use App\Models\System\TenantAccount;
use App\Models\System\TenantCategory;
use App\Models\System\TenantPlan;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\System\TenantAccount>
 */
class TenantAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        $owner = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Cliente', 'Administrador']);
        })
            ->inRandomOrder()
            ->first();

        return [
            'plan_id'       => TenantPlan::inRandomOrder()->value('id') ?? TenantPlan::factory(),
            'user_id'       => $owner?->id ?? User::factory(),
            'role'          => $this->faker->randomElement(['1']),
            'name'          => $name,
            'slug'          => Str::slug($name . '-' . uniqid()),
            'cnpj'          => $this->faker->unique()->numerify('##.###.###/####-##'),
            'domain'        => $this->faker->unique()->domainName(),
            'emails'        => [
                [
                    'email' => $this->faker->unique()->safeEmail(),
                    'name'  => $this->faker->randomElement(['Pessoal', 'Trabalho', 'Outros']),
                ],
                [
                    'email' => $this->faker->unique()->safeEmail(),
                    'name'  => $this->faker->randomElement(['Pessoal', 'Trabalho', 'Outros']),
                ],
            ],
            'phones'        => [
                [
                    'number' => $this->faker->phoneNumber(),
                    'name'   => $this->faker->randomElement(['Celular', 'Whatsapp', 'Casa', 'Trabalho', 'Outros']),
                ],
                [
                    'number' => $this->faker->phoneNumber(),
                    'name'   => $this->faker->randomElement(['Celular', 'Whatsapp', 'Casa', 'Trabalho', 'Outros']),
                ],
            ],
            'complement'    => $this->faker->sentence(),
            'social_media'  => [
                [
                    'role' => $this->faker->randomElement(['Instagram', 'Facebook']),
                    'url'  => $this->faker->url(),
                ],
                [
                    'role' => $this->faker->randomElement(['Twitter', 'LinkedIn']),
                    'url'  => $this->faker->url(),
                ]
            ],
            'opening_hours' => ["Seg - Sex: 08h - 17h", "Sáb: 08h - 12h"],
            'theme'         => [
                'primary_color'    => $this->faker->hexColor(),
                'secondary_color'  => $this->faker->optional()->hexColor(),
                'background_color' => $this->faker->hexColor(),
            ],
            'status'        => $this->faker->randomElement([0, 1]),
            'settings'      => null,
            'custom'        => null,
        ];
    }

    /**
     * After creating a TenantAccount, automatically:
     * - Create an Address
     * - Associate one or more existing Categories
     */
    public function configure()
    {
        return $this->afterCreating(function (TenantAccount $tenant) {
            // Create an Address related to the Tenant
            Address::factory()->create([
                'addressable_id'   => $tenant->id,
                'addressable_type' => MorphMapByClass(model: TenantAccount::class),
            ]);

            // Attach one or more existing Categories to the Tenant
            $categories = TenantCategory::inRandomOrder()
                ->limit(rand(1, 3))
                ->pluck('id');

            $tenant->categories()
                ->attach($categories);

            // Attach all Superadmins to the Tenant
            $superadmins = User::whereHas('roles', function ($query) {
                $query->where('name', 'Superadministrador');
            })
                ->pluck('id')
                ->toArray();

            $usersToAttach = array_unique(array_merge($superadmins, [$tenant->user_id]));

            $tenant->users()
                ->attach($usersToAttach);
        });
    }
}
