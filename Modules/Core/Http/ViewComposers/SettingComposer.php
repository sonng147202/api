<?php
/**
 * Created by PhpStorm.
 * User: phongbui
 * Date: 11/09/2017
 * Time: 09:24
 */
namespace Modules\Core\Http\ViewComposers;

use Illuminate\View\View;
use Modules\Core\Models\Setting;

class SettingComposer
{
    /**
     * @var Setting
     */
    protected $settings;

    /**
     * SettingComposer constructor.
     * @param Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->settings = $setting;
    }

    public function compose(View $view)
    {
        $view->with('sys_settings', $this->settings->getSettings());
    }
}