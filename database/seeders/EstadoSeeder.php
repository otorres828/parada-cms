<?php
namespace Database\Seeders;
use App\Models\Estado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
class EstadoSeeder extends Seeder {public function run(): void {foreach(json_decode(Storage::disk('json')->get('estados_venezuela.json'),true,512,JSON_THROW_ON_ERROR) as $nombre){Estado::firstOrCreate(['nombre'=>$nombre]);}}}
