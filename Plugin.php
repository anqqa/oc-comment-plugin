<?php namespace Klubitus\Comment;

use System\Classes\PluginBase;

class Plugin extends PluginBase {
    
    public function registerComponents() {
        return [
            'Klubitus\Comment\Components\Comments' => 'comments',
        ];
    }

    
    public function registerSettings() {
    }
    
}
