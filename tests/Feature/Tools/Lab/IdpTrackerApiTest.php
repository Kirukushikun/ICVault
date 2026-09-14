<?php

namespace Tests\Feature\Tools\Lab;

use App\Models\User;
use App\Tools\Lab\IdpSeedData;
use App\Tools\Lab\Models\IdpActivity;
use App\Tools\Lab\Models\IdpAttachment;
use App\Tools\Lab\Models\IdpReview;
use App\Tools\Lab\Models\IdpSnapshot;
use App\Tools\Lab\Models\IdpSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdpTrackerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        IdpSeedData::apply();
    }

    // ---------------------------------------------------------------- state

    public function test_state_lists_every_seeded_activity(): void
    {
        $response = $this->getJson(route('lab.idp.state'))->assertOk();

        $response->assertJsonCount(30, 'activities');
        $response->assertJsonPath('activities.0.id', 0);
        $response->assertJsonPath('activities.0.area', 'System Architecture & Solution Design');
        $response->assertJsonStructure([
            'activities' => [['id', 'area', 'obj', 'desc', 'type', 'status', 'target', 'notes', 'sources', 'files']],
            'reviews',
            'history',
        ]);
    }

    // ----------------------------------------------------------- activities

    public function test_updating_status_updates_the_activity_and_records_todays_snapshot(): void
    {
        $this->assertSame(0, IdpSnapshot::count());

        $response = $this->patchJson(route('lab.idp.activities.update', 0), ['status' => 'Completed'])
            ->assertOk();

        $this->assertSame('Completed', $response->json('activity.status'));
        $this->assertSame('Completed', IdpActivity::find(0)->status->value);

        $snapshot = IdpSnapshot::sole();
        $this->assertSame(now()->toDateString(), $snapshot->snapshot_date->toDateString());
        $this->assertSame($response->json('history.0.pct'), $snapshot->overall_pct);
    }

    public function test_a_second_status_change_the_same_day_updates_the_same_snapshot_row(): void
    {
        $this->patchJson(route('lab.idp.activities.update', 0), ['status' => 'Completed'])->assertOk();
        $this->patchJson(route('lab.idp.activities.update', 1), ['status' => 'Completed'])->assertOk();

        $this->assertSame(1, IdpSnapshot::count());
    }

    public function test_updating_notes_or_target_date_does_not_touch_the_snapshot(): void
    {
        $this->patchJson(route('lab.idp.activities.update', 0), [
            'notes' => 'Blocked on review',
            'target_date' => '2026-10-01',
        ])->assertOk();

        $this->assertSame(0, IdpSnapshot::count());
        $this->assertSame('Blocked on review', IdpActivity::find(0)->notes);
        $this->assertSame('2026-10-01', IdpActivity::find(0)->target_date->toDateString());
    }

    public function test_an_unrecognised_status_is_rejected(): void
    {
        $this->patchJson(route('lab.idp.activities.update', 0), ['status' => 'Nope'])
            ->assertInvalid('status');
    }

    // --------------------------------------------------------------- sources

    public function test_a_source_can_be_added_and_deleted(): void
    {
        $source = $this->postJson(route('lab.idp.sources.store', 0), [
            'label' => 'Docs',
            'url' => 'https://example.com',
        ])->assertOk()->json();

        $this->assertDatabaseHas('idp_sources', ['id' => $source['id'], 'activity_id' => 0]);

        $this->deleteJson(route('lab.idp.sources.destroy', $source['id']))->assertNoContent();

        $this->assertDatabaseMissing('idp_sources', ['id' => $source['id']]);
    }

    // ----------------------------------------------------------- attachments

    public function test_an_attachment_can_be_uploaded_downloaded_and_deleted(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf');

        $attachment = $this->postJson(route('lab.idp.attachments.store', 0), ['file' => $file])
            ->assertOk()
            ->json();

        $this->assertSame('notes.pdf', $attachment['name']);
        $this->assertFalse($attachment['isImage']);

        $stored = IdpAttachment::findOrFail($attachment['id']);
        Storage::disk('local')->assertExists($stored->path);

        $this->get($attachment['url'])->assertOk();

        $this->deleteJson(route('lab.idp.attachments.destroy', $attachment['id']))->assertNoContent();

        Storage::disk('local')->assertMissing($stored->path);
        $this->assertDatabaseMissing('idp_attachments', ['id' => $attachment['id']]);
    }

    public function test_an_image_attachment_is_flagged_as_an_image(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('photo.jpg', 50, 'image/jpeg');

        $attachment = $this->postJson(route('lab.idp.attachments.store', 0), ['file' => $file])
            ->assertOk()
            ->json();

        $this->assertTrue($attachment['isImage']);
    }

    // -------------------------------------------------------------- reviews

    public function test_a_review_can_be_added_and_deleted(): void
    {
        $review = $this->postJson(route('lab.idp.reviews.store'), [
            'date' => '2026-09-01',
            'progress' => 'Shipped the migration',
        ])->assertOk()->json();

        $this->assertSame('2026-09-01', $review['date']);
        $this->assertDatabaseHas('idp_reviews', ['id' => $review['id']]);

        $this->deleteJson(route('lab.idp.reviews.destroy', $review['id']))->assertNoContent();

        $this->assertDatabaseMissing('idp_reviews', ['id' => $review['id']]);
    }

    // ---------------------------------------------------------------- reset

    public function test_reset_restores_seed_values_but_keeps_sources_attachments_and_reviews(): void
    {
        Storage::fake('local');

        $this->patchJson(route('lab.idp.activities.update', 0), [
            'status' => 'Completed',
            'notes' => 'My own note',
            'target_date' => '2026-01-01',
        ])->assertOk();

        $source = IdpSource::create(['activity_id' => 0, 'label' => 'Docs', 'url' => 'https://example.com']);
        $attachment = IdpAttachment::create([
            'activity_id' => 0, 'disk' => 'local', 'path' => 'idp/0/keep.txt', 'original_name' => 'keep.txt', 'size' => 3,
        ]);
        $review = IdpReview::create(['review_date' => '2026-09-01', 'progress' => 'Kept across reset']);

        $this->postJson(route('lab.idp.reset'))->assertNoContent();

        $activity = IdpActivity::find(0);
        $this->assertSame('In Progress', $activity->status->value); // seed default for activity 0
        $this->assertNull($activity->notes);
        $this->assertNotSame('2026-01-01', $activity->target_date->toDateString());

        $this->assertDatabaseHas('idp_sources', ['id' => $source->id]);
        $this->assertDatabaseHas('idp_attachments', ['id' => $attachment->id]);
        $this->assertDatabaseHas('idp_reviews', ['id' => $review->id]);
    }
}
