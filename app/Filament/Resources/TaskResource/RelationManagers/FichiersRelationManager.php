<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\TaskFichier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FichiersRelationManager extends RelationManager
{
    protected static string $relationship = 'fichiers';

    protected static ?string $recordTitleAttribute = 'nom';
    
  

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('fichier')
                    ->label('Fichier')
                    ->required()
                    ->disk('public')
                    ->directory('tasks/fichiers')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain', 'application/zip', 'application/x-rar-compressed'])
                    ->maxSize(10240) // 10MB
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('nom')
                    ->label('Nom du fichier')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                Forms\Components\Toggle::make('est_livrable')
                    ->label('Ce fichier est un livrable')
                    ->default(false),
                
                Forms\Components\KeyValue::make('metadata')
                    ->label('Métadonnées')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                
                Tables\Columns\IconColumn::make('est_livrable')
                    ->label('Livrable')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('taille_formatee')
                    ->label('Taille')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('taille', $direction)),
                
                Tables\Columns\TextColumn::make('type_mime')
                    ->label('Type')
                    ->formatStateUsing(function (string $state): string {
                        $types = [
                            'application/pdf' => 'PDF',
                            'image/jpeg' => 'Image JPEG',
                            'image/png' => 'Image PNG',
                            'application/msword' => 'Word',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
                            'application/vnd.ms-excel' => 'Excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
                            'application/vnd.ms-powerpoint' => 'PowerPoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
                            'text/plain' => 'Texte',
                            'application/zip' => 'ZIP',
                            'application/x-rar-compressed' => 'RAR',
                        ];
                        
                        return $types[$state] ?? $state;
                    }),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Téléchargé par')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Téléchargé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('est_livrable')
                    ->label('Livrables uniquement'),
                
                Tables\Filters\SelectFilter::make('type_mime')
                    ->label('Type de fichier')
                    ->options([
                        'application/pdf' => 'PDF',
                        'image/jpeg' => 'Image JPEG',
                        'image/png' => 'Image PNG',
                        'application/msword' => 'Word',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
                        'application/vnd.ms-excel' => 'Excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
                        'application/vnd.ms-powerpoint' => 'PowerPoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
                        'text/plain' => 'Texte',
                        'application/zip' => 'ZIP',
                        'application/x-rar-compressed' => 'RAR',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        /** @var TemporaryUploadedFile $file */
                        $file = $data['fichier'];
                        
                        $data['chemin'] = $file->getRealPath();
                        $data['taille'] = $file->getSize();
                        $data['type_mime'] = $file->getMimeType();
                        $data['user_id'] = Auth::id();
                        $data['entreprise_id'] = $this->getOwnerRecord()->entreprise_id;
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('telecharger')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (TaskFichier $record): string => Storage::disk('public')->url($record->chemin))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('telecharger_zip')
                        ->label('Télécharger (ZIP)')
                        ->icon('heroicon-o-archive-box')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            // Logique pour créer un fichier ZIP avec tous les fichiers sélectionnés
                            // Ceci est simplifié et nécessiterait une implémentation complète
                            $zipPath = storage_path('app/public/tasks/fichiers/temp/' . uniqid('task_files_') . '.zip');
                            $zipUrl = Storage::disk('public')->url('tasks/fichiers/temp/' . basename($zipPath));
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Téléchargement ZIP préparé')
                                ->body('Cliquez pour télécharger les fichiers sélectionnés.')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('download')
                                        ->label('Télécharger')
                                        ->url($zipUrl)
                                        ->openUrlInNewTab(),
                                ])
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
