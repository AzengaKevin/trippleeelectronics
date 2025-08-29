<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-activities',
                'read-activities',
                'delete-activities',
            ]
        );
    }

    public function test_backoffice_activities_index_route(): void
    {
        activity()->log($this->faker->sentence());

        activity()->log($this->faker->sentence());

        $response = $this->actingAs($this->user)->get(route('backoffice.activities.index'));

        $response->assertSuccessful();

        $response->assertInertia(function (AssertableInertia $assertableInertia) {
            $assertableInertia->component('backoffice/activities/IndexPage')
                ->hasAll(['activities', 'params', 'causers']);
        });
    }

    public function test_backoffice_activities_show_route(): void
    {
        $activity = activity()->log($this->faker->sentence());

        $response = $this->actingAs($this->user)->get(route('backoffice.activities.show', $activity));

        $response->assertSuccessful();

        $response->assertInertia(function (AssertableInertia $assertableInertia) {
            $assertableInertia->component('backoffice/activities/ShowPage')
                ->hasAll(['activity']);
        });
    }

    public function test_backoffice_activities_destroy_route(): void
    {
        $activity = activity()->log($this->faker->sentence());

        $response = $this->actingAs($this->user)->delete(route('backoffice.activities.destroy', $activity));

        $this->assertModelMissing($activity);

        $response->assertRedirect(route('backoffice.activities.index'));
    }
}
