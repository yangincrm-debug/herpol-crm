<?php

namespace App\Filament\Resources;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Müşteri Yönetimi';
    protected static ?string $modelLabel = 'Müşteri';
    protected static ?string $pluralModelLabel = 'Müşteriler';
    protected static ?int $navigationSort = 2; // Şirketler 1 olsun, bu 2 olsun

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- Kısım 1: Bağlantı ve Statü ---
                Forms\Components\Section::make('Bağlantı ve Statü')
                    ->description('Müşterinin bağlı olduğu şirket ve sistemdeki durumu.')
                    ->schema([
                        // Şirket İlişkisi (Relation)
                        Forms\Components\Select::make('company_id')
                            ->label('Bağlı Olduğu Şirket')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([ // Modal İçinde Hızlı Şirket Ekleme
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->label('Şirket Adı'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->label('Şirket E-Posta'),
                                Forms\Components\TextInput::make('tax_number')
                                    ->label('Vergi No'),
                            ])
                            ->columnSpan(1),

                        // Enum: Müşteri Tipi
                        Forms\Components\Select::make('type')
                            ->label('Kayıt Tipi')
                            ->options(CustomerType::class)
                            ->required()
                            ->default(CustomerType::INDIVIDUAL)
                            ->native(false),

                        // Enum: Durum
                        Forms\Components\Select::make('status')
                            ->label('Durum')
                            ->options(CustomerStatus::class)
                            ->required()
                            ->default(CustomerStatus::LEAD)
                            ->native(false),
                    ])->columns(3),

                // --- Kısım 2: Kişisel Bilgiler ---
                Forms\Components\Section::make('Kişi Bilgileri')
                    ->description('İletişim kurulacak kişinin detayları.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('first_name')
                                ->label('Ad')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('last_name')
                                ->label('Soyad')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Ünvan (Örn: Satın Alma Md.)')
                                ->placeholder('Müdür, Şef vb.')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('email')
                                ->label('Kişisel E-Posta')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->prefixIcon('heroicon-m-envelope')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('phone')
                                ->label('Cep Telefonu')
                                ->tel()
                                ->prefixIcon('heroicon-m-phone')
                                ->maxLength(255),
                        ]),
                    ]),

                // --- Kısım 3: Adres ve Notlar ---
                Forms\Components\Section::make('Diğer Bilgiler')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Kişisel Adres (Ev/Ofis)')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('notes')
                            ->label('Özel Notlar')
                            ->toolbarButtons([
                                'bold', 'italic', 'bulletList', 'link', 'redo', 'undo'
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Ad Soyad Birleştirme
                Tables\Columns\TextColumn::make('full_name') // Modeldeki Accessor veya veritabanı
                    ->label('Ad Soyad')
                    ->getStateUsing(fn (Customer $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Customer $record) => $record->title), // Altına ünvanı yaz

                // Şirket Adı (Relation)
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Şirket')
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Bireysel'), // Şirket yoksa ne yazsın

                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Posta')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Şirkete Göre Filtrele
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Şirket')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                // Duruma Göre Filtrele
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum')
                    ->options(CustomerStatus::class),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // İleride buraya 'OrdersRelationManager' vb. eklenecek
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
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