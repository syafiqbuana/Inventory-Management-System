<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;



class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $pluralLabel = 'Data User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->options(function () {

                        $user = Auth::user();

                        if ($user->role === 'super_admin') {
                            return [
                                'admin' => 'Admin',
                                'staff' => 'Staff',
                            ];
                        }

                        if ($user->role === 'admin') {
                            return [
                                'staff' => 'Staff',
                            ];
                        }

                    })
                    ->required()
                    ->default('staff'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->color('info')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->date(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah pada')
                    ->date(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'super_admin' => 'success',
                        'admin' => 'primary',
                        'staff' => 'warning',
                    })
                ,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user?->role === 'super_admin') {
            return $query;
        }

        if ($user?->role === 'admin') {
            return $query->whereIn('role', ['admin', 'staff']);
        }

        return $query->whereRaw('1 = 0'); 
    }


    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role, ['admin', 'super_admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
