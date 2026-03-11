<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Crear una empresa y asignar al usuario actual como miembro.
     * Así el usuario puede usar temas de contenido, posts, etc.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        if ($user->company_id) {
            return redirect()->route('content-topics.index')
                ->with('success', 'Ya perteneces a una empresa.');
        }

        $company = Company::create([
            'name' => $request->input('name'),
            'plan' => $request->input('plan', 'free'),
        ]);

        $user->update(['company_id' => $company->id]);

        return redirect()->route('content-topics.index')
            ->with('success', "Empresa «{$company->name}» creada. Ya puedes agregar temas.");
    }
}
