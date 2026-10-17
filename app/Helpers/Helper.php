<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class Helper
{

    //! File or Image Upload
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name) . '.' . $file->extension();
        $path = $file->storeAs('uploads/' . $folder, $imageName, 'public');

        return url('storage/' . $path);
    }

    //! File or Image Delete
    public static function fileDelete(?string $path): void
    {
        if (!$path) {
            return;
        }

        // Strip the base URL if it's a full URL
        $localPath = str_replace(url('/'), '', $path);
        $localPath = ltrim($localPath, '/');

        // Remove 'storage/' prefix if present, as Storage::disk('public')->delete expects paths relative to storage/app/public
        if (Str::startsWith($localPath, 'storage/')) {
            $localPath = substr($localPath, 8);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localPath)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($localPath);
        }
    }



    //! Generate Safe Image URL
    public static function imageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            if (str_contains($path, '/uploads/') && !str_contains($path, '/storage/uploads/')) {
                return str_replace('/uploads/', '/storage/uploads/', $path);
            }
            return $path;
        }

        $trimmed = ltrim($path, '/');
        
        if (\Illuminate\Support\Str::startsWith($trimmed, 'storage/')) {
            return asset($trimmed);
        }

        return asset('storage/' . $trimmed);
    }

    //! Generate Slug
    public static function makeSlug($model, string $title): string
    {
        $slug = Str::slug($title);
        while ($model::where('slug', $slug)->exists()) {
            $randomString = Str::random(5);
            $slug         = Str::slug($title) . '-' . $randomString;
        }
        return $slug;
    }

    //! JSON Response
    public static function jsonResponse(bool $status, string $message, int $code, $data = null, bool $paginate = false, $paginateData = null): JsonResponse
    {
        $response = [
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
        ];
        if ($paginate && !empty($paginateData)) {
            $response['data'] = $data;
            $response['pagination'] = [
                'current_page' => $paginateData->currentPage(),
                'last_page' => $paginateData->lastPage(),
                'per_page' => $paginateData->perPage(),
                'total' => $paginateData->total(),
                'first_page_url' => $paginateData->url(1),
                'last_page_url' => $paginateData->url($paginateData->lastPage()),
                'next_page_url' => $paginateData->nextPageUrl(),
                'prev_page_url' => $paginateData->previousPageUrl(),
                'from' => $paginateData->firstItem(),
                'to' => $paginateData->lastItem(),
                'path' => $paginateData->path(),
            ];
        } elseif ($paginate && !empty($data)) {
            $response['data'] = $data->items();
            $response['pagination'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'first_page_url' => $data->url(1),
                'last_page_url' => $data->url($data->lastPage()),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'path' => $data->path(),
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public static function jsonErrorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
            'code'    => $code,
            'errors'  => $errors,
        ];
        return response()->json($response, $code);
    }
}
