<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\User;
use Illuminate\Console\Command;

class ResizeTeachersPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:resizeteachersphotos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize teachers photos';

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
        $teachers = User::where("activated",1)->get();

        $this->info("\nTEACHERS LIST\n");

        foreach($teachers as $key => $teacher)
        {
            if(file_exists("htdocs/assets/users/photos/".$teacher->id.".jpg"))
            {
                $routeOriginal="htdocs/assets/users/photos/".$teacher->id.".jpg";

                $extension=null;

                try {
                    if($image_type=exif_imagetype("htdocs/assets/users/photos/".$teacher->id.".jpg"))
                    {
                        $extension=image_type_to_extension($image_type, false);
                    }
                } catch (\Exception $e){
                    $this->error('Erorr for user: '.$teacher->id." - ".$teacher->email);
                    continue;
                }

                $this->info($teacher->id." - ".$teacher->email." - ".$extension);
                     
                if($extension=="jpeg")
                {
                    $original = imagecreatefromjpeg($routeOriginal);
                }
                elseif($extension=="png") 
                {
                    $original = imagecreatefrompng($routeOriginal);
                }
                elseif($extension=="gif")
                {
                    $original = imagecreatefromgif($routeOriginal);
                }
                     
                $max_width = 100;
                $max_height = 100;
                 
                list($width,$height)=getimagesize($routeOriginal);
                 
                $x_ratio = $max_width / $width;
                $y_ratio = $max_height / $height;

                if(($width <= $max_width) && ($height <= $max_height))
                {
                    $width_final = $width;
                    $height_final = $height;
                }
                elseif(($x_ratio * $height) < $max_height)
                {
                    $height_final = ceil($x_ratio * $height);
                    $width_final = $max_width;
                }
                else
                {
                    $width_final = ceil($y_ratio * $width);
                    $height_final = $max_height;
                }

                $canvas=imagecreatetruecolor($width_final,$height_final); 
                 
                imagecopyresampled($canvas,$original,0,0,0,0,$width_final,$height_final,$width,$height);
                 
                imagedestroy($original);
                 
                $quality=90;
                 
                imagejpeg($canvas,"htdocs/assets/users/photos/".$teacher->id.".jpg",$quality);
            }
        }
    }
}
