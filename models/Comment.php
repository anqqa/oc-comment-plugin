<?php namespace Klubitus\Comment\Models;

use Auth;
use Cms\Classes\Controller;
use Model;
use October\Rain\Database\Traits\Validation;
use RainLab\User\Models\User as UserModel;

/**
 * Comment Model
 */
class Comment extends Model {
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'comments';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['user_id', 'is_private', 'content'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => 'RainLab\User\Models\User',
    ];
    public $morphTo = [
        'commentable' => [],
    ];

    /**
     * @var  array  Validation rules.
     */
    public $rules = [
        'user_id' => 'required',
        'content' => 'required',
    ];


    /**
     * Can the user delete this?
     *
     * @param  UserModel|int  $user
     * @param  int            $commentable_user_id
     * @return  bool
     */
    public function canDelete($user = null, $commentable_user_id = null) {
        if (is_null($user)) {
            $user = Auth::getUser();
        }

        if (!$user) {
            return false;
        }

        $user_id = $user instanceof UserModel ? $user->id : (int)$user;

        return in_array($user_id, [$this->user_id, $commentable_user_id]);
    }


    /**
     * Can the user edit this?
     *
     * @param  UserModel|int  $user
     * @return  bool
     */
    public function canEdit($user = null) {
        if (is_null($user)) {
            $user = Auth::getUser();
        }

        if (!$user) {
            return false;
        }

        $user_id = $user instanceof UserModel ? $user->id : (int)$user;

        return $user_id == $this->user_id;
    }


    /**
     * Add comment to a commentable model.
     *
     * @param  Model      $model
     * @param  UserModel  $user
     * @param  array      $data
     * @return  Comment
     */
    public static function createInModel(Model $model, UserModel $user, $data) {
        return $model->comments()->create([
            'user_id'    => $user->id,
            'is_private' => (bool)array_get($data, 'is_private'),
            'content'    => trim(array_get($data, 'comment')),
        ]);
    }


    /**
     * Set comment user url.
     *
     * @param  string      $pageName
     * @param  Controller  $controller
     * @return  string
     */
    public function setUserUrl($pageName, Controller $controller) {
        $params = [
            'user_id'  => $this->user->id,
            'username' => $this->user->username
        ];

        return $this->userUrl = $controller->pageUrl($pageName, $params, false);
    }

}
