<?php

namespace Database\Seeders;

use App\Models\AuthorizedDevice;
use Illuminate\Database\Seeder;

class AuthorizedDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'device_name' => 'HGHGY',
                'mac_address' => '00:00:00:00:00:00',
                'ip_address' => '103.171.20.186',
                'status' => 'authorized',
            ],
            [
                'device_name' => 'localhost',
                'mac_address' => '00:00:00:00:00:00',
                'ip_address' => '127.0.0.1',
                'status' => 'authorized'
            ],
            [
                'device_name' => 'Eugene the great',
                'mac_address' => '5a:81:93:0b:77:63',
                'ip_address' => '192.168.1.36',
                'status' => 'authorized'
            ],
            [
                'device_name' => 'JM Makunat',
                'mac_address' => '0e:97:0a:d5:28:fe',
                'ip_address' => '192.168.1.63',
                'status' => 'authorized'
            ],
            [
                'device_name' => 'Phone1',
                'mac_address' => 'b6:81:0a:dd:ea:f8',
                'ip_address' => '192.168.1.57',
                'status' => 'authorized'
            ],
        ];

        foreach ($devices as $device) {
            AuthorizedDevice::updateOrCreate(
                ['device_name' => $device['device_name']],
                $device,
            );
        }
    }
}
