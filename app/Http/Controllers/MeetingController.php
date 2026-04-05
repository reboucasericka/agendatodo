<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MeetingController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->string('tab')->toString();
        $allowed = ['all', 'kanban', 'calendar', 'list', 'completed'];
        if (! in_array($tab, $allowed, true)) {
            $tab = 'all';
        }

        $meetings = Meeting::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('meeting_date')
            ->orderBy('meeting_time')
            ->get();

        $upcoming = Meeting::query()
            ->where('user_id', $request->user()->id)
            ->where('status', '!=', Meeting::STATUS_COMPLETED)
            ->whereDate('meeting_date', '>=', now()->toDateString())
            ->orderBy('meeting_date')
            ->orderBy('meeting_time')
            ->limit(8)
            ->get();

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'upcoming' => $upcoming,
            'tab' => $tab,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedMeeting($request);
        $data['user_id'] = $request->user()->id;

        Meeting::create($data);

        return redirect()->route('meetings.index', ['tab' => $request->input('redirect_tab', 'all')]);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $this->ensureOwnedBy($request, $meeting);

        $meeting->update($this->validatedMeeting($request));

        return redirect()->route('meetings.index', ['tab' => $request->input('redirect_tab', 'all')]);
    }

    public function destroy(Request $request, Meeting $meeting)
    {
        $this->ensureOwnedBy($request, $meeting);
        $meeting->delete();

        return redirect()->route('meetings.index', ['tab' => $request->input('redirect_tab', 'all')]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedMeeting(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:planning,scheduled,completed',
            'duration' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:255',
            'participants_text' => 'nullable|string',
        ]);

        $data['participants'] = $this->parseParticipants($data['participants_text'] ?? null);
        unset($data['participants_text']);

        return $data;
    }

    /**
     * @return list<string>
     */
    private function parseParticipants(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        return collect(preg_split('/[,;\n]+/', $raw))
            ->map(fn (string $s) => trim($s))
            ->filter()
            ->values()
            ->all();
    }

    private function ensureOwnedBy(Request $request, Meeting $meeting): void
    {
        abort_unless($meeting->user_id === $request->user()->id, 403);
    }
}
