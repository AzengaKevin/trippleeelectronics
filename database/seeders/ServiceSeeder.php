<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {

        collect(
            [
                [
                    'title' => 'Computer Repair',
                    'description' => 'Repairing all types of computers including desktops, laptops, and workstations.',
                ],
                [
                    'title' => 'Virus and Malware Removal',
                    'description' => 'Cleaning up computers infected with viruses, malware, spyware, and ransomware.',
                ],
                [
                    'title' => 'Data Recovery',
                    'description' => 'Recovering lost or corrupted data from hard drives, SSDs, and other storage media.',
                ],
                [
                    'title' => 'Network Setup and Configuration',
                    'description' => 'Setting up and configuring both wired and wireless networks for home or business.',
                ],
                [
                    'title' => 'Software Installation and Support',
                    'description' => 'Installing and providing support for a wide range of software applications.',
                ],
                [
                    'title' => 'Hardware Upgrades',
                    'description' => 'Upgrading hardware components such as RAM, storage drives, and graphics cards.',
                ],
                [
                    'title' => 'IT Consultancy',
                    'description' => 'Providing expert IT consultancy services for businesses to improve their infrastructure.',
                ],
                [
                    'title' => 'Cloud Services',
                    'description' => 'Assisting businesses and individuals with cloud migration and services like storage and computing.',
                ],
                [
                    'title' => 'Custom Computer Builds',
                    'description' => 'Building custom computers tailored to specific user needs, from gaming PCs to workstations.',
                ],
                [
                    'title' => 'Cybersecurity Services',
                    'description' => 'Providing cybersecurity solutions including firewalls, antivirus, and secure networking.',
                ],
                [
                    'title' => 'Backup Solutions',
                    'description' => 'Setting up automated and secure backup solutions to protect data from loss or corruption.',
                ],
                [
                    'title' => 'Remote IT Support',
                    'description' => 'Providing remote IT support to troubleshoot and fix issues without needing a physical visit.',
                ],
            ]

        )->each(function ($item) {

            Service::query()->updateOrCreate(
                ['title' => data_get($item, 'title')],
                ['description' => data_get($item, 'description')],
            );
        });
    }
}
