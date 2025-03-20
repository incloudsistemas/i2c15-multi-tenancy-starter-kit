<?php

namespace App\Filament\Pages\System;

use App\Enums\ProfileInfos\UfEnum;
use App\Models\System\User;
use App\Services\Polymorphics\AddressService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Contracts\Database\Eloquent\Builder;

class RegisterTenantAccount extends RegisterTenant
{
    protected ?bool $hasDatabaseTransactions = true;

    public static function getLabel(): string
    {
        return 'Cadastrar Conta';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make(__('Infos. Gerais'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Nome da conta'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('categories')
                            ->label(__('Categoria(s)'))
                            ->relationship(
                                name: 'categories',
                                titleAttribute: 'name',
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cnpj')
                            ->label(__('CNPJ'))
                            ->mask('99.999.999/9999-99')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('domain')
                            ->label(__('Domínio'))
                            ->helperText('Ex: seudominio.com.br')
                            // ->url()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Endereço'))
                    ->schema([
                        Forms\Components\TextInput::make('address.zipcode')
                            ->label(__('CEP'))
                            ->mask('99999-999')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function (AddressService $service, ?string $state, ?string $old, callable $set): void {
                                    if ($old === $state) {
                                        return;
                                    }

                                    $address = $service->getAddressByZipcodeBrasilApi(zipcode: $state);

                                    if (isset($address['error'])) {
                                        $set('address.uf', null);
                                        $set('address.city', null);
                                        $set('address.district', null);
                                        $set('address.address_line', null);
                                        $set('address.complement', null);
                                    } else {
                                        $set('address.uf', $address['state']);
                                        $set('address.city', $address['city']);
                                        $set('address.district', $address['neighborhood']);
                                        $set('address.address_line', $address['street']);
                                        // $set('address.complement', null);
                                    }
                                }
                            )
                            ->columnSpanFull(),
                        Forms\Components\Select::make('address.uf')
                            ->label(__('Estado'))
                            ->options(UfEnum::class)
                            ->placeholder(__('Informe primeiramente o CEP'))
                            ->selectablePlaceholder(false)
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.city')
                            ->label(__('Cidade'))
                            ->placeholder(__('Informe primeiramente o CEP'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('address.uf')
                            ->label(__('Estado'))
                            ->options(UfEnum::class)
                            ->selectablePlaceholder(false)
                            ->searchable()
                            ->native(false)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.city')
                            ->label(__('Cidade'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.district')
                            ->label(__('Bairro'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.address_line')
                            ->label(__('Endereço'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.number')
                            ->label(__('Número'))
                            // ->minLength(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address.complement')
                            ->label(__('Complemento'))
                            ->helperText(__('Apto / Bloco / Casa'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        // Forms\Components\TextInput::make('address.reference')
                        //     ->label(__('Ponto de referência'))
                        //     ->maxLength(255)
                        //     ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function afterRegister(): void
    {
        $this->attachUser();
        $this->createAddress();
    }

    protected function attachUser(): void
    {
        $user = auth()->user();

        // Owner
        $this->tenant->owner()
            ->associate($user)
            ->save();

        // 2 - Cliente, 3 - Admin
        if (!$user->roles()->whereIn('id', [2, 3])->exists()) {
            $user->roles()
                ->syncWithoutDetaching([2, 3]);
        }

        $superadminUsers = User::whereHas('roles', function (Builder $query): Builder {
            return $query->where('id', 1); // 1 - Superadmin
        })
            ->pluck('id')
            ->toArray();

        $users = array_unique(array_merge($superadminUsers, [$user->id]));

        $this->tenant->users()
            ->attach($users);
    }

    protected function createAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->tenant->address()
            ->create($this->data['address']);
    }
}
