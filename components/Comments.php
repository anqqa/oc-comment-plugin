<?php namespace Klubitus\Comment\Components;

use ApplicationException;
use Auth;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Event;
use Exception;
use Flash;
use Klubitus\Comment\Models\Comment as CommentModel;
use Lang;
use Model;
use October\Rain\Support\Collection;
use RainLab\User\Models\User;


class Comments extends ComponentBase {

    /**
     * @var  Collection  CommentModel
     */
    public $comments = null;

    /**
     * @var  Model  Parent model
     */
    public $commentable = null;

    /**
     * @var  string
     */
    public $userPage;


    public function componentDetails() {
        return [
            'name'        => 'Comments',
            'description' => 'Comments section.'
        ];
    }


    public function defineProperties() {
        return [
            'userPage' => [
                'title'       => 'Profile Page',
                'description' => 'Page name for user profile.',
                'type'        => 'dropdown',
            ],
            'commentableClass' => [
                'title'       => 'Commentable Class',
                'description' => 'Model class for commentable.',
                'type'        => 'string',
            ],
            'pageCommentable' => [
                'title'       => 'Commentable Model',
                'description' => 'Page parameter name for commentable model.',
                'type'        => 'string',
                'default'     => 'commentable'
            ],
        ];
    }


    public function getUserPageOptions() {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


    public function onComment() {
        try {
            if (!$user = $this->user()) {
                throw new ApplicationException(Lang::get('klubitus.comment::lang.comment.not_logged_in'));
            }

            $modelId    = (int)post('id');
            $modelClass = $this->property('commentableClass');
            $model      = $modelClass::findOrFail($modelId);
            $comment    = CommentModel::createInModel($model, $user, post());

            $comment->setUserUrl($this->property('userPage'), $this->controller);
            $this->page['comment'] = $comment;

            // Extensibility
            Event::fire('klubitus.comment.post', [$this, $comment]);
            $this->fireEvent('comment.post', [$comment]);

        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }
    }


    public function onRun() {
        $this->prepareVars();

        if ($this->commentable) {
            $this->prepareComments();
        }
    }


    protected function prepareComments() {
        $this->commentable->load('comments.user');

        /** @var  CommentModel  $comment */
        foreach ($this->commentable->comments as $comment) {
            $comment->setUserUrl($this->userPage, $this->controller);
        }

        $this->comments = $this->commentable->comments;
    }


    protected function prepareVars() {
        $this->commentable = $this->page[$this->property('pageCommentable')];
        $this->userPage = $this->property('userPage');
    }


    /**
     * Authenticated user, if any.
     *
     * @return  User|null
     */
    public function user() {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

}
