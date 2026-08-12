<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use App\Models\Media;

use App\Support\MediaStorage;

use Illuminate\Http\Request;



class MediaAdminController extends Controller

{

    public function index(Request $request)
    {
        $media = Media::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('filename', 'like', $q)
                        ->orWhere('alt_text', 'like', $q)
                        ->orWhere('path', 'like', $q);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.media.index', compact('media'));
    }



    public function store(Request $request)

    {

        $request->validate([

            'file' => 'required|image|max:5120',

            'alt_text' => 'nullable|string|max:255',

        ]);



        $file = $request->file('file');

        $path = MediaStorage::store($file);



        Media::create([

            'filename' => $file->getClientOriginalName(),

            'path' => $path,

            'alt_text' => $request->input('alt_text'),

            'mime_type' => $file->getMimeType(),

            'size' => $file->getSize(),

        ]);



        return back()->with('success', 'File uploaded.');

    }



    public function destroy(Media $media)

    {

        MediaStorage::delete($media->path);

        $media->delete();

        return back()->with('success', 'File deleted.');

    }

}

