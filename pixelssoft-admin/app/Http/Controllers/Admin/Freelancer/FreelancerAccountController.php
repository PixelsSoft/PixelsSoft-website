<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Jobs\Freelancer\SyncFreelancerPortfolioJob;
use App\Jobs\Freelancer\SyncFreelancerProfileJob;
use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerApiLog;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Services\Freelancer\FreelancerAccountResolver;
use App\Services\Freelancer\FreelancerAuthService;
use App\Services\Freelancer\FreelancerProfileService;
use App\Services\Freelancer\FreelancerSettingsService;
use Throwable;

class FreelancerAccountController extends Controller
{
    public function index(FreelancerAccountResolver $resolver, FreelancerAuthService $auth, FreelancerSettingsService $settingsService)
    {
        $account = $resolver->firstOrCreateForUser()->load(['settings']);
        $configured = $auth->isConfigured($account);
        $settings = $settingsService->forAccount($account);
        $recentLogs = FreelancerApiLog::where('freelancer_account_id', $account->id)->latest()->limit(10)->get();

        return view('admin.freelancer.account.index', compact('account', 'configured', 'settings', 'recentLogs'));
    }

    public function redirect(FreelancerAccountResolver $resolver, FreelancerAuthService $auth)
    {
        $account = $resolver->firstOrCreateForUser();
        if (!$auth->isConfigured($account)) {
            return back()->with('error', 'Configure Freelancer API settings first.');
        }

        $state = $auth->makeState();
        session([
            'freelancer_oauth_state' => $state,
            'freelancer_oauth_account_id' => $account->id,
        ]);

        return redirect()->away($auth->authorizationUrl($state, $account));
    }

    public function callback(\Illuminate\Http\Request $request, FreelancerAuthService $auth, FreelancerProfileService $profiles)
    {
        if ($request->string('state')->toString() !== session('freelancer_oauth_state')) {
            return redirect()->route('admin.freelancer.account.index')->with('error', 'Invalid OAuth state.');
        }
        if ($request->filled('error')) {
            return redirect()->route('admin.freelancer.account.index')->with('error', 'Freelancer authorization denied: '.$request->string('error')->toString());
        }

        $code = $request->string('code')->toString();
        if ($code === '') {
            return redirect()->route('admin.freelancer.account.index')->with('error', 'Missing authorization code.');
        }

        $account = FreelancerAccount::with('settings')->findOrFail(session('freelancer_oauth_account_id'));
        $owned = app(FreelancerAccountResolver::class)->firstOrCreateForUser();
        if ($account->id !== $owned->id) {
            return redirect()->route('admin.freelancer.account.index')->with('error', 'OAuth account mismatch.');
        }

        try {
            $tokens = $auth->exchangeCode($account, $code);
            $auth->storeTokens($account, $tokens);
            $profiles->sync($account->fresh());

            FreelancerAuditLog::create([
                'user_id' => auth()->id(),
                'freelancer_account_id' => $account->id,
                'action' => 'account.connected',
                'entity_type' => FreelancerAccount::class,
                'entity_id' => $account->id,
                'new_value' => [
                    'freelancer_user_id' => $account->fresh()->freelancer_user_id,
                    'username' => $account->fresh()->username,
                ],
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.freelancer.account.index')->with('error', 'OAuth failed: '.$e->getMessage());
        } finally {
            session()->forget(['freelancer_oauth_state', 'freelancer_oauth_account_id']);
        }

        return redirect()->route('admin.freelancer.account.index')->with('success', 'Freelancer account connected.');
    }

    public function disconnect(FreelancerAccountResolver $resolver, FreelancerAuthService $auth)
    {
        $account = $resolver->current();
        if ($account) {
            $auth->disconnect($account, auth()->id());
        }

        return back()->with('success', 'Freelancer account disconnected.');
    }

    public function test(FreelancerAccountResolver $resolver, FreelancerProfileService $profiles)
    {
        $account = $resolver->current();
        if (!$account?->is_connected) {
            return back()->with('error', 'No connected account.');
        }

        try {
            $result = $profiles->testConnection($account);
            $account->forceFill(['last_connection_test_at' => now(), 'last_connection_test_ok' => true])->save();
            return back()->with('success', 'Connection OK - '.($result['username'] ?? ('user '.($result['freelancer_user_id'] ?? 'unknown'))));
        } catch (Throwable $e) {
            $account->forceFill(['last_connection_test_at' => now(), 'last_connection_test_ok' => false])->save();
            return back()->with('error', 'Connection failed: '.$e->getMessage());
        }
    }

    public function syncProfile(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        if (!$account?->is_connected) {
            return back()->with('error', 'No connected account.');
        }

        SyncFreelancerProfileJob::dispatch($account->id);
        SyncFreelancerPortfolioJob::dispatch($account->id);

        return back()->with('success', 'Profile and portfolio sync queued.');
    }
}
