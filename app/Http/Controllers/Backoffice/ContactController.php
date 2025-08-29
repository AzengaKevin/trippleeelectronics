<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private ContactService $contactService) {}

    public function index(Request $request)
    {
        $params = $request->only('query');

        $contacts = $this->contactService->get(...$params);

        return inertia('backoffice/contacts/IndexPage', [
            'contacts' => $contacts,
            'params' => $params,
        ]);
    }

    public function show(Contact $contact)
    {
        return inertia('backoffice/contacts/ShowPage', [
            'contact' => $contact,
        ]);
    }

    public function destroy(Contact $contact): RedirectResponse
    {

        try {

            $this->contactService->delete($contact, request()->boolean('forever', false));

            return $this->sendSuccessRedirect('Contact deleted successfully.', route('backoffice.contacts.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'An error occurred while deleting the contact',
                $throwable,
            );
        }
    }
}
