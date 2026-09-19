<?php
namespace Database\Seeders;
use App\Models\PermissionEmpresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class PermissionEmpresaSeeder extends Seeder { public function run(): void {
$items=json_decode(Storage::disk('json')->get('permissions_empresa.json'),true,512,JSON_THROW_ON_ERROR);
DB::transaction(function()use($items){foreach($items as $item){ $record = PermissionEmpresa::find($item['attributes']['id']) ?? new PermissionEmpresa; $record->id=$item['attributes']['id']; $record->fill($item['values'])->save();}});
}}
