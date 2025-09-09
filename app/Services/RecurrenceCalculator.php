<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;

class RecurrenceCalculator
{
    /**
     * Kontrollera om en uppgift ska genereras för ett specifikt datum
     */
    public function shouldGenerateTask(Task $task, Carbon $date): bool
    {
        $startDate = $task->getEffectiveStartDate();
        
        // Uppgiften har inte startat än
        if ($date->lt($startDate)) {
            return false;
        }
        
        // Kontrollera slutdatum
        if ($task->end_date && $date->gt($task->end_date)) {
            return false;
        }
        
        // Beräkna baserat på intervalltyp
        switch ($task->interval_type) {
            case 'daily':
                return $this->shouldGenerateDaily($task, $date, $startDate);
            case 'weekly':
                return $this->shouldGenerateWeekly($task, $date, $startDate);
            case 'monthly':
                return $this->shouldGenerateMonthly($task, $date, $startDate);
            case 'yearly':
                return $this->shouldGenerateYearly($task, $date, $startDate);
            case 'custom':
                return $this->shouldGenerateCustom($task, $date, $startDate);
            default:
                return false;
        }
    }
    
    /**
     * Hantera dagliga intervaller
     */
    private function shouldGenerateDaily(Task $task, Carbon $date, Carbon $startDate): bool
    {
        $pattern = $task->recurrence_pattern;
        
        // Endast vardagar
        if ($pattern && isset($pattern['weekdaysOnly']) && $pattern['weekdaysOnly']) {
            return $date->isWeekday();
        }
        
        // Varje X dag
        $daysSinceStart = $date->diffInDays($startDate);
        return $daysSinceStart % $task->interval_value === 0;
    }
    
    /**
     * Hantera veckovisa intervaller
     */
    private function shouldGenerateWeekly(Task $task, Carbon $date, Carbon $startDate): bool
    {
        $pattern = $task->recurrence_pattern;
        
        // Kontrollera om det är rätt veckodag
        if ($pattern && isset($pattern['daysOfWeek'])) {
            $currentDayOfWeek = strtolower($date->englishDayOfWeek);
            if (!in_array($currentDayOfWeek, $pattern['daysOfWeek'])) {
                return false;
            }
        }
        
        // Kontrollera jämn/ojämn vecka logik
        if ($pattern && isset($pattern['weekType'])) {
            $currentWeekNumber = (int) $date->format('W');
            $isEvenWeek = ($currentWeekNumber % 2 === 0);
            
            if ($pattern['weekType'] === 'even' && !$isEvenWeek) {
                return false;
            }
            
            if ($pattern['weekType'] === 'odd' && $isEvenWeek) {
                return false;
            }
            
            // För weekType-uppgifter, hoppa över standardintervall-logiken och returnera true
            // eftersom weekType redan bestämmer när uppgiften ska köras
            return true;
        }
        
        // För veckovisa intervaller, räkna från början av startveckan
        $startOfStartWeek = $startDate->copy()->startOfWeek();
        $startOfCurrentWeek = $date->copy()->startOfWeek();
        
        $weeksSinceStart = $startOfCurrentWeek->diffInWeeks($startOfStartWeek);
        return $weeksSinceStart % $task->interval_value === 0;
    }
    
    /**
     * Hantera månadsintervaller
     */
    private function shouldGenerateMonthly(Task $task, Carbon $date, Carbon $startDate): bool
    {
        $pattern = $task->recurrence_pattern;
        
        // Månader sedan start
        $monthsSinceStart = (int) $date->diffInMonths($startDate->copy()->startOfMonth());
        
        if ($monthsSinceStart % $task->interval_value !== 0) {
            return false;
        }
        
        // Specifik dag i månaden
        if ($pattern && isset($pattern['dayOfMonth'])) {
            $targetDay = (int) $pattern['dayOfMonth'];
            
            // Hantera månader som inte har så många dagar (t.ex. 31:a februari)
            $lastDayOfMonth = $date->copy()->endOfMonth()->day;
            if ($targetDay > $lastDayOfMonth) {
                $targetDay = $lastDayOfMonth;
            }
            
            return $date->day === $targetDay;
        }
        
        // Specifik veckodag i månaden (t.ex. första måndagen)
        if ($pattern && isset($pattern['weekdayOfMonth'])) {
            $ordinal = $pattern['weekdayOfMonth']['ordinal']; // 1-5 (5 = sista)
            $targetDayName = $pattern['weekdayOfMonth']['day']; // 'monday', 'tuesday', etc.
            
            return $this->isSpecificWeekdayOfMonth($date, $targetDayName, $ordinal);
        }
        
        // Standard: första dagen i månaden
        return $date->day === 1;
    }
    
    /**
     * Hantera årliga intervaller
     */
    private function shouldGenerateYearly(Task $task, Carbon $date, Carbon $startDate): bool
    {
        // För nu, enkel implementering - samma månad och dag
        return $date->month === $startDate->month && $date->day === $startDate->day;
    }
    
    /**
     * Hantera anpassade intervaller
     */
    private function shouldGenerateCustom(Task $task, Carbon $date, Carbon $startDate): bool
    {
        $pattern = $task->recurrence_pattern;
        
        if (!$pattern) {
            // Fallback till gamla systemet
            $daysSinceStart = $date->diffInDays($startDate);
            return $daysSinceStart % $task->interval_value === 0;
        }
        
        // Här kan man lägga till mer avancerad logik för anpassade mönster
        // T.ex. specifika datum, kombinationer av ovan, etc.
        
        return false;
    }
    
    /**
     * Kontrollera om datum är en specifik veckodag i månaden
     */
    private function isSpecificWeekdayOfMonth(Carbon $date, string $targetDayName, int $ordinal): bool
    {
        $dayOfWeekMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7
        ];
        
        $targetDayOfWeek = $dayOfWeekMap[$targetDayName] ?? null;
        if (!$targetDayOfWeek || $date->dayOfWeek !== $targetDayOfWeek) {
            return false;
        }
        
        // Sista veckodagen i månaden
        if ($ordinal === 5) {
            $lastOccurrence = $date->copy()->endOfMonth();
            while ($lastOccurrence->dayOfWeek !== $targetDayOfWeek) {
                $lastOccurrence->subDay();
            }
            return $date->isSameDay($lastOccurrence);
        }
        
        // N:te veckodagen i månaden
        $firstOccurrence = $date->copy()->startOfMonth();
        while ($firstOccurrence->dayOfWeek !== $targetDayOfWeek) {
            $firstOccurrence->addDay();
        }
        
        $targetDate = $firstOccurrence->copy()->addWeeks($ordinal - 1);
        
        // Se till att vi fortfarande är i samma månad
        if ($targetDate->month !== $date->month) {
            return false;
        }
        
        return $date->isSameDay($targetDate);
    }
    
    /**
     * Få nästa N datum för förhandsvisning
     */
    public function getNextOccurrences(Task $task, int $count = 5): array
    {
        $dates = [];
        $currentDate = $task->getEffectiveStartDate();
        $maxDate = now()->addMonths(12); // Begränsa till nästa år
        
        while (count($dates) < $count && $currentDate->lte($maxDate)) {
            if ($this->shouldGenerateTask($task, $currentDate)) {
                $dates[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }
        
        return $dates;
    }
}