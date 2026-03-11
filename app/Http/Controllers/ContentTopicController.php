<?php

namespace App\Http\Controllers;

use App\Models\ContentTopic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentTopicController extends Controller
{
    /**
     * Lista de temas de contenido para el flujo n8n (generar posts con IA).
     * Solo usuarios con company_id pueden gestionar temas.
     */
    public function index(Request $request): Response|array
    {
        $user = $request->user();
        if (! $user->company_id) {
            return Inertia::render('ContentTopics/Index', [
                'topics' => [],
                'hasCompany' => false,
                'flash' => ['success' => session('success')],
                'apiTokenPlain' => session()->pull('api_token_plain'),
            ]);
        }

        $topics = ContentTopic::where('company_id', $user->company_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('ContentTopics/Index', [
            'topics' => $topics,
            'hasCompany' => true,
            'companyId' => $user->company_id,
            'flash' => ['success' => session('success')],
            'apiTokenPlain' => session()->pull('api_token_plain'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['topic' => 'required|string|max:500']);

        $user = $request->user();
        if (! $user->company_id) {
            return back()->withErrors(['topic' => 'Debes pertenecer a una empresa para agregar temas.']);
        }

        $maxOrder = ContentTopic::where('company_id', $user->company_id)->max('sort_order') ?? -1;

        ContentTopic::create([
            'company_id' => $user->company_id,
            'topic' => $request->input('topic'),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('content-topics.index')->with('success', 'Tema agregado.');
    }

    public function update(Request $request, ContentTopic $contentTopic)
    {
        $user = $request->user();
        if ($contentTopic->company_id !== $user->company_id) {
            abort(403);
        }

        $request->validate(['topic' => 'required|string|max:500']);
        $contentTopic->update(['topic' => $request->input('topic')]);

        return redirect()->route('content-topics.index')->with('success', 'Tema actualizado.');
    }

    public function destroy(Request $request, ContentTopic $contentTopic)
    {
        $user = $request->user();
        if ($contentTopic->company_id !== $user->company_id) {
            abort(403);
        }

        $contentTopic->delete();
        return redirect()->route('content-topics.index')->with('success', 'Tema eliminado.');
    }
}
