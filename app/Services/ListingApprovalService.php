<?php

namespace App\Services;

use App\Models\Establishment;

/**
 * The one place that decides how an establishment moves between
 * pending, approved and rejected.
 *
 * Policy:
 *  - New listings start as pending.
 *  - Editing the details of an approved listing sends it back to pending (only if something changed).
 *  - Editing a rejected listing resubmits it as pending and clears the reason.
 *  - Services and images never trigger re-approval.
 */
class ListingApprovalService
{
    /** Save an owner's edit and return the message to show them. */
    public function saveOwnerEdit(Establishment $establishment): string
    {
        $message = 'Establishment updated.';

        if ($establishment->status === Establishment::STATUS_REJECTED) {
            $establishment->status = Establishment::STATUS_PENDING;
            $establishment->rejection_reason = null;
            $message = 'Your corrections were saved and the listing has been resubmitted for approval.';
        } elseif ($establishment->status === Establishment::STATUS_APPROVED && $establishment->isDirty()) {
            $establishment->status = Establishment::STATUS_PENDING;
            $message = 'Changes saved. Because this listing was already approved, it is hidden from the public until an administrator approves the changes.';
        }

        $establishment->save();

        return $message;
    }

    public function approve(Establishment $establishment): void
    {
        $establishment->status = Establishment::STATUS_APPROVED;
        $establishment->rejection_reason = null;
        $establishment->save();
    }

    public function reject(Establishment $establishment, string $reason): void
    {
        $establishment->status = Establishment::STATUS_REJECTED;
        $establishment->rejection_reason = $reason;
        $establishment->save();
    }
}
