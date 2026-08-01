<?php

namespace App\Platform\Livewire\Hub;

use App\Platform\Contracts\ProvidesRecentActivity;
use App\Platform\Support\ActivityItem;
use App\Platform\Support\Tool;
use App\Platform\Support\ToolRegistry;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The platform launcher. Renders whatever config/tools.php lists — it has no
 * knowledge of any specific tool, which is what keeps adding one cheap.
 */
#[Layout('layouts.app', ['title' => 'ICVault — Hub'])]
class HubPage extends Component
{
    public function render(ToolRegistry $registry)
    {
        $tools = $registry->enabled();

        return view('platform.hub.hub-page', [
            'tools' => $tools,
            'activity' => $this->collectActivity($tools),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Tool>  $tools
     * @return array<int, ActivityItem>
     */
    private function collectActivity($tools): array
    {
        return $tools
            ->flatMap(function (Tool $tool) {
                $provider = $tool->summaryProvider ? app($tool->summaryProvider) : null;

                if (! $provider instanceof ProvidesRecentActivity) {
                    return [];
                }

                return collect($provider->recentActivity())
                    ->map(fn (ActivityItem $item) => $item->for($tool));
            })
            ->sortByDesc(fn (ActivityItem $item) => $item->at)
            ->take(6)
            ->values()
            ->all();
    }
}
