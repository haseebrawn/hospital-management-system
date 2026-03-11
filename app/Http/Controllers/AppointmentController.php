<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Models\Appointment;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use App\Notifications\AppointmentCreatedNotification;
use App\Events\AppointmentCreated;


class AppointmentController extends Controller
{
    /**
     *  View Appointments with Role-Based Filtering
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $appointments = Appointment::with(['patient', 'doctor'])->get();
        } elseif ($user->hasRole('admin')) {
            $appointments = Appointment::with(['patient', 'doctor'])
                ->whereHas('patient', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->get();
        } elseif ($user->hasRole('doctor')) {
            $appointments = Appointment::with(['patient', 'doctor'])
                ->where('doctor_id', $user->id)->get();
        } else { // Receptionist
            $appointments = Appointment::with(['patient', 'doctor'])
                ->whereHas('patient', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->get();
        }

        return AppointmentResource::collection($appointments);
    }

    /**
     *  Create Appointment → Send Notifications
     */
    public function store(AppointmentRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id;

        //  Automatically get department from the doctor's record
        $doctor = \App\Models\User::find($data['doctor_id']);
        if (!$doctor) {
            return response()->json(['message' => 'Invalid doctor_id'], 422);
        }

        $data['department_id'] = $doctor->department_id;

        //  Map request keys to database columns
        $data['date'] = $request->input('appointment_date') ?? $request->input('scheduled_at');
        $data['time'] = $request->input('appointment_time');

        unset($data['appointment_date'], $data['appointment_time'], $data['scheduled_at']); // remove unused keys

        //  Create appointment
        $appointment = \App\Models\Appointment::create($data);

        //  Notify doctor
        if ($appointment->doctor) {
            $appointment->doctor->notify(new \App\Notifications\AppointmentCreatedNotification($appointment));
        }

        //  Notify all super admins
        $superAdmins = \App\Models\User::role('super_admin')->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new \App\Notifications\AppointmentCreatedNotification($appointment));
        }

        //  Broadcast via Pusher (real-time)
        broadcast(new \App\Events\AppointmentCreated($appointment))->toOthers();

        return response()->json([
            'message' => 'Appointment created successfully and notifications sent.',
            'appointment' => new \App\Http\Resources\AppointmentResource($appointment)
        ], 201);
    }


    /**
     *  View Appointment Details
     */
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
        return new AppointmentResource($appointment);
    }

    /**
     *  Update Appointment
     */
    public function update(AppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->validated());

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'appointment' => new AppointmentResource($appointment)
        ]);
    }

    /**
     *  Cancel/Delete Appointment
     */
    public function destroy($id)
    {
        Appointment::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully.'
        ]);
    }
}
