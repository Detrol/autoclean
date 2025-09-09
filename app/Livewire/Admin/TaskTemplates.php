<?php

namespace App\Livewire\Admin;

use App\Models\CompletedAdditionalTask;
use App\Models\TaskTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TaskTemplates extends Component
{
    use WithPagination;

    public $showCreateForm = false;
    public $showEditForm = false;
    public $editingTemplateId = null;

    // Form properties
    public $name = '';
    public $description = '';
    public $is_active = true;

    // Filters
    public $searchTerm = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403, 'Endast administratörer har tillgång till denna sida.');
        }
    }

    public function render()
    {
        $query = TaskTemplate::query();

        // Apply filters
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }


        $templates = $query->orderBy('name')
                          ->paginate(10);


        // Get usage statistics
        $templateUsage = CompletedAdditionalTask::selectRaw('task_template_id, COUNT(*) as usage_count')
                                              ->whereNotNull('task_template_id')
                                              ->groupBy('task_template_id')
                                              ->pluck('usage_count', 'task_template_id')
                                              ->toArray();

        return view('livewire.admin.task-templates', [
            'templates' => $templates,
            'templateUsage' => $templateUsage,
        ]);
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    public function openEditForm($templateId)
    {
        $template = TaskTemplate::findOrFail($templateId);
        
        $this->editingTemplateId = $templateId;
        $this->name = $template->name;
        $this->description = $template->description;
        $this->is_active = $template->is_active;
        
        $this->showEditForm = true;
    }

    public function hideEditForm()
    {
        $this->showEditForm = false;
        $this->resetForm();
    }

    public function createTemplate()
    {
        $this->validate();

        // Check for duplicate names
        if (TaskTemplate::where('name', $this->name)->exists()) {
            session()->flash('error', 'En mall med detta namn finns redan.');
            return;
        }

        TaskTemplate::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->hideCreateForm();
        session()->flash('message', 'Mall skapad framgångsrikt!');
    }

    public function updateTemplate()
    {
        $this->validate();

        $template = TaskTemplate::findOrFail($this->editingTemplateId);

        // Check for duplicate names (excluding current template)
        if (TaskTemplate::where('name', $this->name)
                       ->where('id', '!=', $this->editingTemplateId)
                       ->exists()) {
            session()->flash('error', 'En mall med detta namn finns redan.');
            return;
        }

        $template->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->hideEditForm();
        session()->flash('message', 'Mall uppdaterad framgångsrikt!');
    }

    public function toggleActive($templateId)
    {
        $template = TaskTemplate::findOrFail($templateId);
        $template->update(['is_active' => !$template->is_active]);
        
        $status = $template->is_active ? 'aktiverad' : 'inaktiverad';
        session()->flash('message', "Mall {$status}!");
    }

    public function deleteTemplate($templateId)
    {
        $template = TaskTemplate::findOrFail($templateId);
        
        // Check if template is being used
        $usageCount = CompletedAdditionalTask::where('task_template_id', $templateId)->count();
        
        if ($usageCount > 0) {
            session()->flash('error', "Kan inte ta bort mallen eftersom den har använts {$usageCount} gånger. Inaktivera den istället.");
            return;
        }

        $template->delete();
        session()->flash('message', 'Mall borttagen framgångsrikt!');
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }


    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->editingTemplateId = null;
    }
}
