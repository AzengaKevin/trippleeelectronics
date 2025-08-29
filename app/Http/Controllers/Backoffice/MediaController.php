<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private readonly MediaService $mediaService) {}

    public function index(Request $request)
    {
        $params = $request->only('query');

        $media = $this->mediaService->get(...$params);

        return Inertia::render('backoffice/media/IndexPage', compact('media', 'params'));
    }

    public function destroy(Media $media)
    {
        try {

            $this->mediaService->delete($media);

            return $this->sendSuccessRedirect("You've successfully deleted a media instance", route('backoffice.media.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed deleting a media instance', $throwable);
        }
    }
}
