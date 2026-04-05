<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\TaskModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TaskModel::query()
            ->where('user_id', $request->user()->id);

        $status = $request->string('status')->toString();
        if ($status === 'pending') {
            $query->where('status', TaskModel::STATUS_PENDING);
        } elseif ($status === 'completed') {
            $query->where('status', TaskModel::STATUS_COMPLETED);
        }

        $priority = $request->string('priority')->toString();
        if (in_array($priority, ['low', 'medium', 'high'], true)) {
            $query->where('priority', $priority);
        }

        $dueFrom = $request->input('due_from');
        $dueTo = $request->input('due_to');
        if (is_string($dueFrom) && $dueFrom !== '') {
            $query->whereDate('due_date', '>=', $dueFrom);
        }
        if (is_string($dueTo) && $dueTo !== '') {
            $query->whereDate('due_date', '<=', $dueTo);
        }

        $search = $request->input('search');
        if (is_string($search) && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $view = $request->string('view')->toString();
        if ($view === 'week') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $query->whereBetween('due_date', [$start->toDateString(), $end->toDateString()]);
        }

        $tasks = $query
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->orderByDesc('id')
            ->get();

        $meetingsForCalendar = Meeting::query()
            ->where('user_id', $request->user()->id)
            ->whereBetween('meeting_date', [
                now()->subMonths(3)->startOfMonth()->toDateString(),
                now()->addMonths(15)->endOfMonth()->toDateString(),
            ])
            ->orderBy('meeting_date')
            ->orderBy('meeting_time')
            ->get(['id', 'title', 'meeting_date', 'meeting_time', 'status']);

        return Inertia::render('Tasks/TaskApp', [
            'tasks' => $tasks,
            'meetings' => $meetingsForCalendar,
            'filters' => [
                'status' => $status === '' ? 'all' : $status,
                'priority' => $priority === '' ? 'all' : $priority,
                'due_from' => is_string($dueFrom) ? $dueFrom : '',
                'due_to' => is_string($dueTo) ? $dueTo : '',
                'search' => is_string($search) ? trim($search) : '',
                'view' => $view === 'week' ? 'week' : 'all',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        TaskModel::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, TaskModel $task)
    {
        $this->ensureOwnedBy($request, $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'nullable|in:pending,completed',
        ]);

        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $task->applyStatus($validated['status']);
            unset($validated['status']);
        }

        $task->fill($validated);
        $task->save();

        return back();
    }

    public function toggle(Request $request, TaskModel $task)
    {
        $this->ensureOwnedBy($request, $task);
        $task->toggleStatus();
        $task->save();

        return back();
    }

    public function destroy(Request $request, TaskModel $task)
    {
        $this->ensureOwnedBy($request, $task);
        $task->delete();

        return back();
    }

    private function ensureOwnedBy(Request $request, TaskModel $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403);
    }
}
