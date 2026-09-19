<?php

namespace Database\Seeders;

use App\Models\SectionAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SectionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $sections = json_decode(Storage::disk('json')->get('sections_admin.json'), true);
        foreach ($sections as $section) {
            SectionAdmin::query()->updateOrCreate($section['attributes'], $section['values']);
        }

    }
}
