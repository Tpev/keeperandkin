<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller; 

class DogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /* LIST all dogs for the logged-in user’s current shelter */
    public function index()
    {
        $team = Auth::user()->currentTeam;

        Gate::authorize('manage-animals', $team);

        $dogs = $team->dogs()->latest()->paginate(15);

        return view('dogs.index', compact('dogs'));
    }

    /* SHOW create form */
    public function create()
    {
        $team = Auth::user()->currentTeam;
        Gate::authorize('manage-animals', $team);

        return view('dogs.create');
    }

    /* STORE new dog */
    public function store(Request $request)
    {
        $team = Auth::user()->currentTeam;
        Gate::authorize('manage-animals', $team);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'breed'       => ['nullable', 'string', 'max:255'],
            'age'         => ['nullable', 'integer', 'min:0', 'max:30'],
            'sex'         => ['nullable', 'in:male,female'],
            'description' => ['nullable', 'string'],
        ]);

        $team->dogs()->create($data);

        return redirect()->route('dogs.index')->with('success', 'Dog added!');
    }

    /* SHOW a single dog */
    public function show(Dog $dog)
    {
        Gate::authorize('manage-animals', $dog->team);

        return view('dogs.show', compact('dog'));
    }

    /* EDIT form */
    public function edit(Dog $dog)
    {
        Gate::authorize('manage-animals', $dog->team);

        return view('dogs.edit', compact('dog'));
    }

    /* UPDATE */
    public function update(Request $request, Dog $dog)
    {
        Gate::authorize('manage-animals', $dog->team);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'breed'       => ['nullable', 'string', 'max:255'],
            'age'         => ['nullable', 'integer', 'min:0', 'max:30'],
            'sex'         => ['nullable', 'in:male,female'],
            'description' => ['nullable', 'string'],
        ]);

        $dog->update($data);

        return redirect()->route('dogs.index')->with('success', 'Dog updated!');
    }

    /* DELETE */
    public function destroy(Dog $dog)
    {
        Gate::authorize('manage-animals', $dog->team);

        $dog->delete();

        return redirect()->route('dogs.index')->with('success', 'Dog removed.');
    }
}
