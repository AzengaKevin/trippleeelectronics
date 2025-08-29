<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Services\ContactService;
use App\Services\ItemCategoryService;
use App\Services\ItemService;
use App\Services\ServiceService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ItemCategoryService $itemCategoryService,
        private readonly ServiceService $serviceService,
        private readonly ItemService $itemService,
        private readonly ContactService $contactService,
    ) {}

    public function aboutUs(): Response
    {
        return Inertia::render('AboutUsPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function cart(): Response
    {
        return Inertia::render('CartPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function claimingWarranty(): Response
    {
        return Inertia::render('ClaimingWarrantyPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function contactUs(): Response
    {
        return Inertia::render('ContactUsPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function contactUsStore(StoreContactRequest $request): RedirectResponse
    {

        $data = $request->validated();

        try {

            $this->contactService->create($data);

            return $this->sendSuccessRedirect('Your message has been sent successfully.', route('contact-us'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while sending your message. Please try again later.', $throwable);
        }
    }

    public function placingOrder(): Response
    {
        return Inertia::render('PlacingOrderPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function privacyPolicy(): Response
    {
        return Inertia::render('PrivacyPolicyPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function returnPolicy(): Response
    {
        return Inertia::render('ReturnPolicyPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function refundPolicy(): Response
    {
        return Inertia::render('RefundPolicyPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('ServicesPage', [
            ...$this->getStandardData(),
        ]);
    }

    public function termsOfService(): Response
    {
        return Inertia::render('TermsOfServicePage', [
            ...$this->getStandardData(),
        ]);
    }

    public function trackYourOrder(): Response
    {
        $params = request()->only('query');

        $order = null;

        if ($query = trim(data_get($params, 'query'))) {

            $order = Order::query()->where('reference', $query)->first();
        }

        return Inertia::render('TrackYourOrderPage', [
            ...$this->getStandardData(),
            'order' => $order,
            'params' => $params,
        ]);
    }

    public function wishlist(): Response
    {
        return Inertia::render('WishlistPage', [
            ...$this->getStandardData(),
        ]);
    }

    private function getStandardData(): array
    {

        $categories = $this->itemCategoryService->get(perPage: null, limit: 20)->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $category->getFirstMediaUrl(),
            ];
        });

        $settings = $this->settingsService->get();

        $treeCategories = ItemCategory::get()->toTree();

        $services = $this->serviceService->get(perPage: null, limit: 10)->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image_url' => $service->getFirstMediaUrl(),
            ];
        });

        return [
            'categories' => $categories,
            'settings' => $settings,
            'treeCategories' => $treeCategories,
            'services' => $services,
        ];
    }
}
