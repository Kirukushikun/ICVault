<?php

namespace App\Tools\Lab\Http\Controllers;

use App\Tools\Lab\Enums\IdpActivityStatus;
use App\Tools\Lab\Models\IdpActivity;
use App\Tools\Lab\Models\IdpAttachment;
use App\Tools\Lab\Models\IdpReview;
use App\Tools\Lab\Models\IdpSnapshot;
use App\Tools\Lab\Models\IdpSource;
use App\Tools\Lab\Services\IdpTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * JSON backend for the IDP Tracker's hand-authored front end. It's a plain
 * controller rather than a Livewire component because the page itself isn't
 * Livewire-reactive — it's a self-contained script that renders everything
 * from a `state` object it now fetches from here instead of localStorage.
 */
final class IdpTrackerController
{
    public function __construct(private readonly IdpTrackerService $service) {}

    public function state()
    {
        return response()->json([
            'activities' => IdpActivity::with(['sources', 'attachments'])
                ->orderBy('id')
                ->get()
                ->map($this->activityPayload(...)),
            'reviews' => IdpReview::orderByDesc('review_date')->get()->map($this->reviewPayload(...)),
            'history' => IdpSnapshot::orderBy('snapshot_date')->get()->map(fn (IdpSnapshot $s) => [
                'date' => $s->snapshot_date->toDateString(),
                'pct' => $s->overall_pct,
            ]),
        ]);
    }

    public function updateActivity(Request $request, IdpActivity $activity)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'in:'.implode(',', array_column(IdpActivityStatus::cases(), 'value'))],
            'target_date' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $activity->update($data);

        if (array_key_exists('status', $data)) {
            $this->service->recordSnapshot();
        }

        return response()->json([
            'activity' => $this->activityPayload($activity->fresh(['sources', 'attachments'])),
            'history' => IdpSnapshot::orderBy('snapshot_date')->get()->map(fn (IdpSnapshot $s) => [
                'date' => $s->snapshot_date->toDateString(),
                'pct' => $s->overall_pct,
            ]),
        ]);
    }

    public function storeSource(Request $request, IdpActivity $activity)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:190'],
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $source = $activity->sources()->create($data);

        return response()->json(['id' => $source->id, 'label' => $source->label, 'url' => $source->url]);
    }

    public function destroySource(IdpSource $source)
    {
        $source->delete();

        return response()->noContent();
    }

    public function storeAttachment(Request $request, IdpActivity $activity)
    {
        $request->validate(['file' => ['required', 'file', 'max:20480']]);

        $file = $request->file('file');
        $name = $file->getClientOriginalName();

        // A same-named re-upload gets a unique disk path without losing the
        // original display name — two "screenshot.png" attachments on one
        // activity are common and shouldn't clobber each other.
        $path = $file->storeAs('idp/'.$activity->id, Str::uuid().'-'.$name);

        $attachment = $activity->attachments()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $name,
            'size' => $file->getSize(),
        ]);

        return response()->json($this->attachmentPayload($attachment));
    }

    public function showAttachment(IdpAttachment $attachment)
    {
        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        return $attachment->isImage()
            ? $disk->response($attachment->path, $attachment->original_name)
            : $disk->download($attachment->path, $attachment->original_name);
    }

    public function destroyAttachment(IdpAttachment $attachment)
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->noContent();
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'progress' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'adjust' => ['nullable', 'string'],
        ]);

        $review = IdpReview::create([
            'review_date' => $data['date'],
            'progress' => $data['progress'] ?? null,
            'challenge' => $data['challenge'] ?? null,
            'adjustment' => $data['adjust'] ?? null,
        ]);

        return response()->json($this->reviewPayload($review));
    }

    public function destroyReview(IdpReview $review)
    {
        $review->delete();

        return response()->noContent();
    }

    public function reset()
    {
        $this->service->reset();

        return response()->noContent();
    }

    private function activityPayload(IdpActivity $activity): array
    {
        return [
            'id' => $activity->id,
            'area' => $activity->area,
            'obj' => $activity->objective,
            'desc' => $activity->description,
            'type' => $activity->type->value,
            'status' => $activity->status->value,
            'target' => $activity->target_date?->toDateString(),
            'notes' => $activity->notes,
            'sources' => $activity->sources->map(fn (IdpSource $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'url' => $s->url,
            ])->values(),
            'files' => $activity->attachments->map($this->attachmentPayload(...))->values(),
        ];
    }

    private function attachmentPayload(IdpAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'isImage' => $attachment->isImage(),
            'url' => route('lab.idp.attachments.show', $attachment),
        ];
    }

    private function reviewPayload(IdpReview $review): array
    {
        return [
            'id' => $review->id,
            'date' => $review->review_date->toDateString(),
            'progress' => $review->progress,
            'challenge' => $review->challenge,
            'adjust' => $review->adjustment,
        ];
    }
}
