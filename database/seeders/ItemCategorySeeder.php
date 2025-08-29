<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories_data = [

            [
                'name' => 'Computers & Accessories',
                'description' => 'Desktops, laptops, and computer accessories',
                'children' => [
                    ['name' => 'Laptops', 'description' => 'All types of laptops', 'children' => []],
                    [
                        'name' => 'Desktops',
                        'description' => 'Various desktop computers',
                        'children' => [],
                    ],
                    [
                        'name' => 'Computer Accessories',
                        'description' => 'Keyboards, mice, and other peripherals',
                        'children' => [
                            [
                                'name' => 'Keyboards',
                                'description' => 'Mechanical, membrane, wireless keyboards',
                                'children' => [],
                            ],
                            [
                                'name' => 'Mice',
                                'description' => 'Wired, wireless, and gaming mice',
                                'children' => [],
                            ],
                            [
                                'name' => 'Monitors',
                                'description' => 'LCD, LED, and gaming monitors',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Printers & Projectors',
                'description' => '',
                'children' => [
                    [
                        'name' => 'Printers',
                        'description' => 'Devices that produce hard copies of digital documents, images, and graphics.',
                        'children' => [
                            [
                                'name' => 'Inkjet Printers',
                                'description' => 'Printers that use ink cartridges to spray tiny droplets of ink onto paper, ideal for high-quality color prints.',
                            ],
                            [
                                'name' => 'Laser Printers',
                                'description' => 'Printers that use laser technology to produce sharp, high-speed black-and-white or color prints.',
                            ],
                            [
                                'name' => 'All-in-One Printers',
                                'description' => 'Multifunction devices that combine printing, scanning, copying, and faxing capabilities in one unit.',
                            ],
                            [
                                'name' => 'Photo Printers',
                                'description' => 'Specialized printers designed to produce high-quality photographic prints.',
                            ],
                            [
                                'name' => 'Dot Matrix Printers',
                                'description' => 'Impact printers that use a series of pins to create text and graphics on paper, often used for multi-part forms.',
                            ],
                            [
                                'name' => 'Thermal Printers',
                                'description' => 'Printers that use heat to transfer ink onto paper, commonly used for labels and receipts.',
                            ],
                            [
                                'name' => 'Wide Format Printers',
                                'description' => 'Printers that can handle larger paper sizes for banners, posters, and other large-scale prints.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Projectors',
                        'description' => 'Devices that project images and videos onto a surface, commonly used for presentations, entertainment, and home theaters.',
                        'children' => [
                            [
                                'name' => 'DLP Projectors',
                                'description' => 'Digital Light Processing projectors that use a digital micromirror device for high-quality images and brightness.',
                            ],
                            [
                                'name' => 'LCD Projectors',
                                'description' => 'Liquid Crystal Display projectors that utilize LCD technology for vibrant colors and sharp images.',
                            ],
                            [
                                'name' => 'LED Projectors',
                                'description' => 'Projectors that use LED light sources for longer lifespan and energy efficiency.',
                            ],
                            [
                                'name' => 'Laser Projectors',
                                'description' => 'Projectors that use lasers for light, offering high brightness and color accuracy.',
                            ],
                            [
                                'name' => 'Short Throw Projectors',
                                'description' => 'Projectors that can display large images from a short distance, ideal for small rooms.',
                            ],
                            [
                                'name' => 'Portable Projectors',
                                'description' => 'Compact and lightweight projectors designed for easy transport and setup, suitable for travel.',
                            ],
                            [
                                'name' => 'Home Theater Projectors',
                                'description' => 'High-definition projectors designed for cinematic experiences in home settings.',
                            ],
                            [
                                'name' => 'Business Projectors',
                                'description' => 'Projectors designed for presentations and meetings, often with features like wireless connectivity.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Network Devices',
                'description' => 'Devices that facilitate network connectivity and communication within home, office, or data center environments.',
                'children' => [
                    [
                        'name' => 'Routers',
                        'description' => 'Devices that forward data packets between computer networks, connecting different devices to the internet or local networks.',
                        'children' => [
                            [
                                'name' => 'Wireless Routers',
                                'description' => 'Routers that use Wi-Fi to connect devices wirelessly to the internet or network.',
                            ],
                            [
                                'name' => 'Wired Routers',
                                'description' => 'Routers that connect devices using physical Ethernet cables for stable, high-speed connections.',
                            ],
                            [
                                'name' => 'Mesh Wi-Fi Systems',
                                'description' => 'Systems consisting of multiple routers to provide seamless, whole-home Wi-Fi coverage.',
                            ],
                            [
                                'name' => 'Gaming Routers',
                                'description' => 'Routers optimized for low-latency gaming with features like QoS and high-speed wireless connections.',
                            ],
                            [
                                'name' => 'Enterprise Routers',
                                'description' => 'High-performance routers designed for large-scale, business-level networking.',
                            ],
                            [
                                'name' => 'Travel Routers',
                                'description' => 'Portable routers designed to provide internet access on the go, often in hotels or public places.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Switches',
                        'description' => 'Devices that connect multiple devices within a local network and direct data traffic efficiently.',
                        'children' => [
                            [
                                'name' => 'Unmanaged Switches',
                                'description' => 'Simple plug-and-play switches that require no configuration, suitable for small networks.',
                            ],
                            [
                                'name' => 'Managed Switches',
                                'description' => 'Switches that offer customizable settings for better control and security in larger networks.',
                            ],
                            [
                                'name' => 'PoE Switches',
                                'description' => 'Switches that provide Power over Ethernet (PoE) to devices like IP cameras and access points.',
                            ],
                            [
                                'name' => 'Gigabit Ethernet Switches',
                                'description' => 'Switches that support gigabit speeds for fast data transfer in high-performance networks.',
                            ],
                            [
                                'name' => 'Modular Switches',
                                'description' => 'Switches with customizable modules for flexible network expansion and functionality.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Modems',
                        'description' => 'Devices that modulate and demodulate signals for communication between internet service providers and local networks.',
                        'children' => [
                            [
                                'name' => 'Cable Modems',
                                'description' => 'Modems that use coaxial cables to connect to the internet through a cable provider.',
                            ],
                            [
                                'name' => 'DSL Modems',
                                'description' => 'Modems that connect to the internet via telephone lines using DSL technology.',
                            ],
                            [
                                'name' => 'Fiber Modems',
                                'description' => 'Modems that provide high-speed internet through fiber-optic cables.',
                            ],
                            [
                                'name' => 'Modem-Router Combos',
                                'description' => 'Devices that combine a modem and a router into a single unit for simplified home networking.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Access Points',
                        'description' => 'Devices that extend or create a wireless network, allowing more devices to connect wirelessly.',
                        'children' => [
                            [
                                'name' => 'Wi-Fi Access Points',
                                'description' => 'Devices that extend the range of a wireless network, providing additional coverage.',
                            ],
                            [
                                'name' => 'Outdoor Access Points',
                                'description' => 'Ruggedized access points designed for outdoor use to provide Wi-Fi in open areas.',
                            ],
                            [
                                'name' => 'Range Extenders/Boosters',
                                'description' => 'Devices that extend the range of an existing Wi-Fi network by amplifying the signal.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Network Adapters',
                        'description' => 'Devices that allow computers and other hardware to connect to a network.',
                        'children' => [
                            [
                                'name' => 'Wireless USB Adapters',
                                'description' => 'Small adapters that connect devices to wireless networks via USB ports.',
                            ],
                            [
                                'name' => 'PCIe Network Cards',
                                'description' => 'Expansion cards that provide high-speed wired or wireless network connectivity for desktop computers.',
                            ],
                            [
                                'name' => 'Powerline Adapters',
                                'description' => 'Devices that use electrical wiring to extend network connectivity throughout a home or office.',
                            ],
                            [
                                'name' => 'Ethernet Adapters',
                                'description' => 'Adapters that allow devices without Ethernet ports to connect via wired Ethernet.',
                            ],
                            [
                                'name' => 'Bluetooth Adapters',
                                'description' => 'Devices that enable Bluetooth connectivity on computers and other hardware.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Cables & Connectors',
                        'description' => 'Essential components for connecting network devices and ensuring reliable data transmission.',
                        'children' => [
                            [
                                'name' => 'Ethernet Cables',
                                'description' => 'Cables used to connect devices to a wired network, available in categories like Cat5, Cat6, and Cat7.',
                            ],
                            [
                                'name' => 'Fiber Optic Cables',
                                'description' => 'Cables that use light to transmit data at high speeds over long distances.',
                            ],
                            [
                                'name' => 'Coaxial Cables',
                                'description' => 'Cables used to connect modems and other devices to a cable-based network.',
                            ],
                            [
                                'name' => 'Network Connectors & Adapters',
                                'description' => 'Various connectors and adapters for establishing network connections between devices.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Antennas & Amplifiers',
                        'description' => 'Devices that improve network signal strength and coverage.',
                        'children' => [
                            [
                                'name' => 'Wi-Fi Antennas',
                                'description' => 'External antennas that boost the wireless signal of routers and access points.',
                            ],
                            [
                                'name' => 'Signal Boosters',
                                'description' => 'Devices that amplify network signals to cover larger areas.',
                            ],
                            [
                                'name' => 'Range Extenders',
                                'description' => 'Devices that extend the range of a network to reduce dead zones.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Security Systems',
                'description' => 'Systems and devices designed to protect IT infrastructure, networks, and data from unauthorized access and cyber threats.',
                'children' => [
                    [
                        'name' => 'CCTV Systems',
                        'description' => 'Closed-circuit television systems used for surveillance and security monitoring.',
                        'children' => [
                            [
                                'name' => 'Analog CCTV Cameras',
                                'description' => 'Traditional cameras that transmit video signals to a monitor or recording device.',
                            ],
                            [
                                'name' => 'IP Cameras',
                                'description' => 'Digital cameras that transmit data over an IP network, offering higher resolution and remote access.',
                            ],
                            [
                                'name' => 'Dome Cameras',
                                'description' => 'Cameras housed in a dome-shaped casing, ideal for discreet monitoring.',
                            ],
                            [
                                'name' => 'PTZ Cameras',
                                'description' => 'Pan-Tilt-Zoom cameras that allow remote control for adjusting the camera angle and zoom.',
                            ],
                            [
                                'name' => 'NVR Systems',
                                'description' => 'Network Video Recorder systems that store and manage footage from IP cameras.',
                            ],
                            [
                                'name' => 'DVR Systems',
                                'description' => 'Digital Video Recorder systems that capture and store video from analog CCTV cameras.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Access Control Systems',
                        'description' => 'Systems that manage user permissions and access to resources and data.',
                        'children' => [
                            [
                                'name' => 'Identity and Access Management (IAM)',
                                'description' => 'Solutions that ensure the right individuals have access to the right resources at the right times.',
                            ],
                            [
                                'name' => 'Multi-Factor Authentication (MFA)',
                                'description' => 'Security measures that require multiple forms of verification to access systems or data.',
                            ],
                            [
                                'name' => 'Single Sign-On (SSO)',
                                'description' => 'Solutions that allow users to log in once to access multiple applications without re-entering credentials.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Intrusion Detection and Prevention Systems (IDPS)',
                        'description' => 'Systems that monitor network traffic for suspicious activity and take action to prevent breaches.',
                        'children' => [
                            [
                                'name' => 'Intrusion Detection Systems (IDS)',
                                'description' => 'Systems that analyze traffic patterns to detect potential intrusions or threats.',
                            ],
                            [
                                'name' => 'Intrusion Prevention Systems (IPS)',
                                'description' => 'Systems that actively block or prevent detected threats from compromising systems.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Antivirus and Anti-Malware Solutions',
                        'description' => 'Software designed to detect, prevent, and remove malicious software from devices.',
                        'children' => [
                            [
                                'name' => 'Endpoint Protection',
                                'description' => 'Solutions that secure end-user devices against malware and cyber threats.',
                            ],
                            [
                                'name' => 'Server Security',
                                'description' => 'Antivirus solutions specifically designed to protect servers from malware and cyber attacks.',
                            ],
                            [
                                'name' => 'Cloud Security',
                                'description' => 'Security solutions that protect data and applications hosted in cloud environments.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Software Programs',
                'description' => 'Various software applications and tools available for purchase in an IT shop.',
                'children' => [
                    [
                        'name' => 'Operating Systems',
                        'description' => 'System software that manages computer hardware and software resources.',
                        'children' => [
                            [
                                'name' => 'Windows OS',
                                'description' => "Microsoft's operating system for personal computers, offering a user-friendly interface and wide compatibility.",
                            ],
                            [
                                'name' => 'Linux Distributions',
                                'description' => 'Open-source operating systems that provide flexibility and customization, including Ubuntu and Fedora.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Productivity Softwares',
                        'description' => 'Applications that help users create documents, spreadsheets, presentations, and more.',
                        'children' => [],
                    ],
                    [
                        'name' => 'Antivirus and Security Software',
                        'description' => 'Programs designed to protect computers from malware, viruses, and cyber threats.',
                        'children' => [
                            [
                                'name' => 'Antivirus Software',
                                'description' => 'Tools that detect and remove malware and provide real-time protection (e.g., Norton, McAfee).',
                            ],
                            [
                                'name' => 'Firewall Software',
                                'description' => 'Applications that monitor and control network traffic based on security rules (e.g., ZoneAlarm, Comodo).',
                            ],
                            [
                                'name' => 'VPN Software',
                                'description' => 'Tools that create secure connections over the internet, protecting user privacy (e.g., ExpressVPN, NordVPN).',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Graphic Design Software',
                        'description' => 'Applications used for creating and editing visual content.',
                        'children' => [
                            [
                                'name' => 'Image Editing Software',
                                'description' => 'Programs for editing and enhancing photos (e.g., Adobe Photoshop, GIMP).',
                            ],
                            [
                                'name' => 'Vector Graphics Software',
                                'description' => 'Applications for creating scalable vector graphics (e.g., Adobe Illustrator, CorelDRAW).',
                            ],
                            [
                                'name' => '3D Modeling Software',
                                'description' => 'Tools for creating and manipulating 3D models (e.g., Blender, Autodesk Maya).',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Development Software',
                        'description' => 'Tools and applications for software development and coding.',
                        'children' => [
                            [
                                'name' => 'Integrated Development Environments (IDEs)',
                                'description' => 'Software applications that provide comprehensive facilities to programmers for software development (e.g., Visual Studio, JetBrains IntelliJ IDEA).',
                            ],
                            [
                                'name' => 'Code Editors',
                                'description' => 'Lightweight applications for editing source code (e.g., Sublime Text, Visual Studio Code).',
                            ],
                        ],
                    ],
                ],
            ],

        ];

        $this->add_categories($categories_data, null);

    }

    private function add_categories(array $categories, ?ItemCategory $parent = null)
    {

        collect($categories)->each(function ($item) use ($parent) {

            $name = data_get($item, 'name');
            $description = data_get($item, 'description');
            $children = data_get($item, 'children', []);

            $category = ItemCategory::firstOrCreate(
                compact('name'),
                [
                    'description' => $description,
                    'parent_id' => $parent?->id,
                ]
            );

            if (count($children)) {

                $this->add_categories($children, $category);
            }
        });

    }
}
