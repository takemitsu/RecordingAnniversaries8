<?php

namespace App\Console\Commands;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = DB::connection('mysql_old')->table('users')->get();
        foreach ($users as $old_user) {
            $user = new User();
            $user->name = $old_user->name;
            $user->email = $old_user->email;
            $user->password = $old_user->password;
            $user->created_at = $old_user->created_at;
            $user->updated_at = $old_user->updated_at;
            $user->save();

            $entities = DB::connection('mysql_old')->table('entities')->where('user_id', $old_user->id)->get();
            foreach ($entities as $old_entity) {
                $entity = new Entity();
                $entity->user_id = $user->id;
                $entity->name = $old_entity->name;
                $entity->desc = $old_entity->desc;
                $entity->status = $old_entity->status;
                $entity->created_at = $old_entity->created_at;
                $entity->updated_at = $old_entity->updated_at;
                $entity->deleted_at = $old_entity->deleted_at;
                $entity->save();

                $days = DB::connection('mysql_old')->table('days')->where('entity_id', $old_entity->id)->get();
                foreach ($days as $old_day) {
                    $day = new Day();
                    $day->entity_id = $entity->id;
                    $day->name = $old_day->name;
                    $day->desc = $old_day->desc;
                    $day->anniv_at = $old_day->anniv_at;
                    $day->created_at = $old_day->created_at;
                    $day->updated_at = $old_day->updated_at;
                    $day->deleted_at = $old_day->deleted_at;
                    $day->save();
                }
            }
        }
    }
}
