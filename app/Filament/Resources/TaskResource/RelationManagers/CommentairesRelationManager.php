<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\TaskCommentaire;
use Illuminate\Support\Facades\Auth;

class CommentairesRelationManager extends RelationManager
{
    protected static string $relationship = 'commentaires';

    protected static ?string $recordTitleAttribute = 'id';
    


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('contenu')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\Toggle::make('prive')
                    ->label('Commentaire privé')
                    ->helperText('Les commentaires privés ne sont visibles que par les administrateurs et les managers')
                    ->default(false),
                
                Forms\Components\Select::make('parent_id')
                    ->label('En réponse à')
                    ->options(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()->commentaires()
                            ->whereNull('parent_id')
                            ->get()
                            ->pluck('contenu_court', 'id');
                    })
                    ->searchable()
                    ->placeholder('Commentaire principal'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auteur')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('contenu')
                    ->label('Contenu')
                    ->html()
                    ->limit(50),
                
                Tables\Columns\IconColumn::make('prive')
                    ->label('Privé')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('parent.contenu_court')
                    ->label('En réponse à')
                    ->placeholder('Commentaire principal')
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('prive')
                    ->label('Commentaires privés'),
                
                Tables\Filters\Filter::make('sans_reponse')
                    ->label('Commentaires principaux')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),
                
                Tables\Filters\Filter::make('avec_reponse')
                    ->label('Réponses')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = Auth::id();
                        $data['entreprise_id'] = $this->getOwnerRecord()->entreprise_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('repondre')
                    ->label('Répondre')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('primary')
                    ->form([
                        Forms\Components\RichEditor::make('contenu')
                            ->label('Réponse')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('prive')
                            ->label('Commentaire privé')
                            ->helperText('Les commentaires privés ne sont visibles que par les administrateurs et les managers')
                            ->default(false),
                    ])
                    ->action(function (TaskCommentaire $record, array $data) {
                        $task = $this->getOwnerRecord();
                        
                        TaskCommentaire::create([
                            'task_id' => $task->id,
                            'entreprise_id' => $task->entreprise_id,
                            'user_id' => Auth::id(),
                            'contenu' => $data['contenu'],
                            'prive' => $data['prive'],
                            'parent_id' => $record->id,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Réponse ajoutée')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (TaskCommentaire $record): bool => $record->parent_id === null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
