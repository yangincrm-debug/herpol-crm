<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    // Sekme Başlığı
    protected static ?string $title = 'Bağlı Kişiler / Çalışanlar';
    
    // Oluşturulacak kaydın başlığı (Silme modalında görünür)
    protected static ?string $recordTitleAttribute = 'first_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- Kişisel Bilgiler ---
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

                // --- İletişim ve Ünvan ---
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Ünvan')
                        ->placeholder('Müdür, Şef vb.')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('E-Posta')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefon')
                        ->tel()
                        ->maxLength(255),
                ]),

                // --- Durum ve Tip ---
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tip')
                        ->options(CustomerType::class)
                        ->required()
                        ->default(CustomerType::CORPORATE) // Şirket içinden eklediğimiz için varsayılan Kurumsal olsun
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->label('Durum')
                        ->options(CustomerStatus::class)
                        ->required()
                        ->default(CustomerStatus::LEAD)
                        ->native(false),
                ]),
                
                Forms\Components\RichEditor::make('notes')
                    ->label('Kişiye Özel Notlar')
                    ->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'bulletList']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Ad Soyad')
                    ->getStateUsing(fn (Customer $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold')
                    ->description(fn (Customer $record) => $record->title), // Altına ünvanı yaz

                Tables\Columns\TextColumn::make('email')
                    ->label('İletişim')
                    ->icon('heroicon-m-envelope')
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(CustomerStatus::class)
                    ->label('Durum'),
            ])
            ->headerActions([
                // Buradan 'Create' dediğinde otomatik olarak company_id'yi doldurur
                Tables\Actions\CreateAction::make()
                    ->label('Yeni Çalışan Ekle')
                    ->modalHeading('Şirkete Yeni Kişi Ekle'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}