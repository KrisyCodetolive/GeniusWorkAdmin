<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des groupes de permissions
        $permissionGroups = [
            'users' => 'Gestion des utilisateurs',
            'employees' => 'Gestion des employés',
            'presence' => 'Gestion des présences',
            'leaves' => 'Gestion des congés',
            'reports' => 'Gestion des rapports',
            'settings' => 'Paramètres système',
            'billing' => 'Gestion de la facturation',
            'departments' => 'Gestion des départements'
        ];

        // Création des permissions système
        $permissions = [
            // Users Management
            'users' => [
                'view_users' => 'Voir les utilisateurs',
                'create_users' => 'Créer des utilisateurs',
                'edit_users' => 'Modifier les utilisateurs',
                'delete_users' => 'Supprimer les utilisateurs',
                'manage_roles' => 'Gérer les rôles',
            ],
            
            // Employees Management
            'employees' => [
                'view_employees' => 'Voir les employés',
                'create_employees' => 'Créer des employés',
                'edit_employees' => 'Modifier les employés',
                'delete_employees' => 'Supprimer des employés',
                'view_employee_history' => 'Voir l\'historique des employés',
            ],
            
            // Presence Management
            'presence' => [
                'view_presence' => 'Voir les présences',
                'manage_presence' => 'Gérer les présences',
                'validate_presence' => 'Valider les présences',
                'edit_presence' => 'Modifier les présences',
                'view_presence_history' => 'Voir l\'historique des présences',
            ],
            
            // Leaves Management
            'leaves' => [
                'view_leaves' => 'Voir les congés',
                'create_leaves' => 'Créer des congés',
                'approve_leaves' => 'Approuver les congés',
                'reject_leaves' => 'Rejeter les congés',
                'manage_leave_types' => 'Gérer les types de congés',
            ],
            
            // Reports Management
            'reports' => [
                'view_reports' => 'Voir les rapports',
                'create_reports' => 'Créer des rapports',
                'export_reports' => 'Exporter les rapports',
                'manage_report_settings' => 'Gérer les paramètres des rapports',
            ],
            
            // Settings Management
            'settings' => [
                'view_settings' => 'Voir les paramètres',
                'edit_settings' => 'Modifier les paramètres',
                'manage_company_settings' => 'Gérer les paramètres de l\'entreprise',
                'manage_system_settings' => 'Gérer les paramètres système',
            ],
            
            // Billing Management
            'billing' => [
                'view_billing' => 'Voir la facturation',
                'manage_subscriptions' => 'Gérer les abonnements',
                'view_invoices' => 'Voir les factures',
                'manage_payment_methods' => 'Gérer les moyens de paiement',
            ],
            
            // Departments Management
            'departments' => [
                'view_departments' => 'Voir les départements',
                'create_departments' => 'Créer des départements',
                'edit_departments' => 'Modifier les départements',
                'delete_departments' => 'Supprimer des départements',
                'manage_department_heads' => 'Gérer les chefs de département',
            ],
        ];

        // Création des permissions dans la base de données
        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $name => $description) {
                Permission::create([
                    'name' => $name,
                    'description' => $description,
                    'groupe' => $group,
                    'is_system' => true,
                    'guard_name' => 'web',
                    'meta_data' => ['group_description' => $permissionGroups[$group]]
                ]);
            }
        }

        // Création des rôles système
        $roles = [
            'super_admin' => [
                'description' => 'Super Administrateur du système',
                'is_system' => true,
                'permissions' => '*'
            ],
            'support' => [
                'description' => 'Support',
                'is_system' => true,
                'permissions' => [
                    'view_users', 'create_users', 'edit_users',
                    'view_employees', 'create_employees', 'edit_employees',
                    'view_presence', 'manage_presence', 'validate_presence',
                    'view_leaves', 'approve_leaves', 'reject_leaves',
                    'view_reports', 'create_reports', 'export_reports'
                ]
            ],
            'admin' => [
                'description' => 'Administrateur',
                'is_system' => true,
                'permissions' => [
                    'view_users', 'create_users', 'edit_users',
                    'view_employees', 'create_employees', 'edit_employees',
                    'view_presence', 'manage_presence', 'validate_presence',
                    'view_leaves', 'approve_leaves', 'reject_leaves',
                    'view_reports', 'create_reports', 'export_reports',
                    'view_settings', 'edit_settings', 'manage_company_settings',
                    'view_departments', 'manage_department_heads'
                ]
            ],
            'manager' => [
                'description' => 'Manager de département',
                'is_system' => true,
                'permissions' => [
                    'view_employees', 'view_presence', 'validate_presence',
                    'view_leaves', 'approve_leaves', 'reject_leaves',
                    'view_reports', 'create_reports'
                ]
            ],
            'rh' => [
                'description' => 'Responsable des Ressources Humaines',
                'is_system' => true,
                'permissions' => [
                    'view_employees', 'create_employees', 'edit_employees',
                    'view_presence', 'manage_presence', 'validate_presence',
                    'view_leaves', 'approve_leaves', 'reject_leaves', 'manage_leave_types',
                    'view_reports', 'create_reports', 'export_reports'
                ]
            ],
            'employee' => [
                'description' => 'Employé',
                'is_system' => true,
                'permissions' => [
                    'view_presence', 'create_leaves', 'view_leaves'
                ]
            ],
            'employeur' => [
                'description' => 'Employeur',
                'is_system' => true,
                'permissions' => [
                    'view_employees', 'create_employees', 'edit_employees',
                    'view_presence', 'manage_presence', 'validate_presence',
                    'view_leaves', 'approve_leaves', 'reject_leaves', 'manage_leave_types',
                    'view_reports', 'create_reports', 'export_reports'
                ]
            ]
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::create([
                'name' => $roleName,
                'description' => $roleData['description'],
                'is_system' => $roleData['is_system'],
                'guard_name' => 'web'
            ]);

            // Attribution des permissions
            if ($roleData['permissions'] === '*') {
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($roleData['permissions']);
            }
        }
    }
}
