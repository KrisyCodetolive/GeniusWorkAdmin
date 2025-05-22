<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CongesCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.conges-calendar-widget';
    
    protected int | string | array $columnSpan = 2;
    
    public ?string $filter = 'current';
    
    public function mount(): void
    {
        $this->filter = 'current';
    }
    
    protected function getViewData(): array
    {
        $date = $this->getDate();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Obtenir le premier jour de la semaine (lundi = 1)
        $firstDayOfWeek = $startOfMonth->copy()->dayOfWeek;
        if ($firstDayOfWeek === 0) $firstDayOfWeek = 7; // Dimanche = 7
        
        // Jours du mois précédent pour compléter la première semaine
        $previousMonth = $startOfMonth->copy()->subMonth();
        $daysFromPreviousMonth = $firstDayOfWeek - 1;
        $previousMonthStart = $previousMonth->endOfMonth()->subDays($daysFromPreviousMonth - 1);
        
        // Jours du mois suivant pour compléter la dernière semaine
        $lastDayOfWeek = $endOfMonth->copy()->dayOfWeek;
        if ($lastDayOfWeek === 0) $lastDayOfWeek = 7; // Dimanche = 7
        $daysFromNextMonth = 7 - $lastDayOfWeek;
        
        // Récupérer les congés pour la période affichée
        $conges = Conge::where('statut', 'approuve')
            ->where(function (Builder $query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date_debut', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('date_fin', [$startOfMonth, $endOfMonth])
                    ->orWhere(function (Builder $query) use ($startOfMonth, $endOfMonth) {
                        $query->where('date_debut', '<', $startOfMonth)
                            ->where('date_fin', '>', $endOfMonth);
                    });
            })
            ->with(['employeur', 'typeConge'])
            ->get()
            ->groupBy(function ($conge) {
                return Carbon::parse($conge->date_debut)->format('Y-m-d');
            });
            
        return [
            'currentMonth' => $date->format('F Y'),
            'previousMonth' => $date->copy()->subMonth()->format('F Y'),
            'nextMonth' => $date->copy()->addMonth()->format('F Y'),
            'daysInMonth' => $endOfMonth->day,
            'startDay' => $firstDayOfWeek,
            'previousMonthStart' => $previousMonthStart->day,
            'daysFromPreviousMonth' => $daysFromPreviousMonth,
            'daysFromNextMonth' => $daysFromNextMonth,
            'today' => Carbon::now()->format('Y-m-d'),
            'currentMonthStart' => $startOfMonth->format('Y-m-d'),
            'conges' => $conges,
        ];
    }
    
    protected function getDate(): Carbon
    {
        return match ($this->filter) {
            'previous' => Carbon::now()->subMonth(),
            'next' => Carbon::now()->addMonth(),
            default => Carbon::now(),
        };
    }
    
    public function previousMonth(): void
    {
        $this->filter = 'previous';
    }
    
    public function nextMonth(): void
    {
        $this->filter = 'next';
    }
    
    public function currentMonth(): void
    {
        $this->filter = 'current';
    }
}
