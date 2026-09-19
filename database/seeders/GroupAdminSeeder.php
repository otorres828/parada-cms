<?php
namespace Database\Seeders;
use App\Models\GroupAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class GroupAdminSeeder extends Seeder { public function run(): void {
$items=json_decode(Storage::disk('json')->get('groups_admin.json'),true,512,JSON_THROW_ON_ERROR);
DB::transaction(function()use($items){foreach($items as $item){ $record = GroupAdmin::find($item['attributes']['id']) ?? new GroupAdmin; $record->id=$item['attributes']['id']; $record->fill($item['values'])->save();}});
}}
