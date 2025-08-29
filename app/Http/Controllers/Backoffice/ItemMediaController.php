<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Item;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ItemMediaController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private readonly MediaService $mediaService) {}

    public function index(Item $item, Request $request)
    {

        $params = $request->only('query');

        $media = $this->mediaService->get(...$params, model: $item);

        return Inertia::render('backoffice/items/media/IndexPage', compact('media', 'item'));
    }

    public function destroy(Item $item, Media $media)
    {
        abort_unless($item->id === $media->model_id, 403);

        try {

            $this->mediaService->delete($media);

            return $this->sendSuccessRedirect('Deleted item media', route('backoffice.items.media.index', $item));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed deleting item media', $throwable);
        }
    }
}
