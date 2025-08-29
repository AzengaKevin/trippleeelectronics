<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_about_us_route_happy_path()
    {
        $response = $this->get(route('about-us'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_cart_route_happy_path()
    {
        $response = $this->get(route('cart'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_claiming_warranty_route_happy_path()
    {
        $response = $this->get(route('claiming-warranty'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_contact_us_route_happy_path()
    {
        $response = $this->get(route('contact-us'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_contact_us_route_store_happy_path()
    {
        $response = $this->post(route('contact-us'), $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'message' => $this->faker->paragraph(),
        ]);

        $response->assertRedirect(route('contact-us'));

        $this->assertDatabaseHas('contacts', [
            'name' => data_get($payload, 'name'),
            'email' => data_get($payload, 'email'),
            'phone' => data_get($payload, 'phone'),
            'message' => data_get($payload, 'message'),
        ]);
    }

    public function test_placing_order_route_happy_path()
    {
        $response = $this->get(route('placing-order'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_privacy_policy_route_happy_path()
    {
        $response = $this->get(route('privacy-policy'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_return_policy_route_happy_path()
    {
        $response = $this->get(route('return-policy'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_refund_policy_route_happy_path()
    {
        $response = $this->get(route('refund-policy'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_services_route_happy_path()
    {
        $response = $this->get(route('services'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_terms_of_service_route_happy_path()
    {
        $response = $this->get(route('terms-of-service'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }

    public function test_track_your_order_route_happy_path()
    {
        $response = $this->get(route('track-your-order'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page->hasAll(['settings', 'services', 'categories', 'treeCategories']));
    }
}
