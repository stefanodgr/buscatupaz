<?php

namespace App\Console\Commands;

use App\Models\UserEvaluation;
use App\Models\Statistics;
use Illuminate\Console\Command;

class MigrateUserEvaluationToStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:evaluation_to_statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the users evaluations to statistics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = new \DateTime('first day of august 2018');
        $new_date = new \DateTime('first day of july 2018');
        $evaluations = UserEvaluation::where("updated_at","<",$date->format("Y-m-d H:i:s"))->get();
        $this->info("\nQualifications before ".$date->format("F j, Y").": ".count($evaluations));

        foreach($evaluations as $evaluation) {
            $statistic = Statistics::create(["user_id"=>$evaluation->user_id, "type"=>"Evaluation_teacher", "data_x"=>$evaluation->teacher_id, "data_y"=>$evaluation->evaluation]);
            Statistics::where("id",$statistic->id)->update(["created_at"=>$new_date->format("Y-m-d H:i:s"), "updated_at"=>$new_date->format("Y-m-d H:i:s")]);
        }
        
        $this->info("\nMigration completed!");
    }
}
