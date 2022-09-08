<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Setting;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // Get settings value
        $settings = Setting::getSettings();

        return view('core::setting.index', compact('settings'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSetting(Request $request)
    {
        try {
            Setting::updateSettings($request->all());

            return redirect()->back();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return redirect()->back();
        }
    }
}
