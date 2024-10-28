<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BrgyInhabitantResource\Pages;
use App\Models\BrgyInhabitant;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BrgyInhabitantResource extends Resource
{
    protected static ?string $model = BrgyInhabitant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Inhabitants';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                Forms\Components\TextInput::make('lastname')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('firstname')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('middlename')
                    ->label('Middlename (optional)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('age')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birthdate')
                    ->required(),
                    Forms\Components\TextInput::make('purok')
                    ->label('Purok')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('placeofbirth')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('sex')
                    ->required()
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),
                Forms\Components\Select::make('civilstatus')
                    ->required()
                    ->options([
                        'Single' => 'Single',
                        'Married' => 'Married',
                        'Divorced' => 'Divorced',
                        'Widowed' => 'Widowed',
                    ]),
                Forms\Components\Select::make('positioninFamily')
                    ->required()
                    ->options([
                        'Head of the family' => 'Head of the family',
                        'Wife' => 'Wife',
                        'Son' => 'Son',
                        'Daugther' => 'Daugther',
                    ]),
                Forms\Components\Select::make('citizenship')
                    ->required()
                    ->options([
                        'Filipino' => 'Filipino',
                        'Others' => 'Others',
                    ])
                    ->reactive(), // Make this field reactive to detect changes

                Forms\Components\TextInput::make('other_citizenship')
                    ->label('Please specify citizenship')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('citizenship') === 'Others'),
                Forms\Components\Select::make('educAttainment')
                    ->required()
                    ->options([
                        'Graduate' => 'Graduate',
                        'Others' => 'Others',
                    ])
                    ->reactive(),
                Forms\Components\TextInput::make('other_educationalAtt')
                    ->label('Please specify Attainment')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('educAttainment') === 'Others'),
                Forms\Components\TextInput::make('occupation')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('ofw')
                    ->required()
                    ->options([
                        'Yes' => 'YES',
                        'No' => 'No',
                    ]),
                Forms\Components\Select::make('PWD')
                    ->required()
                    ->options([
                        'YES' => 'YES',
                        'NO' => 'NO',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lastname')->searchable(),
                Tables\Columns\TextColumn::make('firstname')->searchable(),
                Tables\Columns\TextColumn::make('middlename')->searchable(),
                Tables\Columns\TextColumn::make('age')->searchable(),
                Tables\Columns\TextColumn::make('birthdate')->searchable(),
                Tables\Columns\TextColumn::make('purok')->searchable(),
                Tables\Columns\TextColumn::make('placeofbirth')->searchable(),
                Tables\Columns\TextColumn::make('sex')->searchable(),
                Tables\Columns\TextColumn::make('civilstatus')->searchable(),
                Tables\Columns\TextColumn::make('positioninFamily')->searchable(),
                Tables\Columns\TextColumn::make('citizenship')->searchable(),
                Tables\Columns\TextColumn::make('educAttainment')->searchable(),
                Tables\Columns\TextColumn::make('occupation')->searchable(),
                Tables\Columns\TextColumn::make('ofw')->searchable(),
                Tables\Columns\TextColumn::make('pwd')->searchable(),
                BooleanColumn::make('is_approved')->label('Approved'),
            ])
            ->filters([
                Filter::make('Pending Approval')
                    ->query(fn (Builder $query) => $query->where('is_approved', false)),
                  // Filter for PWD
            Filter::make('PWD')
            ->query(fn (Builder $query) => $query->where('pwd', true)),

        // Filter for OFW
        Filter::make('OFW')
            ->query(fn (Builder $query) => $query->where('ofw', true)),

        // Filter for age 60 and above
        Filter::make('Senior Citizens')
            ->label('Age 60 and Above')
            ->query(fn (Builder $query) => $query->where('age', '>=', 60)),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->action(function (BrgyInhabitant $record) {
                        $record->is_approved = true;
                        $record->save();
                    })
                    ->visible(fn (BrgyInhabitant $record) => Filament::auth()->user() && (Filament::auth()->user()->hasRole('super_admin') || Filament::auth()->user()->hasRole('brgySecretary')) && ! $record->is_approved),
                Tables\Actions\EditAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('brgySecretary')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            // Define any relationships if necessary
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrgyInhabitants::route('/'),
            'create' => Pages\CreateBrgyInhabitant::route('/create'),
            'edit' => Pages\EditBrgyInhabitant::route('/{record}/edit'),
        ];
    }
}