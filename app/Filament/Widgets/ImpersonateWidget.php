<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Entreprise;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Log;

class ImpersonateWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    // Définir la position du widget sur le tableau de bord
    protected static string $view = 'filament.widgets.impersonate-widget';

    // Propriétés du formulaire
    public ?string $entreprise_id = null;
    public ?string $user_id = null;
    
    // Liste des utilisateurs de l'entreprise
    public $users = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    // Définir si le widget doit être affiché
    public static function canView(): bool
    {
        // N'afficher le widget que pour les SuperAdmin et Support
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isSupport());
    }
    
    // Définir le formulaire
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('entreprise_id')
                            ->label('Entreprise')
                            ->options(Entreprise::query()->pluck('nom', 'id'))
                            ->searchable()
                            ->placeholder('Sélectionnez une entreprise')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->user_id = null;
                                $this->loadUsers();
                            }),
                        
                        Select::make('user_id')
                            ->label('Utilisateur')
                            ->options(function () {
                                return $this->users;
                            })
                            ->searchable()
                            ->placeholder('Sélectionnez un utilisateur')
                            ->required()
                            ->disabled(fn () => !$this->entreprise_id)
                            ->live(),
                    ]),
            ]);
    }
    
    // Charger les utilisateurs de l'entreprise sélectionnée
    protected function loadUsers()
    {
        if (!$this->entreprise_id) {
            $this->users = [];
            return;
        }
        
        $query = User::query()
            ->where('id', '!=', Auth::id()) // Exclure l'utilisateur actuel
            ->where('entreprise_id', $this->entreprise_id)
            ->where(function ($q) {
                $q->where('role', 'admin')
                  ->orWhere('role', 'entreprise');
            });
        
        $this->users = $query->get()->pluck('name', 'id')->toArray();
        
        if (empty($this->users)) {
            Notification::make()
                ->title('Information')
                ->body('Aucun administrateur trouvé pour cette entreprise.')
                ->info()
                ->send();
        }
    }
    
    // Action pour se connecter en tant qu'un autre utilisateur
    public function impersonate()
    {
        try {
            // Vérifier si l'utilisateur actuel est un SuperAdmin ou Support
            if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport()) {
                Notification::make()
                    ->title('Action non autorisée')
                    ->body('Vous n\'avez pas les permissions nécessaires pour cette action.')
                    ->danger()
                    ->send();
                
                return;
            }
            
            // Vérifier si un utilisateur a été sélectionné
            if (!$this->user_id) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Veuillez sélectionner un utilisateur.')
                    ->danger()
                    ->send();
                
                return;
            }
            
            // Trouver l'utilisateur à impersonate
            $userToImpersonate = User::find($this->user_id);
            
            if (!$userToImpersonate) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Utilisateur introuvable.')
                    ->danger()
                    ->send();
                
                return;
            }
            
            // Stocker l'ID de l'utilisateur original et son rôle
            $originalUserId = Auth::id();
            $originalUserRole = Auth::user()->role;
            
            // Nettoyer la session actuelle
            Session::flush();
            
            // Créer une nouvelle session
            Session::regenerate(true);
            
            // Stocker les informations d'impersonation dans la nouvelle session
            Session::put('impersonate_origin_id', $originalUserId);
            Session::put('impersonate_origin_role', $originalUserRole);
            
            // Se connecter en tant que l'utilisateur cible
            Auth::loginUsingId($userToImpersonate->id);
            
            // Journaliser l'action pour l'audit
            Log::info('Impersonation: Utilisateur ' . $originalUserId . ' se connecte en tant que ' . $userToImpersonate->id);
            Log::info('Utilisateur impersonaté : ' . $userToImpersonate->name);
            Log::info('Role impersonaté : ' . $userToImpersonate->role);
            
            // Rediriger vers le tableau de bord avec un message de succès
            return redirect()->to(route('filament.admin.pages.dashboard'));
        } catch (\Exception $exception) {
            \Log::error('Erreur d\'impersonation: ' . $exception->getMessage());
            
            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue: ' . $exception->getMessage())
                ->danger()
                ->send();
        }
    }
    
    // Action pour revenir à l'utilisateur original
    public static function stopImpersonating()
    {
        if (!Session::has('impersonate_origin_id')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }
        
        // Récupérer l'ID de l'utilisateur original
        $originalUserId = Session::get('impersonate_origin_id');
        
        // Récupérer l'utilisateur original
        $originalUser = User::find($originalUserId);
        
        if (!$originalUser) {
            // Si l'utilisateur original n'existe plus, déconnecter simplement
            Auth::logout();
            Session::flush();
            
            return redirect()->route('filament.admin.auth.login');
        }
        
        // Nettoyer la session actuelle
        Session::flush();
        
        // Créer une nouvelle session
        Session::regenerate(true);
        
        // Se reconnecter en tant qu'utilisateur original
        Auth::loginUsingId($originalUserId);
        
        // Journaliser l'action pour l'audit
        Log::info('Fin d\'impersonation: Retour à l\'utilisateur ' . $originalUserId);
        
        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', 'Vous êtes revenu à votre compte original.');
    }
}
