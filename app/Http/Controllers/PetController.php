<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PetController extends Controller
{
    /**
     * Display a listing of the user's pets.
     */
    public function index(): Response
    {
        $pets = auth()->user()->pets ?? Pet::where('owner_id', auth()->id())->get();

        return Inertia::render('Pets/Index', [
            'pets' => $pets,
            'title' => 'My Animals',
        ]);
    }

    /**
     * Show the form for creating a new pet.
     */
    public function create(): Response
    {
        return Inertia::render('Pets/Create');
    }

    /**
     * Store a newly created pet in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
            'breed' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'gender' => 'required|in:male,female',
            'medical_notes' => 'nullable|string',
        ]);

        $validated['owner_id'] = auth()->id();

        Pet::create($validated);

        return redirect()->route('pets.index')
            ->with('success', 'Animal registered successfully!');
    }

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet): Response
    {
        // Ensure the pet belongs to the authenticated user
        if ($pet->owner_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to pet record.');
        }

        $pet->load('appointments.veterinarian.user');

        return Inertia::render('Pets/Show', [
            'pet' => $pet,
        ]);
    }

    /**
     * Show the form for editing the specified pet.
     */
    public function edit(Pet $pet): Response
    {
        // Ensure the pet belongs to the authenticated user
        if ($pet->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized access to pet record.');
        }

        return Inertia::render('Pets/Edit', [
            'pet' => $pet,
        ]);
    }

    /**
     * Update the specified pet in storage.
     */
    public function update(Request $request, Pet $pet): RedirectResponse
    {
        // Ensure the pet belongs to the authenticated user
        if ($pet->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized access to pet record.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
            'breed' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'gender' => 'required|in:male,female',
            'medical_notes' => 'nullable|string',
        ]);

        $pet->update($validated);

        return redirect()->route('pets.index')
            ->with('success', 'Animal updated successfully!');
    }

    /**
     * Remove the specified pet from storage (soft delete).
     */
    public function destroy(Pet $pet): RedirectResponse
    {
        // Ensure the pet belongs to the authenticated user
        if ($pet->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized access to pet record.');
        }

        $pet->delete(); // Soft delete

        return redirect()->route('pets.index')
            ->with('success', 'Animal removed successfully!');
    }
}
