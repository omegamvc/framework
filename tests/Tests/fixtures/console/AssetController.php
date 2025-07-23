<?php

declare(strict_types=1);

namespace App\Controllers;

use Omega\Http\Response;

class AssetController extends Controller
{
    public function index(): Response
    {
        return view('Asset', [
            "title" => "Documnet Title",
        ]);
    }
}
