<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\CarouselExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreCarouselRequest;
use App\Http\Requests\UpdateCarouselRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\CarouselImport;
use App\Models\Carousel;
use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use App\Services\CarouselService;
use App\Services\ExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarouselController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private CarouselService $carouselService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'position', 'orientation', 'limit');

        $positions = PositionOption::labelledOptions();

        $orientations = OrientationOption::labelledOptions();

        $carousels = $this->carouselService->get(...$params)
            ->through(fn (Carousel $carousel) => [
                ...$carousel->only('id', 'title', 'description', 'link', 'slug', 'position', 'orientation'),
                'image_url' => $carousel->getFirstMediaUrl(),
            ]);

        return Inertia::render('backoffice/carousels/IndexPage', compact('carousels', 'params', 'positions', 'orientations'));
    }

    public function create()
    {
        return Inertia::render('backoffice/carousels/CreatePage', [
            'orientationOptions' => OrientationOption::labelledOptions(),
            'positionOptions' => PositionOption::labelledOptions(),
        ]);
    }

    public function store(StoreCarouselRequest $storeCarouselRequest): RedirectResponse
    {
        $data = $storeCarouselRequest->validated();

        /** @var User $currentUser */
        $currentUser = request()->user();

        $data['author_user_id'] = $currentUser->id;

        try {
            $this->carouselService->create($data);

            return $this->sendSuccessRedirect('Created a new carousel', route('backoffice.carousels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to create a carousel', $throwable);
        }
    }

    public function show(Carousel $carousel): Response
    {
        $carousel->load('author');

        $carousel->image_url = $carousel->getFirstMediaUrl();

        return Inertia::render('backoffice/carousels/ShowPage', compact('carousel'));
    }

    public function edit(Carousel $carousel): Response
    {
        return Inertia::render('backoffice/carousels/EditPage', [
            'carousel' => $carousel,
            'orientationOptions' => OrientationOption::labelledOptions(),
            'positionOptions' => PositionOption::labelledOptions(),
        ]);
    }

    public function update(UpdateCarouselRequest $updateCarouselRequest, Carousel $carousel)
    {
        $data = $updateCarouselRequest->validated();

        try {
            $this->carouselService->update($carousel, $data);

            return $this->sendSuccessRedirect('Updated a carousel', route('backoffice.carousels.show', $carousel));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to update a carousel', $throwable);
        }
    }

    public function destroy(Request $request, string $carousel): RedirectResponse
    {
        try {

            $forever = $request->boolean('forever');

            $carousel = Carousel::query()->findOrFail($carousel);

            $this->carouselService->delete($carousel, $forever);

            return $this->sendSuccessRedirect('Deleted a carousel', route('backoffice.carousels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to delete a carousel', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'limit');

        $carouselExport = new CarouselExport(...$params);

        return $carouselExport->download(Carousel::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/carousels/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {
            $this->robustImport(new CarouselImport, $data['file'], 'carousels', 'carousels');

            return $this->sendSuccessRedirect('Imported carousels', route('backoffice.carousels.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import carousels', $throwable);
        }
    }
}
