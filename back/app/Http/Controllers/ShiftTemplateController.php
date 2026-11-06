<?php

namespace App\Http\Controllers;

use App\Models\ShiftTemplate;
use Illuminate\Http\JsonResponse;

class ShiftTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = ShiftTemplate::query()->orderBy('hotkey')->get();

        return response()->json([
            'data' => $templates->map(fn (ShiftTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'starts_at' => $template->starts_at,
                'ends_at' => $template->ends_at,
                'border_color' => $template->border_color,
                'background_color' => $template->background_color,
                'hotkey' => $template->hotkey,
            ])->all(),
        ]);
    }
}
