<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigratePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migratepdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy ALL PDF Lesson SLUG to Lesson ID';

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
        //Copy PDF files
        $lessons=DB::table("lessons")->get();

        foreach($lessons as $lesson){

            $fileLesson=Storage::disk("uploads")->exists('assets/lessons/pdf/'.$lesson->slug.'.pdf');

            if($fileLesson){
                $newFileExist=Storage::disk("uploads")->exists('assets/lessons/pdf/'.$lesson->id.'.pdf');
                if($newFileExist){
                    Storage::disk("uploads")->delete('assets/lessons/pdf/'.$lesson->id.'.pdf');
                }
                Storage::disk("uploads")->move('assets/lessons/pdf/'.$lesson->slug.'.pdf', 'assets/lessons/pdf/'.$lesson->id.'.pdf');
            }
            //$lesson->slug;
        }
    }
}
