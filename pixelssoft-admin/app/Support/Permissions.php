<?php

namespace App\Support;

class Permissions
{
    public const ROLES = [
        'super-admin',
        'content-editor',
        'sales-manager',
        'sales-rep',
        'project-manager',
        'finance',
        'hr-admin',
        'employee',
    ];

    public static function all(): array
    {
        return [
            // System
            'system.users.view', 'system.users.create', 'system.users.edit', 'system.users.delete',
            'system.roles.view', 'system.roles.edit',
            'system.invitations.manage',
            'system.activity.view',
            // CMS
            'cms.blogs.view', 'cms.blogs.create', 'cms.blogs.edit', 'cms.blogs.delete',
            'cms.portfolios.view', 'cms.portfolios.create', 'cms.portfolios.edit', 'cms.portfolios.delete',
            'cms.showcases.view', 'cms.showcases.create', 'cms.showcases.edit', 'cms.showcases.delete',
            'cms.services.view', 'cms.services.create', 'cms.services.edit', 'cms.services.delete',
            'cms.sections.view', 'cms.sections.edit',
            'cms.media.view', 'cms.media.upload', 'cms.media.delete',
            'cms.messages.view', 'cms.messages.read',
            'cms.settings.view', 'cms.settings.edit',
            // CRM
            'crm.dashboard.view',
            'crm.leads.view', 'crm.leads.create', 'crm.leads.edit', 'crm.leads.delete', 'crm.leads.convert',
            'crm.deals.view', 'crm.deals.create', 'crm.deals.edit', 'crm.deals.delete',
            'crm.companies.view', 'crm.companies.create', 'crm.companies.edit', 'crm.companies.delete',
            'crm.contacts.view', 'crm.contacts.create', 'crm.contacts.edit', 'crm.contacts.delete',
            'crm.activities.view', 'crm.activities.create', 'crm.activities.edit', 'crm.activities.delete',
            'crm.reports.view',
            // PM
            'pm.dashboard.view',
            'pm.projects.view', 'pm.projects.create', 'pm.projects.edit', 'pm.projects.delete',
            'pm.tasks.view', 'pm.tasks.create', 'pm.tasks.edit', 'pm.tasks.delete',
            'pm.time.view-own', 'pm.time.view-all', 'pm.time.approve',
            'pm.reports.view',
            // Accounts
            'accounts.dashboard.view',
            'accounts.invoices.view', 'accounts.invoices.create', 'accounts.invoices.edit', 'accounts.invoices.delete',
            'accounts.payments.view', 'accounts.payments.create',
            'accounts.expenses.view-own', 'accounts.expenses.view-all', 'accounts.expenses.create', 'accounts.expenses.approve',
            'accounts.reports.view',
            // HR
            'hr.dashboard.view',
            'hr.employees.view', 'hr.employees.create', 'hr.employees.edit', 'hr.employees.delete',
            'hr.leave.view-own', 'hr.leave.request', 'hr.leave.approve',
            'hr.attendance.view', 'hr.attendance.manage',
            'hr.payroll.view', 'hr.payroll.process',
            'hr.documents.view', 'hr.documents.manage',
        ];
    }

    public static function rolePermissions(): array
    {
        $all = self::all();

        return [
            'super-admin' => $all,
            'content-editor' => array_filter($all, fn ($p) => str_starts_with($p, 'cms.')),
            'sales-manager' => array_merge(
                array_filter($all, fn ($p) => str_starts_with($p, 'crm.')),
                ['pm.projects.view', 'pm.dashboard.view', 'accounts.invoices.view', 'accounts.dashboard.view']
            ),
            'sales-rep' => [
                'crm.dashboard.view', 'crm.leads.view', 'crm.leads.create', 'crm.leads.edit', 'crm.leads.convert',
                'crm.deals.view', 'crm.deals.create', 'crm.deals.edit',
                'crm.companies.view', 'crm.companies.create', 'crm.companies.edit',
                'crm.contacts.view', 'crm.contacts.create', 'crm.contacts.edit',
                'crm.activities.view', 'crm.activities.create', 'crm.activities.edit',
            ],
            'project-manager' => array_merge(
                array_filter($all, fn ($p) => str_starts_with($p, 'pm.')),
                ['crm.deals.view', 'crm.companies.view', 'accounts.invoices.create', 'accounts.invoices.view']
            ),
            'finance' => array_filter($all, fn ($p) => str_starts_with($p, 'accounts.')),
            'hr-admin' => array_filter($all, fn ($p) => str_starts_with($p, 'hr.') || str_starts_with($p, 'system.users.view')),
            'employee' => [
                'pm.dashboard.view', 'pm.tasks.view', 'pm.time.view-own',
                'hr.leave.view-own', 'hr.leave.request', 'hr.attendance.view',
                'accounts.expenses.view-own', 'accounts.expenses.create',
            ],
        ];
    }
}
