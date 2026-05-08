<?php

namespace App\Livewire\Dashboard;

use App\Models\Patient;
use Livewire\Component;

class SearchPatients extends Component
{
    public string $query = '';

    public function clearSearch(): void
    {
        $this->reset('query');
    }

    public function render()
    {
        $term = trim($this->query);

        $results = collect();
        if ($term !== '') {
            $results = Patient::query()
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('breed', 'like', "%{$term}%");
                })
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('livewire.dashboard.search-patients', [
            'results' => $results,
        ]);
    }
}
