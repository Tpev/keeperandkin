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

    protected $queryString = [
        'q'        => ['except' => ''],
        'sex'      => ['except' => ''],
        'flags'    => ['except' => ''],
        'scoreMin' => ['except' => null],
        'page'     => ['except' => 1],
    ];

    public function updating($name, $value)
    {
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
        $team = Auth::user()->currentTeam;

        $dogs = Dog::query()
            ->where('team_id', $team->id)
            // Eager-load latestEvaluation; fully-qualify columns AND only select existing fields
            ->with(['latestEvaluation' => function ($q) {
                $t = $q->getModel()->getTable(); // usually 'evaluations'
                $q->select(
                    "$t.id",
                    "$t.dog_id",
                    "$t.score",
                    "$t.category_scores",
                    "$t.red_flags",
                    "$t.created_at"
                );
            }])
            ->withCount('evaluations');

        // Search
        if ($this->q !== '') {
            $q = trim($this->q);
            $dogs->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('breed', 'like', "%{$q}%")
                   ->orWhere('serial_number', 'like', "%{$q}%");
            });
        }

        // Sex
        if ($this->sex === 'male' || $this->sex === 'female') {
            $dogs->where('sex', $this->sex);
        }

        // Score min (filters by global score on latest eval)
        if (is_numeric($this->scoreMin)) {
            $min = max(0, min(100, (int) $this->scoreMin));
            $dogs->whereHas('latestEvaluation', function ($q) use ($min) {
                $q->where('score', '>=', $min);
            });
        }

        // Flags (JSON length check; if your DB lacks JSON_LENGTH, adjust accordingly)
        if ($this->flags === 'with') {
            $dogs->whereHas('latestEvaluation', function ($q) {
                $q->whereRaw("json_length(red_flags) > 0");
            });
        } elseif ($this->flags === 'none') {
            $dogs->where(function ($q) {
                $q->doesntHave('latestEvaluation')
                  ->orWhereHas('latestEvaluation', function ($qq) {
                      $qq->whereRaw("json_length(red_flags) = 0");
                  });
            });
        }

        $rows = $dogs->latest()->paginate(10);

        $headers = [
            ['index' => 'name',  'label' => 'Dog'],
            ['index' => 'age',   'label' => 'Age'],
            ['index' => 'sex',   'label' => 'Sex'],
            ['index' => 'score', 'label' => 'Scores (C/S/T)'],
            ['index' => 'flag',  'label' => 'Flag'],
            ['index' => 'action'],
        ];

        return view('livewire.dogs.table', compact('headers', 'rows'));
    }
}
