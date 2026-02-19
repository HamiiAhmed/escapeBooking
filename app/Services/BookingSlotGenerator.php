<?php

namespace App\Services;

use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingSlotGenerator
{
    public static function generateSlots(
        string $dateStr,
        int $packageDuration,
        Collection $currentBookings,
        ?WorkingHour $workingHour = null
    ): array {
        $date = Carbon::parse($dateStr);

        // OFF DAY CHECK
        if (!$workingHour || $workingHour->status !== 'open' || !$workingHour->start_time) {
            return [];
        }
        // TIMES WITH SAME DATE
        $startTime = $date->copy()->setTimeFromTimeString($workingHour->start_time);
        $endTimeStr = $workingHour->end_time ?? '23:59';
        $endTime = $date->copy()->setTimeFromTimeString($endTimeStr);

        // OVERNIGHT: End time next day pe hai?
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        // TODAY: Current time se start (15 min intervals)
        if ($date->isToday()) {
            $now = Carbon::now()->ceilMinute(15);
            $startTime = $startTime->max($now);
        }

        $slots = [];
        $currentSlot = $startTime->copy();
        while ($currentSlot->lte($endTime->copy()->subMinutes($packageDuration))) {
            $slotEnd = $currentSlot->copy()->addMinutes($packageDuration);

            if ($slotEnd->gt($endTime)) {
                break;
            }

            $isBooked = $currentBookings->contains(function ($booking) use ($currentSlot, $slotEnd, $date) {
                
            $bookingStartRaw = $booking['start'] ?? null;

                if (!$bookingStartRaw) return false;

                $bookingStart = Carbon::parse($bookingStartRaw);
                $bookingDuration = $booking['duration_minutes'];
                $bookingEnd = $bookingStart->copy()->addMinutes($bookingDuration);

                if ($bookingStart->format('Y-m-d') !== $date->format('Y-m-d')) {
                    return false;
                }

                return $currentSlot->lt($bookingEnd) && $slotEnd->gt($bookingStart);
            });

            $slots[] = [
                'start' => $currentSlot->format('g:i A'),
                'end' => $slotEnd->format('g:i A'),
                'start_full' => $currentSlot->format('Y-m-d H:i:s'),
                'is_available' => !$isBooked,
                'backgroundColor' => $isBooked ? '#e74c3c' : '#27ae60',
                'borderColor' => $isBooked ? '#c0392b' : '#219a52'
            ];

            $currentSlot->addMinutes($packageDuration);
        }

        return $slots;
    }
}
