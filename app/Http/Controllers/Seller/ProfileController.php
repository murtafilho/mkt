<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\UpdateSellerRequest;
use App\Services\SellerService;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProfileController extends Controller
{
    public function __construct(
        private SellerService $sellerService
    ) {}

    /**
     * Show seller profile preview.
     */
    public function show()
    {
        $seller = Auth::user()->seller;

        if (! $seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Você precisa criar um perfil de vendedor primeiro.');
        }

        return redirect()->route('seller.profile.edit');
    }

    /**
     * Show seller profile edit form.
     */
    public function edit()
    {
        $seller = Auth::user()->seller;

        if (! $seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Você precisa criar um perfil de vendedor primeiro.');
        }

        return view('seller.profile.edit', compact('seller'));
    }

    /**
     * Update seller profile.
     */
    public function update(UpdateSellerRequest $request)
    {
        /** @var \App\Models\Seller|null $seller */
        $seller = Auth::user()->seller;

        if (! $seller) {
            return redirect()->route('seller.register');
        }

        try {
            $this->sellerService->updateSeller($seller, $request->validated());

            // Get validated data
            $validated = $request->validated();

            // Update or create business address
            $seller->addresses()->updateOrCreate(
                ['type' => 'business'],
                [
                    'postal_code' => $validated['postal_code'] ?? '',
                    'street' => $validated['street'] ?? '',
                    'number' => $validated['number'] ?? '',
                    'complement' => $validated['complement'] ?? null,
                    'neighborhood' => $validated['neighborhood'] ?? '',
                    'city' => $validated['city'] ?? '',
                    'state' => $validated['state'] ?? '',
                    'is_default' => true,
                ]
            );

            // Handle logo deletion
            if ($request->input('logo_delete')) {
                $seller->clearMediaCollection('seller_logo');
            }
            // Handle logo upload with crop
            elseif ($request->has('logo_crop_data') && $request->hasFile('logo')) {
                $cropData = json_decode($request->input('logo_crop_data'), true);

                $seller->clearMediaCollection('seller_logo');
                $logoFile = $request->file('logo');
                $seller->addMedia($logoFile)
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

            // Handle banner deletion
            if ($request->input('banner_delete')) {
                $seller->clearMediaCollection('seller_banner');
            }
            // Handle banner upload with crop
            elseif ($request->has('banner_crop_data') && $request->hasFile('banner')) {
                $cropData = json_decode($request->input('banner_crop_data'), true);

                $seller->clearMediaCollection('seller_banner');
                $bannerFile = $request->file('banner');
                $seller->addMedia($bannerFile)
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

            return redirect()->route('seller.profile.edit')->with('success', 'Perfil atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a seller profile image (logo or banner).
     */
    public function deleteImage(Media $media)
    {
        /** @var \App\Models\Seller|null $seller */
        $seller = Auth::user()->seller;

        if (! $seller) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de vendedor não encontrado.',
            ], 404);
        }

        // Verify image belongs to this seller
        if ($media->model_id !== $seller->id || $media->model_type !== get_class($seller)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta imagem não pertence ao seu perfil.',
            ], 403);
        }

        // Only allow deletion of logo and banner
        if (! in_array($media->collection_name, ['seller_logo', 'seller_banner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de imagem inválido.',
            ], 403);
        }

        try {
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Imagem removida com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover imagem.',
            ], 500);
        }
    }
}
