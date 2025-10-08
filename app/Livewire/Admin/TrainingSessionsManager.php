<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TrainingSession;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TrainingSessionsManager extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public string $video_url = '';
    public $pdf_upload = null; // UploadedFile
    public ?int $duration_minutes = null;
    public bool $is_active = true;

    public ?int $editId = null;

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'video_url' => ['nullable','url'],
            'pdf_upload' => ['nullable','file','mimes:pdf','max:20480'],
            'duration_minutes' => ['nullable','integer','min:1','max:1440'],
            'is_active' => ['boolean'],
        ]);

        $slug = Str::slug($this->name);

        $pdfPath = null;
        if ($this->pdf_upload) {
            $pdfPath = $this->pdf_upload->store('training_pdfs','public');
        }

        if ($this->editId) {
            $sess = TrainingSession::findOrFail($this->editId);
            $sess->name = $this->name;
            $sess->slug = $sess->slug ?: $slug;
            $sess->description = $this->description ?: null;
            $sess->video_url = $this->video_url ?: null;
            if ($pdfPath) {
                // cleanup old?
                if ($sess->pdf_path && Storage::disk('public')->exists($sess->pdf_path)) {
                    // optional: Storage::disk('public')->delete($sess->pdf_path);
                }
                $sess->pdf_path = $pdfPath;
            }
            $sess->duration_minutes = $this->duration_minutes;
            $sess->is_active = $this->is_active;
            $sess->save();
        } else {
            TrainingSession::create([
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description ?: null,
                'video_url' => $this->video_url ?: null,
                'pdf_path' => $pdfPath,
                'duration_minutes' => $this->duration_minutes,
                'is_active' => $this->is_active,
            ]);
        }

        $this->reset(['name','description','video_url','pdf_upload','duration_minutes','is_active','editId']);
        session()->flash('success','Session saved.');
    }

    public function edit(int $id): void
    {
        $s = TrainingSession::findOrFail($id);
        $this->editId = $s->id;
        $this->name = $s->name;
        $this->description = $s->description ?? '';
        $this->video_url = $s->video_url ?? '';
        $this->duration_minutes = $s->duration_minutes;
        $this->is_active = (bool) $s->is_active;
    }

    public function delete(int $id): void
    {
        TrainingSession::where('id', $id)->delete();
        session()->flash('success','Session deleted.');
    }

    public function toggleActive(int $id): void
    {
        $s = TrainingSession::findOrFail($id);
        $s->is_active = !$s->is_active;
        $s->save();
    }

    public function render()
    {
        $sessions = TrainingSession::orderByDesc('created_at')->get();
        return view('livewire.admin.training-sessions-manager', [
            'sessions' => $sessions,
        ])->layout('layouts.app');
    }
}
