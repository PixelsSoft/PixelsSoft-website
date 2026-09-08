<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — PixelsSoft</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-link">
                <span class="sidebar-mark" aria-hidden="true">
                    <img src="{{ asset('favicon.png') }}" alt="">
                </span>
                <span class="sidebar-brand-text">
                    <strong>PixelsSoft</strong>
                    <small>Business Platform</small>
                </span>
            </a>
        </div>
        <nav class="sidebar-nav" id="sidebar-nav">
            <div class="nav-group" data-group="overview">
                <button type="button" class="nav-group-toggle" aria-expanded="true">
                    <span>Overview</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            @can('crm.dashboard.view')
            <div class="nav-group" data-group="crm">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Sales / CRM</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    <a href="{{ route('admin.crm.dashboard') }}" class="{{ request()->routeIs('admin.crm.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        CRM Dashboard
                    </a>
                    @can('crm.leads.view')
                    <a href="{{ route('admin.crm.leads.index') }}" class="{{ request()->routeIs('admin.crm.leads.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Leads
                    </a>
                    @endcan
                    @can('crm.deals.view')
                    <a href="{{ route('admin.crm.deals.kanban') }}" class="{{ request()->routeIs('admin.crm.deals.kanban', 'admin.crm.deals.create', 'admin.crm.deals.edit', 'admin.crm.deals.index') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10"/></svg>
                        Deal Pipeline
                    </a>
                    <a href="{{ route('admin.crm.milestones.index') }}" class="{{ request()->routeIs('admin.crm.milestones.*', 'admin.crm.deals.show') ? 'active' : '' }}">Milestones</a>
                    @endcan
                    @can('crm.companies.view')
                    <a href="{{ route('admin.crm.companies.index') }}" class="{{ request()->routeIs('admin.crm.companies.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Companies
                    </a>
                    @endcan
                    @can('crm.contacts.view')
                    <a href="{{ route('admin.crm.contacts.index') }}" class="{{ request()->routeIs('admin.crm.contacts.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Contacts
                    </a>
                    @endcan
                    @can('crm.reports.view')
                    <a href="{{ route('admin.crm.reports.index') }}" class="{{ request()->routeIs('admin.crm.reports.*') ? 'active' : '' }}">CRM Reports</a>
                    @endcan
                    @can('crm.sources.manage')
                    <a href="{{ route('admin.crm.sources.index') }}" class="{{ request()->routeIs('admin.crm.sources.*') ? 'active' : '' }}">Lead Sources</a>
                    @endcan
                </div>
            </div>
            @endcan

            @canany(['pm.dashboard.view', 'pm.projects.view'])
            <div class="nav-group" data-group="pm">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Projects</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    @can('pm.dashboard.view')
                    <a href="{{ route('admin.pm.dashboard') }}" class="{{ request()->routeIs('admin.pm.dashboard') ? 'active' : '' }}">PM Dashboard</a>
                    @endcan
                    @can('pm.projects.view')
                    <a href="{{ route('admin.pm.projects.index') }}" class="{{ request()->routeIs('admin.pm.projects.*') ? 'active' : '' }}">Projects</a>
                    @endcan
                    @can('pm.time.view-own')
                    <a href="{{ route('admin.pm.time.index') }}" class="{{ request()->routeIs('admin.pm.time.*') ? 'active' : '' }}">Timesheet</a>
                    @endcan
                    @can('pm.reports.view')
                    <a href="{{ route('admin.pm.reports.index') }}" class="{{ request()->routeIs('admin.pm.reports.*') ? 'active' : '' }}">PM Reports</a>
                    @endcan
                </div>
            </div>
            @endcanany

            @canany(['accounts.dashboard.view', 'accounts.invoices.view', 'accounts.payments.view', 'accounts.settlements.manage', 'accounts.ledger.view', 'accounts.commissions.view', 'accounts.payment-accounts.manage', 'accounts.expenses.view-own', 'accounts.expenses.view-all', 'accounts.reports.view'])
            <div class="nav-group" data-group="accounts">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Accounts</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    @can('accounts.dashboard.view')
                    <a href="{{ route('admin.accounts.dashboard') }}" class="{{ request()->routeIs('admin.accounts.dashboard') ? 'active' : '' }}">Finance Dashboard</a>
                    @endcan
                    @can('accounts.invoices.view')
                    <a href="{{ route('admin.accounts.invoices.index') }}" class="{{ request()->routeIs('admin.accounts.invoices.*') ? 'active' : '' }}">Invoices</a>
                    @endcan
                    @can('accounts.payments.view')
                    <a href="{{ route('admin.accounts.payments.index') }}" class="{{ request()->routeIs('admin.accounts.payments.*') ? 'active' : '' }}">Payments</a>
                    @endcan
                    @can('accounts.settlements.manage')
                    <a href="{{ route('admin.accounts.settlements.index') }}" class="{{ request()->routeIs('admin.accounts.settlements.*') ? 'active' : '' }}">Pending payments</a>
                    @endcan
                    @can('accounts.ledger.view')
                    <a href="{{ route('admin.accounts.ledger.index') }}" class="{{ request()->routeIs('admin.accounts.ledger.*') ? 'active' : '' }}">Ledger</a>
                    @endcan
                    @can('accounts.commissions.view')
                    <a href="{{ route('admin.accounts.commissions.index') }}" class="{{ request()->routeIs('admin.accounts.commissions.*') ? 'active' : '' }}">Sales Commissions</a>
                    @endcan
                    @can('accounts.payment-accounts.manage')
                    <a href="{{ route('admin.accounts.wallets.index') }}" class="{{ request()->routeIs('admin.accounts.wallets.*') ? 'active' : '' }}">Wallets</a>
                    @endcan
                    @canany(['accounts.expenses.view-own', 'accounts.expenses.view-all'])
                    <a href="{{ route('admin.accounts.expenses.index') }}" class="{{ request()->routeIs('admin.accounts.expenses.*') ? 'active' : '' }}">Expenses</a>
                    @endcanany
                    @can('accounts.reports.view')
                    <a href="{{ route('admin.accounts.reports.index') }}" class="{{ request()->routeIs('admin.accounts.reports.*') ? 'active' : '' }}">Finance Reports</a>
                    @endcan
                </div>
            </div>
            @endcanany

            @can('hr.dashboard.view')
            <div class="nav-group" data-group="hr">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Human Resources</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    <a href="{{ route('admin.hr.dashboard') }}" class="{{ request()->routeIs('admin.hr.dashboard') ? 'active' : '' }}">HR Dashboard</a>
                    @can('hr.employees.view')
                    <a href="{{ route('admin.hr.employees.index') }}" class="{{ request()->routeIs('admin.hr.employees.*') ? 'active' : '' }}">Employees</a>
                    @endcan
                    @canany(['hr.leave.view-own', 'hr.leave.approve'])
                    <a href="{{ route('admin.hr.leave.index') }}" class="{{ request()->routeIs('admin.hr.leave.*') ? 'active' : '' }}">Leave</a>
                    @endcanany
                    @canany(['hr.attendance.view', 'hr.attendance.manage'])
                    <a href="{{ route('admin.hr.attendance.index') }}" class="{{ request()->routeIs('admin.hr.attendance.*') ? 'active' : '' }}">Attendance</a>
                    @endcanany
                    @can('hr.payroll.view')
                    <a href="{{ route('admin.hr.payroll.index') }}" class="{{ request()->routeIs('admin.hr.payroll.*') ? 'active' : '' }}">Payroll</a>
                    @endcan
                    @can('hr.documents.view')
                    <a href="{{ route('admin.hr.documents.index') }}" class="{{ request()->routeIs('admin.hr.documents.*') ? 'active' : '' }}">Documents</a>
                    @endcan
                    @can('hr.employees.view')
                    <a href="{{ route('admin.hr.departments.index') }}" class="{{ request()->routeIs('admin.hr.departments.*') ? 'active' : '' }}">Departments</a>
                    @endcan
                    @can('hr.dashboard.view')
                    <a href="{{ route('admin.hr.reports.index') }}" class="{{ request()->routeIs('admin.hr.reports.*') ? 'active' : '' }}">HR Reports</a>
                    @endcan
                </div>
            </div>
            @endcan

            @canany(['cms.blogs.view', 'cms.portfolios.view', 'cms.showcases.view', 'cms.services.view', 'cms.sections.view', 'cms.media.view'])
            <div class="nav-group" data-group="content">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Content</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    @can('cms.blogs.view')
                    <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h10"/></svg>
                        Blogs
                    </a>
                    @endcan
                    @can('cms.portfolios.view')
                    <a href="{{ route('admin.portfolios.index') }}" class="{{ request()->routeIs('admin.portfolios.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Portfolio
                    </a>
                    @endcan
                    @can('cms.showcases.view')
                    <a href="{{ route('admin.showcases.index') }}" class="{{ request()->routeIs('admin.showcases.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                        Showcase
                    </a>
                    @endcan
                    @can('cms.services.view')
                    <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Services
                    </a>
                    @endcan
                    @can('cms.sections.view')
                    <a href="{{ route('admin.sections.index') }}" class="{{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Page Content
                    </a>
                    @endcan
                    @can('cms.media.view')
                    <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2h4a2 2 0 012 2h4a2 2 0 012 2v10a2 2 0 01-2 2H5z"/><circle cx="12" cy="13" r="3"/></svg>
                        Media Library
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            @can('cms.messages.view')
            <div class="nav-group" data-group="communication">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Communication</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Contact Inbox
                        @if(($unreadCount ?? 0) > 0)
                            <span class="badge badge-unread" style="margin-left:auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
            @endcan

            @canany(['system.users.view', 'system.roles.view', 'system.invitations.manage', 'system.activity.view', 'system.stripe.manage'])
            <div class="nav-group" data-group="system">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>System</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    @can('system.users.view')
                    <a href="{{ route('admin.system.users.index') }}" class="{{ request()->routeIs('admin.system.users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                    @endcan
                    @can('system.roles.view')
                    <a href="{{ route('admin.system.roles.index') }}" class="{{ request()->routeIs('admin.system.roles.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Roles
                    </a>
                    @endcan
                    @can('system.invitations.manage')
                    <a href="{{ route('admin.system.invitations.index') }}" class="{{ request()->routeIs('admin.system.invitations.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Invitations
                    </a>
                    @endcan
                    @can('system.activity.view')
                    <a href="{{ route('admin.system.activity.index') }}" class="{{ request()->routeIs('admin.system.activity.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Activity Log
                    </a>
                    @endcan
                    @can('system.stripe.manage')
                    <a href="{{ route('admin.system.stripe.payments') }}" class="{{ request()->routeIs('admin.system.stripe.payments*') ? 'active' : '' }}">Card payments</a>
                    @endcan
                </div>
            </div>
            @endcanany

            @can('cms.settings.view')
            <div class="nav-group" data-group="settings">
                <button type="button" class="nav-group-toggle" aria-expanded="false">
                    <span>Settings</span>
                    <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="nav-group-body">
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Site Settings
                    </a>
                    <a href="{{ route('admin.settings.chat') }}" class="{{ request()->routeIs('admin.settings.chat') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Chat Support
                    </a>
                    <a href="{{ route('admin.settings.google') }}" class="{{ request()->routeIs('admin.settings.google*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                        Google Services
                    </a>
                    @can('system.stripe.manage')
                    <a href="{{ route('admin.system.stripe.edit') }}" class="{{ request()->routeIs('admin.system.stripe.edit') ? 'active' : '' }}">Stripe</a>
                    @endcan
                </div>
            </div>
            @endcan
        </nav>
        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Toggle menu">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="topbar-logo-link">
                    <img src="{{ asset('img/pixels-soft-logo.png') }}" alt="PixelsSoft" class="topbar-logo">
                </a>
                <h1>@yield('title', 'Admin')</h1>
            </div>
            <div class="topbar-actions">
                <a href="{{ route('admin.notifications.index') }}" class="notif-bell" title="Notifications">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
                    @if(($adminNotificationCount ?? 0) > 0)
                        <span class="notif-count">{{ $adminNotificationCount > 9 ? '9+' : $adminNotificationCount }}</span>
                    @endif
                </a>
                <a href="{{ $frontendUrl }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Website
                </a>
                <a href="{{ route('admin.system.profile.edit') }}" class="btn btn-outline btn-sm">Profile</a>
                <span style="font-size:13px;color:#6b7280;">{{ auth()->user()->name ?? auth()->user()->email }}</span>
            </div>
        </header>
        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @include('admin.partials.errors')
            @yield('content')
        </main>
    </div>
</div>
<script>
(function () {
    var STORAGE_KEY = 'ps_admin_nav_groups';
    var nav = document.getElementById('sidebar-nav');
    if (!nav) return;

    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {}; } catch (e) {}

    nav.querySelectorAll('.nav-group').forEach(function (group) {
        var key = group.getAttribute('data-group');
        var toggle = group.querySelector('.nav-group-toggle');
        var hasActive = !!group.querySelector('a.active');
        var open = hasActive || saved[key] === true || (saved[key] === undefined && key === 'overview');

        function setOpen(isOpen) {
            group.classList.toggle('is-open', isOpen);
            if (toggle) toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            saved[key] = isOpen;
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(saved)); } catch (e) {}
        }

        setOpen(open);

        if (toggle) {
            toggle.addEventListener('click', function () {
                setOpen(!group.classList.contains('is-open'));
            });
        }
    });
})();
</script>
</body>
</html>
