<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Admin\Settings\SettingsController as BaseSettingsController;

class MainSettingsController extends BaseSettingsController
{
    /**
     * Показать главную страницу настроек
     */
    public function index()
    {
        return view('admin.settings.index');
    }
} 