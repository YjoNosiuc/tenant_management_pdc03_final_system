<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerTerms;
use App\Models\TenantTermAgreement;
use App\Support\DefaultRentalTerms;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $terms = OwnerTerms::where('owner_id', auth()->id())->first();

        $agreedCount = 0;
        if ($terms) {
            $agreedCount = TenantTermAgreement::query()
                ->where('owner_id', auth()->id())
                ->where('version', $terms->version)
                ->count();
        }

        $defaultContent = trim(DefaultRentalTerms::content());

        return view('admin.settings.index', compact('terms', 'agreedCount', 'defaultContent'));
    }

    public function updateTerms(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string', 'min:100'],
        ]);

        $existing = OwnerTerms::where('owner_id', auth()->id())->first();

        if ($existing) {
            $existing->update([
                'content' => $request->content,
                'version' => $existing->version + 1,
            ]);
        } else {
            OwnerTerms::create([
                'owner_id' => auth()->id(),
                'content' => $request->content,
                'version' => 1,
            ]);
        }

        return back()->with('success',
            'Terms & Conditions updated. All tenants will be required to re-agree.');
    }
}
