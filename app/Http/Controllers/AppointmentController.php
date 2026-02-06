<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Pet;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Store a newly created appointment in storage.
     *
     * @param StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        try {
            // Use database transaction with pessimistic locking to prevent double-booking
            $appointment = DB::transaction(function () use ($request) {
                // Lock the time slot row for update to prevent concurrent bookings
                $timeSlot = TimeSlot::where('id', $request->time_slot_id)
                    ->lockForUpdate()
                    ->first();

                // Verify the time slot is still available
                if (!$timeSlot || !$timeSlot->isAvailable()) {
                    throw new \Exception('The selected time slot is no longer available.');
                }

                // Verify the pet belongs to the authenticated user
                $pet = Pet::where('id', $request->pet_id)
                    ->where('owner_id', auth()->id())
                    ->first();

                if (!$pet) {
                    throw new \Exception('The selected pet does not belong to you.');
                }

                // Create the appointment
                $appointment = Appointment::create([
                    'pet_owner_id' => auth()->id(),
                    'veterinarian_id' => $request->veterinarian_id,
                    'pet_id' => $request->pet_id,
                    'time_slot_id' => $request->time_slot_id,
                    'status' => 'pending',
                ]);

                // Mark the time slot as booked
                $timeSlot->book($appointment);

                return $appointment;
            });

            // Send notifications to both pet owner and veterinarian
            $appointment->load(['petOwner', 'veterinarian.user']);
            
            // Notify pet owner
            $appointment->petOwner->notify(new AppointmentBookedNotification($appointment));
            
            // Notify veterinarian
            $appointment->veterinarian->user->notify(new AppointmentBookedNotification($appointment));

            return redirect()
                ->route('appointments.show', $appointment)
                ->with('success', 'Appointment booked successfully! Please proceed to payment.');

        } catch (\Exception $e) {
            Log::error('Appointment booking failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Cancel an appointment and release the time slot.
     *
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        try {
            // Verify the user is authorized to cancel this appointment
            if ($appointment->pet_owner_id !== auth()->id() && 
                $appointment->veterinarian_id !== auth()->id()) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'You are not authorized to cancel this appointment.']);
            }

            // Use transaction to ensure atomicity
            DB::transaction(function () use ($appointment) {
                // Update appointment status
                $appointment->update(['status' => 'cancelled']);

                // Release the time slot
                if ($appointment->timeSlot) {
                    $appointment->timeSlot->release();
                }
            });

            // Send notifications to both parties
            $appointment->load(['petOwner', 'veterinarian.user']);
            
            // Notify pet owner
            $appointment->petOwner->notify(new AppointmentCancelledNotification($appointment));
            
            // Notify veterinarian
            $appointment->veterinarian->user->notify(new AppointmentCancelledNotification($appointment));

            return redirect()
                ->route('appointments.index')
                ->with('success', 'Appointment cancelled successfully.');

        } catch (\Exception $e) {
            Log::error('Appointment cancellation failed', [
                'appointment_id' => $appointment->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to cancel appointment. Please try again.']);
        }
    }

    /**
     * Display the specified appointment.
     *
     * @param Appointment $appointment
     * @return Response
     */
    public function show(Appointment $appointment): Response
    {
        // Load relationships
        $appointment->load(['pet', 'veterinarian.user', 'timeSlot']);

        return Inertia::render('Appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Display a listing of appointments for the authenticated user.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->isPetOwner()) {
            $appointments = Appointment::where('pet_owner_id', $user->id)
                ->with(['pet', 'veterinarian.user', 'timeSlot'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($user->isVeterinarian()) {
            $appointments = Appointment::where('veterinarian_id', $user->id)
                ->with(['pet', 'petOwner', 'timeSlot'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $appointments = collect();
        }

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }
}
