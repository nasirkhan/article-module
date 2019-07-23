<?php

namespace Modules\Article\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Auth;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Log;
use Modules\Article\Http\Requests\Frontend\CommentsRequest;
use Modules\Article\Notifications\NewCommentAdded;

class CommentsController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Comments';

        // module name
        $this->module_name = 'comments';

        // directory path of the module
        $this->module_path = 'comments';

        // module icon
        $this->module_icon = 'fas fa-comments';

        // module model name, path
        $this->module_model = "Modules\Article\Entities\Comment";
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = str_singular($module_name);

        $module_action = 'List';

        $$module_name = $module_model::with('post')->paginate();

        return view("article::frontend.$module_path.index",
        compact('module_title', 'module_name', "$module_name", 'module_path', 'module_icon', 'module_action', 'module_name_singular'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = decode_id($id);

        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = str_singular($module_name);

        $module_action = 'Show';

        $$module_name_singular = $module_model::findOrFail($id);

        return view("article::frontend.$module_name.show",
        compact('module_title', 'module_name', 'module_icon', 'module_action', 'module_name_singular', "$module_name_singular"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(CommentsRequest $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = str_singular($module_name);
        $module_action = 'Store';
        $data = [
            'name'    => $request->name,
            'slug'    => '',
            'comment' => $request->comment,
            'post_id' => decode_id($request->post_id),
            'user_id' => decode_id($request->user_id),
        ];
        // $$module_name_singular = $module_model::create($request->all());

        $$module_name_singular = $module_model::create($data);

        auth()->user()->notify(new NewCommentAdded($$module_name_singular));

        Flash::success("<i class='fas fa-check'></i> New '".str_singular($module_title)."' Added")->important();
        Log::info(label_case($module_title.' '.$module_action)." | '".$$module_name_singular->name.'(ID:'.$$module_name_singular->id.") ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return redirect()->back();
    }
}
