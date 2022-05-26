<?php

namespace app\models;
use Yii;


class LoginUser extends \yii\db\ActiveRecord
{

    public $old_password;
	public $new_password;
	public $repeat_password;

    public static function tableName()
    {
        return 'login_user';
    }

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['last_login'], 'safe'],
            [['username', 'password'], 'string', 'max' => 128],
            [['authKey'], 'string', 'max' => 200],
            [['role'], 'string', 'max' => 20],
            [['username'], 'unique'],
            [['new_password', 'old_password', 'repeat_password'], 'required', 'on' => 'changePwd'],
            ['new_password', 'string', 'length'=>[12,32], 'on' => 'changePwd'],
		    ['old_password', 'findPasswords', 'on' => 'changePwd'],
		    ['repeat_password', 'compare', 'compareAttribute'=>'new_password', 'on'=>'changePwd'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'authKey' => 'Auth Key',
            'role' => 'Role',
            'last_login' => 'Last Login',
        ];
    }
    
	//matching the old password with your existing password.
	public function findPasswords($attribute, $params)
	{
		$user = LoginUser::find( Yii::$app->user->identity->id )->one();
		if ($user->password != sha1($this->old_password)) {
            $this->addError($attribute, 'old password is incorrect.');
        }
	}

}
