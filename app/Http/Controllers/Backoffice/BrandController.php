<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\BrandExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\BrandImport;
use App\Models\Brand;
use App\Services\BrandService;
use App\Services\ExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $brands = $this->brandService->get(...$params);

        return Inertia::render('backoffice/brands/IndexPage', compact('brands', 'params'));
    }

    public function create()
    {
        return Inertia::render('backoffice/brands/CreatePage');
    }

    public function store(StoreBrandRequest $storeBrandRequest): RedirectResponse
    {
        $data = $storeBrandRequest->validated();

        /** @var User $currentUser */
        $currentUser = request()->user();

        try {

            $data['author_user_id'] = $currentUser->id;

            $this->brandService->create($data);

            return $this->sendSuccessRedirect('Brand created successfully.', route('backoffice.brands.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Brand creation failed.',
                $throwable
            );
        }
    }

    public function show(Brand $brand)
    {
        $brand->image_url = $brand->getFirstMediaUrl();

        $brand->load('author');

        return Inertia::render('backoffice/brands/ShowPage', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return Inertia::render('backoffice/brands/EditPage', compact('brand'));
    }

    public function update(UpdateBrandRequest $updateBrandRequest, Brand $brand): RedirectResponse
    {
        $data = $updateBrandRequest->validated();

        try {

            $this->brandService->update($brand, $data);

            return $this->sendSuccessRedirect('Brand updated successfully.', route('backoffice.brands.show', $brand));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Brand update failed.',
                $throwable
            );
        }
    }

    public function destroy(Request $request, string $brand): RedirectResponse
    {
        try {

            $brand = Brand::findOrFail($brand);

            $this->brandService->delete($brand, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Brand deleted successfully.', route('backoffice.brands.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Brand deletion failed.',
                $throwable
            );
        }
    }

    public function export(Request $request)
    {

        $data = $request->only('query', 'limit');

        $brandExport = new BrandExport($data);

        return $brandExport->download(Brand::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/brands/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new BrandImport, $data['file'], 'brands', 'brands');

            return $this->sendSuccessRedirect('Imported brands', route('backoffice.brands.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import brands', $throwable);
        }
    }
}
