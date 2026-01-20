<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Open a notification and redirect to the related entity (if any).
     */
    public function show(int $id): RedirectResponse
    {
        $user = Auth::user();

        $notification = Notification::where('user_id', $user->id)->findOrFail($id);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if (!$notification->entity_type || !$notification->entity_id) {
            return redirect()->route('dashboard')
                ->with('info', 'This notification has no linked record.');
        }

        $entityType = Str::of($notification->entity_type)->snake()->lower()->value();
        $entityId = (int) $notification->entity_id;

        switch ($entityType) {
            case 'promotion_eligibility_list':
                if ($user->hasRole('Board')) {
                    return redirect()->route('board.promotions.show', $entityId);
                }
                if ($user->hasRole('HRD')) {
                    return redirect()->route('hrd.promotion-eligibility.show', $entityId);
                }
                break;

            case 'education_change_request':
                return redirect()->route('hrd.education-requests.show', $entityId);

            case 'investigation':
                return redirect()->route('investigation.show', $entityId);

            case 'aper_form':
            case 'aperform':
                if ($user->hasRole('HRD')) {
                    return redirect()->route('hrd.aper-forms.show', $entityId);
                }
                if ($user->hasRole('Officer')) {
                    return redirect()->route('officer.aper-forms.show', $entityId);
                }
                break;

            case 'officer':
                if ($user->hasRole('HRD')) {
                    return redirect()->route('hrd.officers.show', $entityId);
                }
                break;
        }

        return redirect()->route('dashboard')
            ->with('info', 'This notification cannot be opened directly.');
    }
}

