<?php
namespace Component\oAuth\Models;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Expression as raw;

class UserNotes extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'user_notes';
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function addNote($data)
    {
        $this->app['helper']('ModelLog')->Log();
        $payLoad = [];
        $data['created_at'] = $this->app['helper']('DateTimeFunc')->nowDateTime();

        if($this->app['helper']('Utility')->notEmpty($data)) {
            $resData = UserNotes::insertGetId($data);

            if($this->app['helper']('Utility')->notEmpty($resData)) {
                $payLoad = ['status' => 'success', 'message' => 'The note inserted successfully.'];
            } else {
                $this->app['monolog.debug']->error('error in add user note.', $data);
                $msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
                $payLoad = ['status' => 'error', 'message' => $msg];
            }
        }else {
            $payLoad = ['status' => 'error', 'message' => "There is'nt data for insert."];
        }

        return $payLoad;
    }

    public function editNote($id_user, $data)
    {
        $this->app['helper']('ModelLog')->Log();
        $payLoad = [];
        $saveData = [];

        if(!empty($id_user))
        {
            if($this->app['helper']('Utility')->notEmpty($data)) {
                $saveData['notes'] = $data['notes'];
                $resData = UserNotes::where('id_user', $id_user)->update($saveData);

                if($this->app['helper']('Utility')->notEmpty($resData)) {
                    $payLoad = ['status' => 'success', 'message' => 'The note updated successfully.'];
                } else {
                    $this->app['monolog.debug']->error('error in add user note.', $data);
                    $msg = $this->app['translator']->trans('UnexpectedError', array('%code%' => $this->app['monolog.debug']->getProcessors()[0]->getUid()));
                    $payLoad = ['status' => 'error', 'message' => $msg];
                }
            }else {
                $payLoad = ['status' => 'error', 'message' => "There is'nt data for update."];
            }
        }else {
            $payLoad = ['status'=>'error','message'=>$this->app['translator']->trans('RequiredsEmpty', array('%name%' => 'id_user'))];
        }

        return $payLoad;
    }

}