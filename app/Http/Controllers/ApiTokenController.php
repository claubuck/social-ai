<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    /**
     * Crear un token de API para n8n u otro cliente.
     * El token solo se muestra una vez; hay que copiarlo.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:100']);

        $user = $request->user();
        $name = $request->input('name', 'n8n');

        $token = $user->createToken(Str::slug($name))->plainTextToken;

        return back()->with('api_token_plain', $token);
    }
}
