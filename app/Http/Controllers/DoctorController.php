<?php

namespace App\Http\Controllers;

use App\Models\Veterinarian;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    /**
     * Display a listing of veterinarians.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $query = Veterinarian::with(['user', 'specializations']);

        // Apply specialization filter if provided
        if ($request->has('specialization')) {
            $query->whereHas('specializations', function ($q) use ($request) {
                $q->where('specializations.id', $request->specialization);
            });
        }

        $veterinarians = $query->paginate(12);

        return Inertia::render('Doctors/Index', [
            'veterinarians' => $veterinarians,
            'specializations' => Specialization::all(),
            'filters' => $request->only(['specialization']),
        ]);
    }

    /**
     * Display the specified veterinarian profile.
     *
     * @param Veterinarian $veterinarian
     * @return Response
     */
    public function show(Veterinarian $veterinarian): Response
    {
        $veterinarian->load(['user', 'specializations']);

        // Get available time slots for the next 30 days
        $startDate = now();
        $endDate = now()->addDays(30);
        $availableSlots = $veterinarian->availableSlots($startDate, $endDate);

        return Inertia::render('Doctors/Show', [
            'veterinarian' => $veterinarian,
            'averageRating' => $veterinarian->averageRating(),
            'availableSlots' => $availableSlots,
        ]);
    }

    /**
     * Search for veterinarians based on query.
     *
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
            'specialization' => 'nullable|exists:specializations,id',
        ]);

        $query = Veterinarian::with(['user', 'specializations']);

        // Search by name or bio
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%");
                })->orWhere('bio', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->whereHas('specializations', function ($q) use ($request) {
                $q->where('specializations.id', $request->input('specialization'));
            });
        }

        $veterinarians = $query->paginate(12);

        return Inertia::render('Doctors/Search', [
            'veterinarians' => $veterinarians,
            'specializations' => Specialization::all(),
            'filters' => $request->only(['query', 'specialization']),
        ]);
    }
}
