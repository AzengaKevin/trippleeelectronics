<?php

namespace Database\Seeders;

use App\Models\SourceChannel;
use Illuminate\Database\Seeder;

class SourceChannelSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Airbnb',
            'Booking.com',
            'Expedia',
            'Agoda',
            'TripAdvisor',
            'Travel agents',
            'Corporate bookings',
            'Walk-ins',
            'Referrals / Word of mouth',
            'Own website / Direct bookings',
        ])->each(
            fn ($name) => SourceChannel::query()->updateOrCreate(compact('name'))
        );
    }
}
