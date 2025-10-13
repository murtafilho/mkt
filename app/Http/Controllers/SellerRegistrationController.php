<?php

namespace App\Http\Controllers;

use App\Http\Requests\Seller\StoreSellerRequest;
use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Support\Facades\Auth;

class SellerRegistrationController extends Controller
{
    public function __construct(
        private SellerService $sellerService
    ) {}

    /**
     * Show seller registration form.
     */
    public function create()
    {
        // Check if user already has a seller profile
        if (Auth::user()->seller) {
            return redirect()->route('seller.dashboard')
                ->with('info', 'Você já possui um perfil de vendedor.');
        }

        return view('seller.register');
    }

    /**
     * Store seller registration.
     */
    public function store(StoreSellerRequest $request)
    {
        try {
            // DEBUG: Log request data
            \Log::info('Seller Registration - Request Data:', [
                'all' => $request->all(),
                'files' => $request->allFiles(),
                'hasLogo' => $request->hasFile('logo'),
                'hasBanner' => $request->hasFile('banner'),
            ]);

            $seller = $this->sellerService->createSeller(Auth::user(), $request->validated());

            // Handle logo upload with crop
            if ($request->filled('logo_crop_data')) {
                $cropData = json_decode($request->input('logo_crop_data'), true);

                // The file input with name="logo" is added by JavaScript after crop
                if ($request->hasFile('logo')) {
                    $seller->addMedia($request->file('logo'))
                        ->withManipulations([
                            'crop' => [
                                'width' => (int) $cropData['width'],
                                'height' => (int) $cropData['height'],
                                'x' => (int) $cropData['x'],
                                'y' => (int) $cropData['y'],
                            ],
                        ])
                        ->toMediaCollection('seller_logo');
                }
            } elseif ($request->hasFile('logo')) {
                // Handle direct upload without crop (for testing or simple uploads)
                $seller->addMedia($request->file('logo'))
                    ->toMediaCollection('seller_logo');
            }

            // Handle banner upload with crop
            if ($request->filled('banner_crop_data')) {
                $cropData = json_decode($request->input('banner_crop_data'), true);

                if ($request->hasFile('banner')) {
                    $seller->addMedia($request->file('banner'))
                        ->withManipulations([
                            'crop' => [
                                'width' => (int) $cropData['width'],
                                'height' => (int) $cropData['height'],
                                'x' => (int) $cropData['x'],
                                'y' => (int) $cropData['y'],
                            ],
                        ])
                        ->toMediaCollection('seller_banner');
                }
            } elseif ($request->hasFile('banner')) {
                // Handle direct upload without crop (for testing or simple uploads)
                $seller->addMedia($request->file('banner'))
                    ->toMediaCollection('seller_banner');
            }

            // Redirect to dashboard - seller can access before approval
            return redirect()->route('seller.dashboard')
                ->with('success', 'Cadastro realizado com sucesso! Você já pode começar a cadastrar produtos. Após a aprovação do administrador, seu perfil e produtos ficarão visíveis ao público.');
        } catch (\Exception $e) {
            \Log::error('Seller Registration Failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar: '.$e->getMessage());
        }
    }

    /**
     * Show public seller page.
     */
    public function show(string $slug)
    {
        $seller = Seller::where('slug', $slug)
            ->approved()
            ->with('media') // Eager load seller media (logo, banner)
            ->firstOrFail();

        // Get published products with pagination
        $products = $seller->products()
            ->where('status', 'published')
            ->with(['category', 'media'])
            ->latest()
            ->paginate(12);

        return view('sellers.show', compact('seller', 'products'));
    }
}
