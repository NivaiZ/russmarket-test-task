<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FrontendController extends Controller
{
    public function show(Request $request, ?string $path = null): BinaryFileResponse|View
    {
        $root = realpath(storage_path('app/frontend'));
        $relative = ($path === null || $path === '') ? 'index.html' : $path;
        $file = $this->fileInside($root, $relative);

        if ($file !== null) {
            return $this->responseFile($file);
        }

        $index = $this->fileInside($root, 'index.html');
        if ($index !== null && ! str_contains($relative, '.')) {
            return $this->responseFile($index);
        }

        if ($relative === 'index.html') {
            return view('welcome');
        }

        abort(404);
    }

    private function fileInside(string|false $root, string $relative): ?string
    {
        if ($root === false || str_contains($relative, "\0")) {
            return null;
        }

        $candidate = realpath($root.DIRECTORY_SEPARATOR.$relative);
        if ($candidate === false || ! is_file($candidate)) {
            return null;
        }

        $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (! str_starts_with($candidate, $rootPrefix) && $candidate !== $root) {
            return null;
        }

        return $candidate;
    }

    private function responseFile(string $file): BinaryFileResponse
    {
        $types = [
            'css' => 'text/css',
            'js' => 'text/javascript',
            'html' => 'text/html; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'ico' => 'image/x-icon',
            'txt' => 'text/plain; charset=UTF-8',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $headers = [];
        if (isset($types[$extension])) {
            $headers['Content-Type'] = $types[$extension];
        }
        if ($extension === 'html') {
            $headers['Cache-Control'] = 'no-cache';
        }

        return response()->file($file, $headers);
    }
}
