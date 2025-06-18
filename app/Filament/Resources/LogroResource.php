<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogroResource\Pages;
use App\Models\Logro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class LogroResource extends Resource
{
    protected static ?string $model = Logro::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Logros';
    
    protected static ?string $modelLabel = 'Logro';
    
    protected static ?string $pluralModelLabel = 'Logros';
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(20)
                    ->label('Código'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('codigo')
                            ->required()
                            ->maxLength(20)
                            ->label('Código'),
                        Forms\Components\Select::make('grado_id')
                            ->relationship('grado', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grado'),
                    ])
                    ->label('Materia'),
                Forms\Components\Select::make('grado_id')
                    ->relationship('grado', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'preescolar' => 'Preescolar',
                                'primaria' => 'Primaria',
                                'secundaria' => 'Secundaria',
                            ])
                            ->required()
                            ->label('Tipo'),
                    ])
                    ->label('Grado'),
                Forms\Components\Select::make('periodos')
                    ->relationship('periodos', 'nombre')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->required()
                            ->label('Fecha de Inicio'),
                        Forms\Components\DatePicker::make('fecha_fin')
                            ->required()
                            ->label('Fecha de Fin'),
                    ])
                    ->label('Períodos'),
                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Logro Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('grado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Grado'),
                Tables\Columns\TextColumn::make('periodos.nombre')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->label('Períodos'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('grado_id')
                    ->relationship('grado', 'nombre')
                    ->label('Grado'),
                Tables\Filters\SelectFilter::make('periodos')
                    ->relationship('periodos', 'nombre')
                    ->label('Período'),
                Tables\Filters\SelectFilter::make('activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Logro $record) {
                        // Eliminar en cascada los logros de estudiantes
                        $record->estudianteLogros()->delete();
                    })
                    ->after(function (Logro $record) {
                        Notification::make()
                            ->title('Logro eliminado exitosamente')
                            ->icon('heroicon-o-trash')
                            ->iconColor('danger')
                            ->body('El logro y sus registros relacionados han sido eliminados del sistema.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Eliminar en cascada los logros de estudiantes
                                $record->estudianteLogros()->delete();
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Logros eliminados exitosamente')
                                ->icon('heroicon-o-trash')
                                ->iconColor('danger')
                                ->body('Los logros seleccionados y sus registros relacionados han sido eliminados del sistema.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogros::route('/'),
            'create' => Pages\CreateLogro::route('/create'),
            'edit' => Pages\EditLogro::route('/{record}/edit'),
        ];
    }
}
