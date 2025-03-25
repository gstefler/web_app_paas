<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Image;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;
use Throwable;
use Illuminate\Support\Facades\Redirect;

class ImageController extends Controller
{
    public function index()
    {
        return Inertia::render('Images', [
            'images' => auth()->user()->images->map(function (Image $image) {
                return [
                    'id' => $image->id,
                    'name' => $image->name,
                    'path' => route('images.show', $image->id),
                    'created_at' => $image->created_at,
                ];
            })
        ]);
    }

    public function show(Image $image)
    {
        if ($image->user_id !== auth()->id()) abort(404);
        return response()->file(Storage::disk('private')->path("images/$image->filename"));
    }

    /**
     * @throws Throwable
     */
    public function store(ImageUploadRequest $request)
    {
        DB::transaction(function () use ($request) {
            $image = $request->user()->images()->create([
                'name' => $request->name,
                'extension' => $request->file('image')->getClientOriginalExtension(),
            ]);

            $file = $request->file('image');
            $file->storeAs('images', $image->filename, 'private');
        });
        return Redirect::back();
    }

    /**
     * @throws Throwable
     */
    public function destroy(Image $image)
    {
        if ($image->user_id !== auth()->id()) abort(403);

        DB::transaction(function () use ($image) {
            $filename = $image->filename;
            $image->delete();
            Storage::disk('private')->delete("images/$filename");
        });

        return Redirect::back();
    }
}
