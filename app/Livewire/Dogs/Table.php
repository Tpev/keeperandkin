<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;

class Table extends Component
{
    use WithPagination;

    // Filters
    public string $q = '';
    public ?string $sex = '';
    public ?string $flags = '';      // '', 'with', 'none'
    public ?int $scoreMin = null;

    // Keep page in query string for UX
    protected $queryString = [
        'q'        => ['except' => ''],
        'sex'      => ['except' => ''],
        'flags'    => ['except' => ''],
        'scoreMin' => ['except' => null],
        'page'     => ['except' => 1],
    ];

    public function updating($name, $value)
    {
        // Reset pagination whenever a filter/search changes
        if (in_array($name, ['q','sex','flags','scoreMin'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->sex = '';
        $this->flags = '';
        $this->scoreMin = null;
        $this->resetPage();
    }

    public function render()
    {
        $teamDogs = Auth::user()
            ->currentTeam
            ->dogs()
            ->with('latestEvaluation')
            ->withCount('evaluations');

        // Search (name, breed, serial_number)
        if ($this->q !== '') {
            $q = trim($this->q);
            $teamDogs->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('breed', 'like', "%{$q}%")
                   ->orWhere('serial_number', 'like', "%{$q}%");
            });
        }

        // Sex filter
        if ($this->sex === 'male' || $this->sex === 'female') {
            $teamDogs->where('sex', $this->sex);
        }

        // Score min filter (using latestEvaluation->score)
        if (is_numeric($this->scoreMin)) {
            $min = max(0, min(100, (int) $this->scoreMin));
            $teamDogs->whereHas('latestEvaluation', function ($q) use ($min) {
                $q->where('score', '>=', $min);
            });
        }

        // Flags: with / none (using JSON length on red_flags)
        if ($this->flags === 'with') {
            $teamDogs->whereHas('latestEvaluation', function ($q) {
                // if your DB supports json_length; otherwise adapt to your column type
                $q->whereRaw("json_length(red_flags) > 0");
            });
        } elseif ($this->flags === 'none') {
            $teamDogs->where(function ($q) {
                $q->doesntHave('latestEvaluation')
                  ->orWhereHas('latestEvaluation', function ($qq) {
                      $qq->whereRaw("json_length(red_flags) = 0");
                  });
            });
        }

        $rows = $teamDogs
            ->latest()
            ->paginate(10);

        $headers = [
            ['index' => 'name',  'label' => 'Dog'],
            ['index' => 'age',   'label' => 'Age'],
            ['index' => 'sex',   'label' => 'Sex'],
            ['index' => 'score', 'label' => 'Score'],
            ['index' => 'flag',  'label' => 'Flag'],
            ['index' => 'action'],
        ];

        return view('livewire.dogs.table', compact('headers', 'rows'));
    }
}
