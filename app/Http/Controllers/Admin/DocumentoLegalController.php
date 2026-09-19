<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\DocumentoLegal;
use App\Services\Admin\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class DocumentoLegalController extends Controller
{
    public function show(Request $request, int $empresa_id, int $documento_id)
    {
        Access::authorize('legales','detail');
        Access::authorize('legales','file');
        $documento=DocumentoLegal::searchAdmin('', ['empresa_id'=>$empresa_id])->findOrFail($documento_id);
        abort_unless(str_starts_with($documento->archivo,'legales/'.$empresa_id.'/') && !str_contains($documento->archivo,'..'),404);
        abort_unless(Storage::disk('local')->exists($documento->archivo),404,'El archivo no está disponible.');
        $extension=match($documento->mime){'application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp',default=>abort(404)};
        $name=(Str::slug($documento->titulo)?:'documento').'.'.$extension;
        $headers=['Content-Type'=>$documento->mime,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store','Content-Security-Policy'=>"default-src 'none'; sandbox"];
        return $request->boolean('download')
            ? Storage::disk('local')->download($documento->archivo,$name,$headers)
            : Storage::disk('local')->response($documento->archivo,$name,$headers,'inline');
    }
}
