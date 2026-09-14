<?php

namespace Database\Seeders;

use App\Tools\Lab\IdpSeedData;
use App\Tools\Lab\Models\IdpActivity;
use App\Tools\Lab\Models\IdpAttachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds the IDP Tracker's 30 activities and pulls in the real attachment
 * files that used to live next to the standalone tool, one time, from
 * whichever machine still has that folder. `DUMP_PATH` is this developer's
 * own machine — on any other machine this step is skipped, not failed, so
 * `db:seed` still works everywhere else.
 *
 * Folder → activity matching mirrors the original tool's own naming: each
 * folder is `slugify(objective) + '-' + activity id`, confirmed against the
 * real folders before writing this.
 */
class LabIdpTrackerSeeder extends Seeder
{
    private const DUMP_PATH = 'C:\Users\DEV-PC\Desktop\Dump\idp-tracker';

    /**
     * Folder name => activity id, for the folders known to hold real
     * attachments. Matched by hand once rather than re-deriving the
     * original tool's slugify() in PHP for a handful of one-time folders.
     */
    private const ATTACHMENT_FOLDERS = [
        'lead-a-small-team-through-a-project-delivery-9' => 9,
        'develop-mentoring-and-coaching-skills-10' => 10,
        'learn-architectural-decision-making-from-senior--1' => 1,
        'master-owasp-top-10-and-secure-development-princ-5' => 5,
        'understand-core-architectural-patterns-and-princ-2' => 2,
        'understand-leadership-and-communication-framewor-11' => 11,
    ];

    public function run(): void
    {
        IdpSeedData::apply();

        $this->importAttachments();
    }

    private function importAttachments(): void
    {
        if (! File::isDirectory(self::DUMP_PATH)) {
            $this->command?->line('  <fg=gray>IDP Tracker: Dump folder not found on this machine — skipping attachment import.</>');

            return;
        }

        foreach (self::ATTACHMENT_FOLDERS as $folder => $activityId) {
            $activity = IdpActivity::find($activityId);
            $sourceDir = self::DUMP_PATH.DIRECTORY_SEPARATOR.$folder;

            if (! $activity || ! File::isDirectory($sourceDir)) {
                continue;
            }

            // Re-running db:seed shouldn't duplicate attachments already pulled in.
            if ($activity->attachments()->exists()) {
                continue;
            }

            foreach (File::files($sourceDir) as $file) {
                $diskPath = 'idp/'.$activityId.'/'.Str::uuid().'-'.$file->getFilename();

                Storage::disk('local')->put($diskPath, File::get($file->getPathname()));

                IdpAttachment::create([
                    'activity_id' => $activityId,
                    'disk' => 'local',
                    'path' => $diskPath,
                    'original_name' => $file->getFilename(),
                    'size' => $file->getSize(),
                ]);
            }
        }
    }
}
