<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:data {output=export.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data for migration to ra9';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting export...');

        // 1. daysデータを持つユーザーを取得（SoftDeletesにより論理削除は自動除外）
        $users = User::whereHas('entities.days')
            ->with(['entities.days'])
            ->get();

        if ($users->isEmpty()) {
            $this->error('No users with days data found.');

            return Command::FAILURE;
        }

        // 2. JSON構造の構築
        $data = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'source' => 'recordingAnniversaries8',
            'users' => [],
        ];

        foreach ($users as $user) {
            $newUuid = (string) Str::uuid();

            $userData = [
                'old_id' => $user->id,
                'new_uuid' => $newUuid,
                'email' => $user->email,
                'name' => $user->name,
                'email_verified' => $user->email_verified_at?->toIso8601String(),
                'created_at' => ($user->created_at ?? now())->toIso8601String(),
                'updated_at' => ($user->updated_at ?? now())->toIso8601String(),
                'collections' => [],
            ];

            foreach ($user->entities as $entity) {
                $collectionData = [
                    'old_id' => $entity->id,
                    'name' => $entity->name,
                    'description' => $entity->desc,
                    'created_at' => ($entity->created_at ?? now())->toIso8601String(),
                    'updated_at' => ($entity->updated_at ?? now())->toIso8601String(),
                    'anniversaries' => [],
                ];

                foreach ($entity->days as $day) {
                    $collectionData['anniversaries'][] = [
                        'old_id' => $day->id,
                        'name' => $day->name,
                        'description' => $day->desc,
                        'anniversary_date' => $day->anniv_at,
                        'created_at' => ($day->created_at ?? now())->toIso8601String(),
                        'updated_at' => ($day->updated_at ?? now())->toIso8601String(),
                    ];
                }

                $userData['collections'][] = $collectionData;
            }

            $data['users'][] = $userData;
        }

        // 3. 統計情報
        $data['stats'] = [
            'total_users' => count($data['users']),
            'total_collections' => collect($data['users'])->sum(fn ($u) => count($u['collections'])),
            'total_anniversaries' => collect($data['users'])->sum(fn ($u) => collect($u['collections'])->sum(fn ($c) => count($c['anniversaries']))
            ),
        ];

        // 4. ファイル出力（圧縮形式）
        $output = $this->argument('output');
        $outputPath = storage_path('app/'.$output);
        file_put_contents($outputPath, json_encode($data, JSON_UNESCAPED_UNICODE));

        $this->info("✓ Exported {$data['stats']['total_users']} users to {$outputPath}");
        $this->info("  - Collections: {$data['stats']['total_collections']}");
        $this->info("  - Anniversaries: {$data['stats']['total_anniversaries']}");

        return Command::SUCCESS;
    }
}
