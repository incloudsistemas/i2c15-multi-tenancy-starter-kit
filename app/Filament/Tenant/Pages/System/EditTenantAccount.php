<?php

namespace App\Filament\Tenant\Pages\System;

use App\Enums\ProfileInfos\SocialMediaEnum;
use App\Enums\ProfileInfos\UfEnum;
use App\Services\Polymorphics\AddressService;
use App\Services\System\TenantCategoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Support\Str;
use Filament\Support;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditTenantAccount extends EditTenantProfile
{
    protected ?bool $hasDatabaseTransactions = true;

    public static function getLabel(): string
    {
        return 'Perfil da Conta';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Infos. Gerais'))
                            ->schema([
                                static::getGeneralInfosFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Infos. Complementares e Endereço'))
                            ->schema([
                                static::getAdditionalInfosFormSection(),
                                static::getAddressFormSection(),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre a conta.'))
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome da conta'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(
                        function (callable $set, ?string $state): void {
                            $set('slug', Str::slug($state));
                        }
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->helperText(__('O "slug" é a versão do nome amigável para URL. Geralmente é todo em letras minúsculas e contém apenas letras, números e hifens.'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
                Forms\Components\Select::make('categories')
                    ->label(__('Categoria(s)'))
                    ->relationship(
                        name: 'categories',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(TenantCategoryService $service, Builder $query): Builder =>
                        $service->getQueryByTenantCategories(query: $query)
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    // ->when(
                    //     auth()->user()->can('Cadastrar Categorias de Contas'),
                    //     fn(Forms\Components\Select $component): Forms\Components\Select =>
                    //     $component->suffixAction(
                    //         fn(TenantCategoryService $service): Forms\Components\Actions\Action =>
                    //         $service->getQuickCreateActionByTenantCategories(field: 'categories', multiple: true),
                    //     ),
                    // )
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('Logo/Avatar'))
                    ->helperText(__('Tipos de arquivo permitidos: .png, .jpg, .jpeg, .gif. // 500x500px // máx. 5 mb.'))
                    ->collection('avatar')
                    ->image()
                    ->avatar()
                    ->downloadable()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        // '16:9', // ex: 1920x1080px
                        // '4:3',  // ex: 1024x768px
                        '1:1',  // ex: 500x500px
                    ])
                    ->circleCropper()
                    ->imageResizeTargetWidth(500)
                    ->imageResizeTargetHeight(500)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])
                    ->maxSize(5120)
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->guessExtension())
                            ->prepend(Str::slug($get('name'))),
                    ),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAdditionalInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Complementares'))
            ->description(__('Forneça informações adicionais relevantes.'))
            ->schema([
                Forms\Components\TextInput::make('cnpj')
                    ->label(__('CNPJ'))
                    ->mask('99.999.999/9999-99')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('domain')
                    ->label(__('Domínio'))
                    ->helperText('Ex: seudominio.com.br')
                    // ->url()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Repeater::make('emails')
                    ->label(__('Email(s)'))
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            // ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de email'))
                            ->helperText(__('Nome identificador. Ex: Pessoal, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Pessoal',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['email'] ?? null
                    )
                    ->addActionLabel(__('Adicionar email'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Repeater::make('phones')
                    ->label(__('Telefone(s) de contato'))
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label(__('Nº do telefone'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $input.length === 14 ? '(99) 9999-9999' : '(99) 99999-9999'
                                JS)
                            )
                            // ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de contato'))
                            ->helperText(__('Nome identificador. Ex: Celular, Whatsapp, Casa, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Celular',
                                'Whatsapp',
                                'Casa',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['number'] ?? null
                    )
                    ->addActionLabel(__('Adicionar telefone'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Fieldset::make(__('Configs. do tema'))
                    ->schema([
                        Forms\Components\ColorPicker::make('theme.primary_color')
                            ->label(__('Cor primária (hexadecimal)')),
                        Forms\Components\ColorPicker::make('theme.secondary_color')
                            ->label(__('Cor secundária (hexadecimal)')),
                        Forms\Components\ColorPicker::make('theme.background_color')
                            ->label(__('Cor do fundo (hexadecimal)')),
                    ])
                    ->columns(3),
                Forms\Components\Repeater::make('social_media')
                    ->label('Redes Sociais')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label(__('Rede social'))
                            ->options(SocialMediaEnum::class)
                            ->selectablePlaceholder(false)
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('url')
                            ->label(__('Link'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['name'] ?? null
                    )
                    ->addActionLabel(__('Adicionar rede social'))
                    ->defaultItems(0)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Repeater::make('opening_hours')
                    ->label(__('Horário de funcionamento'))
                    ->simple(
                        Forms\Components\TextInput::make('value')
                            ->label(__('Horário de funcionamento'))
                            ->required()
                            ->maxLength(255),
                    )
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['value'] ?? null
                    )
                    ->addActionLabel(__('Adicionar nova linha'))
                    ->defaultItems(0)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAddressFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Endereço'))
            ->description(__('Detalhes do endereço da conta de cliente.'))
            ->schema([
                Forms\Components\Group::make()
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
                            ),
                    ])
                    ->columns(2)
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
                    ->dehydrated(),
                Forms\Components\TextInput::make('address.city')
                    ->label(__('Cidade'))
                    ->placeholder(__('Informe primeiramente o CEP'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('address.district')
                    ->label(__('Bairro'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.address_line')
                    ->label(__('Endereço'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.number')
                    ->label(__('Número'))
                    // ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.complement')
                    ->label(__('Complemento'))
                    ->helperText(__('Apto / Bloco / Casa'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.reference')
                    ->label(__('Ponto de referência'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('address.gmap_coordinates')
                    ->label(__('Incorporar Google Maps'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['address']['zipcode'] = $this->tenant->address?->zipcode;
        $data['address']['uf'] = $this->tenant->address?->uf?->name;
        $data['address']['city'] = $this->tenant->address?->city;
        $data['address']['district'] = $this->tenant->address?->district;
        $data['address']['address_line'] = $this->tenant->address?->address_line;
        $data['address']['number'] = $this->tenant->address?->number;
        $data['address']['complement'] = $this->tenant->address?->complement;
        $data['address']['reference'] = $this->tenant->address?->reference;
        $data['address']['gmap_coordinates'] = $this->tenant->address?->gmap_coordinates;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->updateOrCreateAddress();
    }

    protected function updateOrCreateAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->tenant->address()
            ->updateOrCreate(
                [
                    'addressable_type' => MorphMapByClass(model: $this->tenant::class),
                    'addressable_id'   => $this->tenant->id,
                ],
                $this->data['address'],
            );
    }
}
