<?php

namespace App\Http\Controllers\Admin;

use App\Models\InformationContents;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class InformationContentsController extends Controller
{
    public function __construct() {
        View::share('menu_active', "information_contents");
    }

    public function getIndex() {
        return view("admin.information_contents.list", ["breadcrumb" => true]);
    }

    public function getList() {
        $information_contents = InformationContents::all();

        $information_contents_list = [];
        foreach($information_contents as $information) {

        	$upper_content = null;
        	if($information->information_content_id) {
        		$upper_content = InformationContents::find($information->information_content_id);
        		if($upper_content) {
        			$upper_content = $upper_content->name;
        		}
        	}

            $information_contents_list[] = [$information->name, $information->type==null?"None":"City Information Medellin", $upper_content==null?"None":$upper_content, $information->state==1?"Active":"Inactive", $information->created_at->format("Y/m/d"), '<a href="'.route("admin_information_contents_edit",["information_content_id"=>$information->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a> <a href="'.route("admin_information_contents_trash",["information_content_id"=>$information->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>'];
        }

        return response()->json(['data' => $information_contents_list]);
    }

    public function getCreate() {
    	$information_contents = InformationContents::where("information_content_id",null)->get();

        foreach($information_contents as $information) {
            if($information->type=="city_info_medellin") {
                $information->type_name="City Information Medellin";
            }
        }

        return view("admin.information_contents.create", ["breadcrumb" => true, "information_contents" => $information_contents]);
    }

    public function create(Request $request) {
        $data = $request->only(["name", "slug", "type", "order", "state", "description", "information_content_id"]);
        $slug = $data["slug"];

        if(!empty($slug)) {
            $checkSlug = InformationContents::where("slug",$slug)->first();
            if($checkSlug) {
                //slug exist
                return redirect()->back()->withErrors(["Slug already in use"]);
            }else {
                $slug = str_replace(' ', '-', $slug); // Replaces all spaces with hyphens.
                $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.
            }
        }else {
            $slug = str_replace(' ', '-', $data["name"]); // Replaces all spaces with hyphens.
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.

            $testSlug = $slug;
            $i = 1;
            do {
                $checkSlug = InformationContents::where("slug",$testSlug)->first();
                if(!$checkSlug) {
                    $testSlug = $slug."-(".$i.")";
                    $i++;
                }

            } while($checkSlug);
            $slug = $testSlug;
        }

        $data["slug"] = $slug;

        if(!$data["state"]) {
            $data["state"] = 0;
        }else {
            $data["state"] = 1;
        }

        if($data["type"]=="none") {
        	$data["type"] = null;
        }

        if($data["information_content_id"]=="none") {
        	$data["information_content_id"] = null;
        }

        if(empty($data["description"])) {
            $data["description"] = '';
        }

        $information_content = InformationContents::create($data);

        return redirect()->route("admin_information_contents_edit",["information_content_id"=>$information_content->id])->with(["message_info"=>"Information Content created"]);
    }

    public function getEdit($information_content_id) {
        $information_content = InformationContents::find($information_content_id);

        if(!$information_content){
            return redirect()->route("admin_information_contents")->with(["message_info" => "Information Content ".$information_content_id." does not exist"]);
        }

        $information_contents = InformationContents::where("information_content_id",null)->get();

        foreach($information_contents as $information) {
            if($information->type=="city_info_medellin") {
                $information->type_name="City Information Medellin";
            }
        }

        return view("admin.information_contents.edit",["breadcrumb" => true, "information_content" => $information_content, "information_contents" => $information_contents]);
    }

    public function update(Request $request){
        $data = $request->only(["name", "slug", "type", "order", "state", "description", "information_content_id"]);

        $info_content_id = $request->get("info_content_id");
        $information_content = InformationContents::where("id",$info_content_id)->first();
        $slug = $data["slug"];

        if(!empty($slug)) {
            $checkSlug = InformationContents::where("slug",$slug)->first();
            if($checkSlug && $checkSlug->id!=$information_content->id) {
                //slug exist
                return redirect()->back()->withErrors(["Slug already in use"]);
            }elseif(!$checkSlug) {
                $slug = str_replace(' ', '-', $slug); // Replaces all spaces with hyphens.
                $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.
            }
        }else {
            //generate slug
            $slug = str_replace(' ', '-', $data["name"]); // Replaces all spaces with hyphens.
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.

            $testSlug = $slug;
            $i = 1;
            do {
                $checkSlug = InformationContents::where("slug",$testSlug)->first();
                if(!$checkSlug) {
                    $testSlug = $slug."-(".$i.")";
                    $i++;
                }

            } while($checkSlug);
            $slug = $testSlug;
        }

        $data["slug"] = $slug;

        if(!$data["state"]) {
            $data["state"] = 0;
        }else {
            $data["state"] = 1;
        }

        if($data["type"]=="none") {
        	$data["type"] = null;
        }

        if($data["information_content_id"]=="none") {
        	$data["information_content_id"] = null;
        }

        if(empty($data["description"])) {
            $data["description"] = '';
        }

        InformationContents::where("id",$info_content_id)->update($data);

        return redirect()->route("admin_information_contents_edit",["information_content_id"=>$info_content_id])->with(["message_info"=>"Information Content updated"]);
    }

    public function getTrash($information_content_id) {
        $information_content = InformationContents::find($information_content_id);

        if(!$information_content){
            return redirect()->route("admin_information_contents")->with(["message_info" => "Information Content ".$information_content_id." does not exist"]);
        }

        return view("admin.information_contents.trash",["breadcrumb" => true, "information_content" => $information_content]);
    }

    public function delete(Request $request) {
        $info_content_id = $request->get("info_content_id");

        InformationContents::where("id",$info_content_id)->delete();

        return redirect()->route("admin_information_contents")->with(["message_info"=>"Information Content deleted"]);
    }
}
