<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Müşteri Yönetimi';
    protected static ?string $modelLabel = 'Şirket';
    protected static ?string $pluralModelLabel = 'Şirketler';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- KURAL: Entegrasyon Bilgisi (Sadece Edit ve Doluysa Görünür) ---
                Forms\Components\Section::make('Entegrasyon Durumu')
                    ->schema([
                        Forms\Components\TextInput::make('legacy_code')
                            ->label('Entegrasyon Kodu')
                            ->disabled() // Asla değiştirilemez
                            ->dehydrated(false) // Post edilmez
                            ->helperText('Bu kayıt dış sistemden (MSSQL) gelmektedir.'),
                    ])
                    // Sadece düzenleme sayfasındaysa VE legacy_code doluysa göster
                    ->visible(fn (string $context, ?Company $record) => $context === 'edit' && $record?->legacy_code)
                    ->collapsed(),

                // --- Şirket Bilgileri ---
                Forms\Components\Section::make('Şirket Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Unvan')
                            ->required()
                            ->maxLength(255)
                            // KURAL: İsim benzersiz olmalı (Mükerrer kaydı engellemek için)
                            ->unique(ignoreRecord: true)
                            // Entegrasyon varsa kilitli, yoksa açık
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('short_name')
                            ->label('Kısa Ad')
                            ->maxLength(255)
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('account_number')
                            ->label('Hesap No')
                            ->maxLength(255)
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('type')
                            ->label('Tip')
                            ->maxLength(255)
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),
                    ])
                    ->columns(2),

                // --- İletişim ve Fatura ---
                Forms\Components\Section::make('İletişim ve Fatura')
                    ->schema([
                        Forms\Components\TextInput::make('tax_number')
                            ->label('Vergi No / TCKN')
                            ->numeric()
                            ->maxLength(11)
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('tax_office')
                            ->label('Vergi Dairesi')
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\Textarea::make('address')
                            ->label('Adres')
                            ->columnSpanFull()
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('fax')
                            ->label('Fax')
                            ->tel()
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('email')
                            ->label('E-Posta')
                            ->email()
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),

                        Forms\Components\TextInput::make('contact_person')
                            ->label('İlgili Kişi')
                            ->disabled(fn (?Company $record) => filled($record?->legacy_code)),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // KURAL: Satıra tıklanınca işlem yapma
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('legacy_code')
                    ->label('Kod')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Manuel')
                    // KURAL: 6 Punto (~8px)
                    ->extraAttributes(['style' => 'font-size: 5px !important']),

                Tables\Columns\TextColumn::make('name')
                    ->label('Unvan')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('bold')
                    ->extraAttributes(['style' => 'font-size: 5px !important']),

                Tables\Columns\TextColumn::make('short_name')
                    ->label('Kısa Ad')
                    ->searchable()
                    ->extraAttributes(['style' => 'font-size: 5px !important'])
                    ->toggleable(isToggledHiddenByDefault: true), // Mobilde gizli

                Tables\Columns\TextColumn::make('tax_number')
                    ->label('Vergi No')
                    ->searchable()
                    ->extraAttributes(['style' => 'font-size: 5px !important'])
                    ->toggleable(), // Mobilde açılabilir

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->extraAttributes(['style' => 'font-size: 5px !important']),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Posta')
                    ->searchable()
                    ->extraAttributes(['style' => 'font-size: 5px !important'])
                    ->toggleable(isToggledHiddenByDefault: true), // Mobilde gizli
            ])
            // --- DevExpress Tarzı Hızlı Filtreleme ---
            ->filters([
                // 1. Unvana Göre Filtre (Input)
                Tables\Filters\Filter::make('name_filter')
                    ->form([
                        Forms\Components\TextInput::make('name_search')
                            ->label('Unvan Ara')
                            ->placeholder('Unvan...')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['name_search'],
                            fn (Builder $query, $term) => $query->where('name', 'like', "%{$term}%")
                        );
                    }),

                // 2. Vergi No Filtresi (Input)
                Tables\Filters\Filter::make('tax_filter')
                    ->form([
                        Forms\Components\TextInput::make('tax_search')
                            ->label('Vergi No Ara')
                            ->placeholder('Vergi...')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['tax_search'],
                            fn (Builder $query, $term) => $query->where('tax_number', 'like', "%{$term}%")
                        );
                    }),

                // 3. Entegrasyon Filtresi
                Tables\Filters\SelectFilter::make('integration_status')
                    ->label('Kaynak')
                    ->options([
                        'integrated' => 'Entegre (MSSQL)',
                        'manual' => 'Manuel',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'integrated') {
                            return $query->whereNotNull('legacy_code');
                        }
                        if ($data['value'] === 'manual') {
                            return $query->whereNull('legacy_code');
                        }
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            // --- Filtre Görünüm Ayarları (Mobil Uyumlu) ---
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent) // Tablonun üzerinde açık durur
            ->filtersFormColumns([
                'default' => 1, // Mobilde: Alt alta 1 sütun
                'sm' => 2,      // Tablette: 2 sütun
                'lg' => 4,      // Masaüstünde: 4 sütun yan yana
            ])
            
            ->actions([
                // KURAL: Sadece ikon butonlar
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton()->color('primary'),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // KURAL: Müşteri İlişkisi
            RelationManagers\CustomersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}